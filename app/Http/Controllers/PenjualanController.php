<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\LogStok;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * PenjualanController — Core POS Transaction Engine
 *
 * This controller is the single point of truth for processing all sales
 * transactions from both POS modes (Standard & Quick-Entry Custom).
 *
 * Security Architecture:
 * ─────────────────────
 * Prices are NEVER trusted from the client. The controller re-derives the
 * correct selling price entirely from the database based on the customer's
 * status_pelanggan. This prevents any price manipulation via browser
 * inspect-element, Postman, or any other client-side tampering.
 * (Compliance: PRD Pasal 5 — Backend Price & Payment Validation)
 *
 * Transaction Safety:
 * ───────────────────
 * All database writes are wrapped in a single DB::beginTransaction() block.
 * Row-level locking (lockForUpdate) is applied on every Produk row before
 * reading stock, preventing race conditions under concurrent POS usage.
 * If any step fails, DB::rollBack() ensures the database remains consistent.
 *
 * Automation Triggers (executed inside the transaction):
 * ───────────────────────────────────────────────────────
 *   1. INSERT penjualan            → Sales header record
 *   2. INSERT penjualan_detail[]   → One row per cart item
 *   3. DECREMENT produk.stok       → Real-time inventory deduction
 *   4. INSERT log_stok[]           → Append-only audit entry per item
 *   5. INSERT arus_kas             → Cash flow entry (Tunai / Digital only)
 */
class PenjualanController extends Controller
{
    // ─── Constants ─────────────────────────────────────────────────────────

    /**
     * Maps pelanggan.status_pelanggan to the produk price column.
     * This is the single source of truth for tier-to-column mapping.
     */
    private const PRICE_TIER_MAP = [
        'Umum'    => 'harga_jual_umum',
        'Member'  => 'harga_jual_member',
        'Rekan'   => 'harga_jual_rekan',
        'Motoris' => 'harga_jual_motoris',
    ];

    // ─── Public Methods ─────────────────────────────────────────────────────

    /**
     * Store a new sales transaction (POS Checkout).
     *
     * Expected JSON Payload:
     * ─────────────────────
     * {
     *   "pelanggan_id"       : null | integer,   // null = walk-in "Umum" customer
     *   "metode_pembayaran"  : "Tunai" | "Kredit" | "Digital Payment",
     *   "diskon"             : 0,                // header-level discount (optional)
     *   "nominal_uang"       : 50000,            // cash tendered (required for Tunai)
     *   "referensi_digital"  : "TRX-12345",      // optional ref for Digital Payment
     *   "items"              : [
     *     { "product_id": 5, "qty": 2 },
     *     { "product_id": 12, "qty": 1 }
     *   ]
     * }
     *
     * Prices are NOT accepted from the client — they are derived from the DB.
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // ══════════════════════════════════════════════════════════════════
        // PHASE 1 — STRUCTURAL VALIDATION
        // Validate the shape and types of the incoming payload.
        // ══════════════════════════════════════════════════════════════════
        $validated = $request->validate([
            'pelanggan_id'        => ['nullable', 'integer', 'exists:pelanggan,id'],
            'metode_pembayaran'   => ['required', 'string', Rule::in(['Tunai', 'Kredit', 'Digital Payment'])],
            'diskon'              => ['nullable', 'numeric', 'min:0'],
            'nominal_uang'        => ['required', 'numeric', 'min:0'],
            'uang_muka'           => ['nullable', 'numeric', 'min:0'],
            'tanggal_jatuh_tempo' => ['nullable', 'date'],
            'referensi_digital'   => ['nullable', 'string', 'max:100'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'integer', 'exists:produk,id'],
            'items.*.qty'         => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        // ══════════════════════════════════════════════════════════════════
        // PHASE 2 — BUSINESS RULE PRE-VALIDATION (Outside Transaction)
        // Resolve customer tier, validate stock, and compute prices
        // from the database before acquiring any row locks.
        // ══════════════════════════════════════════════════════════════════

        // 2a. Resolve pelanggan and determine price tier
        $statusPelanggan = 'Umum'; // Default for walk-in customers

        if (! empty($validated['pelanggan_id'])) {
            $pelanggan = Pelanggan::find($validated['pelanggan_id']);

            // Guard: ensure the customer belongs to the same tenant
            // (Global Scope handles this, but we check explicitly for clarity)
            if (! $pelanggan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelanggan tidak ditemukan atau bukan milik toko ini.',
                ], 422);
            }

            $statusPelanggan = $pelanggan->status_pelanggan;
        }

        // 2b. Determine which price column to use based on customer tier
        $priceColumn = self::PRICE_TIER_MAP[$statusPelanggan] ?? 'harga_jual_umum';

        // 2c. Pre-load all products, validate stock, and compute per-item prices
        $resolvedItems = [];
        $stockErrors   = [];
        $totalHarga    = 0;
        $diskon        = (float) ($validated['diskon'] ?? 0);

        foreach ($validated['items'] as $index => $item) {
            /** @var Produk $produk */
            $produk = Produk::select([
                'id', 'nama_produk', 'barcode', 'stok', 'harga_modal',
                'harga_jual_umum', 'harga_jual_member',
                'harga_jual_rekan', 'harga_jual_motoris',
            ])->find($item['product_id']);

            // Guard: product must exist within this tenant (Global Scope enforces this)
            if (! $produk) {
                $stockErrors[] = "Item #{$index}: Produk tidak ditemukan.";
                continue;
            }

            $qty = (int) $item['qty'];

            // ── STOCK VALIDATION ──────────────────────────────────────────
            if ($produk->stok < $qty) {
                $stockErrors[] = sprintf(
                    'Stok tidak mencukupi untuk produk "%s". Stok tersedia: %d %s, diminta: %d %s.',
                    $produk->nama_produk,
                    $produk->stok,
                    'unit',
                    $qty,
                    'unit'
                );
                continue;
            }

            // ── PRICE DERIVATION (Server-Side Only) ───────────────────────
            // Prices come ONLY from the database, never from the client.
            // This is the anti-inspect-element protection required by PRD Pasal 5.
            $hargaSatuan = (float) $produk->{$priceColumn};
            $subtotal    = $hargaSatuan * $qty;

            $resolvedItems[] = [
                'produk'       => $produk,
                'qty'          => $qty,
                'harga_satuan' => $hargaSatuan,
                'harga_modal'  => (float) $produk->harga_modal,
                'subtotal'     => $subtotal,
            ];

            $totalHarga += $subtotal;
        }

        // Abort if any stock or product errors were found
        if (! empty($stockErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Periksa stok produk.',
                'errors'  => $stockErrors,
            ], 422);
        }

        // 2d. Compute final totals
        $totalBayar = max(0, $totalHarga - $diskon);

        // 2e. Payment-method-specific validations
        $metodePembayaran = $validated['metode_pembayaran'];
        $nominalUang      = (float) ($validated['nominal_uang'] ?? 0);

        if ($metodePembayaran === 'Tunai') {
            // For cash payments: tendered amount must cover the bill
            if ($nominalUang < $totalBayar) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'Nominal uang (Rp %s) tidak mencukupi. Total yang harus dibayar: Rp %s.',
                        number_format($nominalUang, 0, ',', '.'),
                        number_format($totalBayar, 0, ',', '.')
                    ),
                    'errors' => ['nominal_uang' => 'Nominal uang kurang dari total belanja.'],
                ], 422);
            }
        }

        // Compute kembalian (change) — only meaningful for Tunai
        $kembalian = ($metodePembayaran === 'Tunai')
            ? $nominalUang - $totalBayar
            : 0;

        // Determine payment status
        $statusPembayaran = ($metodePembayaran === 'Kredit') ? 'Belum Lunas' : 'Lunas';

        // Kredit-specific: compute uang_muka (DP) and sisa_piutang
        $uangMuka          = 0;
        $sisaPiutang       = 0;
        $tanggalJatuhTempo = $validated['tanggal_jatuh_tempo'] ?? null;
        if ($metodePembayaran === 'Kredit') {
            $uangMuka    = min((float) ($validated['uang_muka'] ?? 0), $totalBayar);
            $sisaPiutang = $totalBayar - $uangMuka;
            if ($sisaPiutang <= 0) {
                // Full payment at time of kredit = directly lunas
                $sisaPiutang      = 0;
                $statusPembayaran = 'Lunas';
            }
        } else {
            $uangMuka    = $totalBayar; // Tunai/Digital: fully paid
            $sisaPiutang = 0;
        }

        // ══════════════════════════════════════════════════════════════════
        // PHASE 3 — DATABASE TRANSACTION (Closure-Based)
        // All writes are atomic inside DB::transaction(). If any step
        // throws, Laravel automatically rolls back the transaction,
        // leaving the database in a consistent state.
        // ══════════════════════════════════════════════════════════════════
        try {
            $result = DB::transaction(function () use (
                $validated, $resolvedItems, $totalHarga, $diskon, $totalBayar,
                $nominalUang, $kembalian, $metodePembayaran, $statusPembayaran,
                $statusPelanggan
            ) {
                // ── 3a. Generate globally unique nomor_invoice ─────────────
                $nomorInvoice = $this->generateNomorInvoice();

                // ── 3b. Insert the Penjualan header ───────────────────────
                // toko_id is auto-assigned by the model's "creating" event.
                // user_id is explicitly set from the authenticated session.
                $penjualan = Penjualan::create([
                    'pelanggan_id'        => $validated['pelanggan_id'] ?? null,
                    'nomor_invoice'       => $nomorInvoice,
                    'total_harga'         => $totalHarga,
                    'diskon'              => $diskon,
                    'total_bayar'         => $totalBayar,
                    'nominal_uang'        => $nominalUang,
                    'kembalian'           => $kembalian,
                    'metode_pembayaran'   => $metodePembayaran,
                    'status_pembayaran'   => $statusPembayaran,
                    'user_id'             => auth()->user()?->id,
                    'uang_muka'           => $uangMuka,
                    'sisa_piutang'        => $sisaPiutang,
                    'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                ]);

                // ── 3c. Process each cart item ────────────────────────────
                foreach ($resolvedItems as $lineItem) {
                    /** @var Produk $produk */
                    $produk = $lineItem['produk'];
                    $qty    = $lineItem['qty'];

                    // Re-read current stock with a row-level lock to prevent
                    // race conditions when multiple kasirs serve simultaneously.
                    $produkLocked = Produk::lockForUpdate()->find($produk->id);

                    if (! $produkLocked) {
                        throw new \RuntimeException(
                            "Produk ID {$produk->id} tidak ditemukan saat proses transaksi."
                        );
                    }

                    // Double-check stock under lock (another transaction may have
                    // consumed stock between our pre-validation and this point)
                    if ($produkLocked->stok < $qty) {
                        throw ValidationException::withMessages([
                            'items' => sprintf(
                                'Stok "%s" habis saat transaksi berlangsung. Stok tersisa: %d.',
                                $produkLocked->nama_produk,
                                $produkLocked->stok
                            ),
                        ]);
                    }

                    $stokAwal  = $produkLocked->stok;
                    $stokAkhir = $stokAwal - $qty;

                    // ── i. Insert penjualan_detail (line item) ─────────────
                    PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'produk_id'    => $produk->id,
                        'harga_satuan' => $lineItem['harga_satuan'],
                        'qty'          => $qty,
                        'subtotal'     => $lineItem['subtotal'],
                    ]);

                    // ── ii. Decrement stock on the Produk table ────────────
                    $produkLocked->decrement('stok', $qty);

                    // ── iii. Append immutable log_stok row ────────────────
                    // toko_id is auto-assigned by the model's "creating" event.
                    LogStok::create([
                        'produk_id'       => $produk->id,
                        'tipe_perubahan'  => 'Penjualan',
                        'jumlah'          => -$qty,
                        'stok_awal'       => $stokAwal,
                        'stok_akhir'      => $stokAkhir,
                        'keterangan'      => "Invoice: {$nomorInvoice}",
                    ]);
                }

                // ── 3d. Insert arus_kas entry ─────────────────────────────
                // Only for cash-generating payment methods (Tunai & Digital Payment).
                // Kredit/Tempo does NOT create a cash inflow — it creates a receivable
                // (piutang), which is tracked via status_pembayaran = 'Belum Lunas'.
                // ── 3d. Insert arus_kas entry ─────────────────────────────
                // Only for cash-generating payment methods (Tunai & Digital Payment).
                // Kredit/Tempo does NOT create a cash inflow — it creates a receivable
                // (piutang), which is tracked via status_pembayaran = 'Belum Lunas'.
                if (in_array($metodePembayaran, ['Tunai', 'Digital Payment'], true)) {
                    $refDigital    = $validated['referensi_digital'] ?? '-';
                    $keteranganKas = ($metodePembayaran === 'Tunai')
                        ? "Penjualan Tunai - Invoice: {$nomorInvoice}"
                        : "Penjualan Digital ({$refDigital}) - Invoice: {$nomorInvoice}";

                    ArusKas::create([
                        'tipe'       => 'Masuk',
                        'kategori'   => 'Penjualan POS',
                        'nominal'    => $totalBayar,
                        'keterangan' => $keteranganKas,
                        'tanggal'    => now()->toDateString(),
                        'user_id'    => auth()->user()?->id,
                    ]);
                } elseif ($metodePembayaran === 'Kredit' && $uangMuka > 0) {
                    // Record the down payment (uang muka) as a cash inflow
                    ArusKas::create([
                        'tipe'       => 'Masuk',
                        'kategori'   => 'Penjualan POS',
                        'nominal'    => $uangMuka,
                        'keterangan' => "DP Kredit - Invoice: {$nomorInvoice} (sisa Rp " . number_format($sisaPiutang, 0, ',', '.') . ")",
                        'tanggal'    => now()->toDateString(),
                        'user_id'    => auth()->user()?->id,
                    ]);
                }

                // ── 3e. Automatic Jurnal Akuntansi & Audit Log ─────────────
                try {
                    $tokoId = auth()->user()?->toko_id ?? 1;
                    $kasAkun = \App\Models\AkunAkuntansi::where('toko_id', $tokoId)
                        ->where('kode_akun', $metodePembayaran === 'Digital Payment' ? '1002' : ($metodePembayaran === 'Kredit' ? '1200' : '1001'))
                        ->first();
                    $penjualanAkun = \App\Models\AkunAkuntansi::where('toko_id', $tokoId)->where('kode_akun', '4001')->first();
                    $hppAkun = \App\Models\AkunAkuntansi::where('toko_id', $tokoId)->where('kode_akun', '5001')->first();
                    $persediaanAkun = \App\Models\AkunAkuntansi::where('toko_id', $tokoId)->where('kode_akun', '1100')->first();

                    if ($kasAkun && $penjualanAkun) {
                        $jurnal = \App\Models\JurnalUmum::create([
                            'toko_id'      => $tokoId,
                            'nomor_jurnal' => 'JU-POS-' . $penjualan->id,
                            'tanggal'      => now()->toDateString(),
                            'keterangan'   => "Penjualan POS Invoice {$nomorInvoice}",
                            'ref_type'     => 'Penjualan',
                            'ref_id'       => $penjualan->id,
                            'user_id'      => auth()->user()?->id,
                        ]);

                        // Debit Kas/Bank/Piutang & Kredit Pendapatan
                        \App\Models\JurnalDetail::create([
                            'jurnal_id' => $jurnal->id,
                            'akun_id'   => $kasAkun->id,
                            'debit'     => $totalBayar,
                            'kredit'    => 0,
                            'memo'      => "Penerimaan POS Invoice {$nomorInvoice}",
                        ]);
                        \App\Models\JurnalDetail::create([
                            'jurnal_id' => $jurnal->id,
                            'akun_id'   => $penjualanAkun->id,
                            'debit'     => 0,
                            'kredit'    => $totalBayar,
                            'memo'      => "Pendapatan POS Invoice {$nomorInvoice}",
                        ]);

                        // Debit HPP & Kredit Persediaan (jika HPP > 0)
                        $totalHpp = 0;
                        foreach ($resolvedItems as $item) {
                            $totalHpp += ($item['produk']->harga_modal ?? 0) * $item['qty'];
                        }
                        if ($totalHpp > 0 && $hppAkun && $persediaanAkun) {
                            \App\Models\JurnalDetail::create([
                                'jurnal_id' => $jurnal->id,
                                'akun_id'   => $hppAkun->id,
                                'debit'     => $totalHpp,
                                'kredit'    => 0,
                                'memo'      => "HPP POS Invoice {$nomorInvoice}",
                            ]);
                            \App\Models\JurnalDetail::create([
                                'jurnal_id' => $jurnal->id,
                                'akun_id'   => $persediaanAkun->id,
                                'debit'     => 0,
                                'kredit'    => $totalHpp,
                                'memo'      => "Pengurangan Persediaan POS Invoice {$nomorInvoice}",
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning("Gagal membuat Jurnal Otomatis Penjualan: " . $e->getMessage());
                }

                \App\Models\AuditLog::log("Transaksi POS Berhasil Diproses: {$nomorInvoice} (Rp " . number_format($totalBayar, 0, ',', '.') . ")", 'POS');

                // Return data from the closure for the success response
                return [
                    'penjualan'     => $penjualan,
                    'nomorInvoice'  => $nomorInvoice,
                    'uangMuka'      => $uangMuka,
                    'sisaPiutang'   => $sisaPiutang,
                ];
            }); // ← DB::transaction auto-commits here; auto-rollbacks on exception

            // ══════════════════════════════════════════════════════════════
            // PHASE 4 — SUCCESS RESPONSE
            // Return the complete transaction summary for receipt printing.
            // ══════════════════════════════════════════════════════════════
            $penjualan    = $result['penjualan'];
            $nomorInvoice = $result['nomorInvoice'];

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses.',
                'data'    => [
                    'penjualan_id'        => $penjualan->id,
                    'nomor_invoice'       => $nomorInvoice,
                    'status_pelanggan'    => $statusPelanggan,
                    'total_harga'         => $totalHarga,
                    'diskon'              => $diskon,
                    'total_bayar'         => $totalBayar,
                    'metode_pembayaran'   => $metodePembayaran,
                    'status_pembayaran'   => $statusPembayaran,
                    'nominal_uang'        => $nominalUang,
                    'kembalian'           => $kembalian,
                    'uang_muka'           => $result['uangMuka'],
                    'sisa_piutang'        => $result['sisaPiutang'],
                    'tanggal_jatuh_tempo' => $tanggalJatuhTempo,
                    'jumlah_item'         => count($resolvedItems),
                    'created_at'          => $penjualan->created_at->toIso8601String(),
                ],
            ], 201);

        } catch (ValidationException $e) {
            // Stock was consumed between pre-validation and lock acquisition
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal saat memproses transaksi.',
                'errors'  => $e->errors(),
            ], 422);

        } catch (QueryException $e) {
            // Database error (e.g., duplicate nomor_invoice from a race condition)
            Log::error('PenjualanController@store QueryException', [
                'message'    => $e->getMessage(),
                'user_id'    => auth()->id(),
                'toko_id'    => auth()->user()?->toko_id,
                'payload'    => $request->except(['password']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database. Silakan coba lagi.',
            ], 500);

        } catch (\Throwable $e) {
            // Catch-all for unexpected errors
            Log::error('PenjualanController@store Throwable', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'toko_id' => auth()->user()?->toko_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Tim teknis telah diberitahu.',
            ], 500);
        }
    }

    // ─── Private Helpers ────────────────────────────────────────────────────

    /**
     * Generate a globally unique nomor_invoice.
     *
     * Format  : INV-YYYYMMDD-XXXX
     * Example : INV-20260101-0001, INV-20260101-0002
     *
     * Uses withoutGlobalScope('tenant') so the sequence counter increments
     * across ALL tenants' invoices for the current date. This guarantees
     * global uniqueness (since nomor_invoice has a UNIQUE DB constraint).
     *
     * Must be called INSIDE an active DB transaction to prevent a race
     * condition where two concurrent requests generate the same number.
     *
     * @return string
     */
    private function generateNomorInvoice(): string
    {
        $datePrefix = 'INV-' . now()->format('Ymd') . '-';

        // Lock the last invoice row for this date to prevent concurrent
        // processes from generating duplicate sequence numbers
        $lastInvoice = Penjualan::withoutGlobalScope('tenant')
            ->where('nomor_invoice', 'like', $datePrefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('nomor_invoice');

        $nextSequence = $lastInvoice
            ? ((int) substr($lastInvoice, -4)) + 1
            : 1;

        return $datePrefix . str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cetak Struk Belanja (Thermal Layout)
     * Returns a minimalist standalone view for POS receipt printing.
     */
    public function printStruk($id)
    {
        $penjualan = Penjualan::with(['details.produk', 'pelanggan', 'toko', 'user'])->findOrFail($id);
        return view('pages.pos.print_struk', compact('penjualan'));
    }

    /**
     * Get details of a specific sale via API.
     */
    public function detail($id): JsonResponse
    {
        $penjualan = Penjualan::with(['details.produk', 'user', 'pelanggan'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data'    => $penjualan
        ]);
    }
}
