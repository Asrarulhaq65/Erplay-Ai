<?php

namespace App\Http\Controllers;

use App\Models\ArusKas;
use App\Models\AuditLog;
use App\Models\PembayaranKredit;
use App\Models\Penjualan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * PembayaranKreditController
 *
 * Handles partial payments (cicilan) and full settlement (pelunasan)
 * for credit sales (penjualan with metode_pembayaran = 'Kredit').
 *
 * Business Rules:
 *   - Payment cannot exceed current sisa_piutang
 *   - When sisa_piutang reaches 0 → status_pembayaran = 'Lunas'
 *   - Every payment creates an arus_kas entry (Masuk)
 */
class PembayaranKreditController extends Controller
{
    /**
     * Record a partial or full payment for a credit sale.
     * POST /penjualan/{id}/bayar
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $penjualan = Penjualan::with('pelanggan')->findOrFail($id);

        if ($penjualan->status_pembayaran === 'Lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi ini sudah lunas.',
            ], 422);
        }

        $validated = $request->validate([
            'jumlah'      => ['required', 'numeric', 'min:1'],
            'keterangan'  => ['nullable', 'string', 'max:255'],
            'tanggal_bayar' => ['nullable', 'date'],
        ]);

        $jumlah = (float) $validated['jumlah'];
        $sisaSekarang = (float) $penjualan->sisa_piutang;

        if ($jumlah > $sisaSekarang) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah bayar (Rp " . number_format($jumlah, 0, ',', '.') . ") melebihi sisa piutang (Rp " . number_format($sisaSekarang, 0, ',', '.') . ").",
            ], 422);
        }

        try {
            DB::transaction(function () use ($penjualan, $jumlah, $validated, $sisaSekarang) {
                $sisaBaru = $sisaSekarang - $jumlah;
                $statusBaru = $sisaBaru <= 0 ? 'Lunas' : 'Belum Lunas';

                // 1. Record cicilan
                PembayaranKredit::create([
                    'penjualan_id'  => $penjualan->id,
                    'user_id'       => auth()->id(),
                    'jumlah'        => $jumlah,
                    'keterangan'    => $validated['keterangan'] ?? null,
                    'tanggal_bayar' => $validated['tanggal_bayar'] ?? now()->toDateString(),
                ]);

                // 2. Update penjualan
                $penjualan->update([
                    'sisa_piutang'      => max(0, $sisaBaru),
                    'status_pembayaran' => $statusBaru,
                ]);

                // 3. Arus kas
                ArusKas::create([
                    'tipe'       => 'Masuk',
                    'kategori'   => 'Penjualan POS',
                    'nominal'    => $jumlah,
                    'keterangan' => "Bayar Kredit - Invoice: {$penjualan->nomor_invoice}" . ($statusBaru === 'Lunas' ? ' (LUNAS)' : " (sisa Rp " . number_format($sisaBaru, 0, ',', '.') . ")"),
                    'tanggal'    => $validated['tanggal_bayar'] ?? now()->toDateString(),
                    'user_id'    => auth()->id(),
                ]);

                AuditLog::log("Pembayaran Kredit Invoice {$penjualan->nomor_invoice}: Rp " . number_format($jumlah, 0, ',', '.') . " → {$statusBaru}", 'Kredit');
            });

            $penjualan->refresh();

            return response()->json([
                'success'           => true,
                'message'           => $penjualan->status_pembayaran === 'Lunas'
                    ? "Transaksi {$penjualan->nomor_invoice} sudah LUNAS! 🎉"
                    : "Pembayaran Rp " . number_format($jumlah, 0, ',', '.') . " berhasil dicatat.",
                'status_pembayaran' => $penjualan->status_pembayaran,
                'sisa_piutang'      => (float) $penjualan->sisa_piutang,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a credit sale as fully paid (lunas) immediately.
     * POST /penjualan/{id}/lunas
     */
    public function lunas(Request $request, int $id): JsonResponse
    {
        $penjualan = Penjualan::findOrFail($id);

        if ($penjualan->status_pembayaran === 'Lunas') {
            return response()->json(['success' => false, 'message' => 'Sudah lunas.'], 422);
        }

        $sisaSekarang = (float) $penjualan->sisa_piutang;

        try {
            DB::transaction(function () use ($penjualan, $sisaSekarang, $request) {
                // Record sisa as final payment
                if ($sisaSekarang > 0) {
                    PembayaranKredit::create([
                        'penjualan_id'  => $penjualan->id,
                        'user_id'       => auth()->id(),
                        'jumlah'        => $sisaSekarang,
                        'keterangan'    => $request->input('keterangan', 'Pelunasan penuh'),
                        'tanggal_bayar' => now()->toDateString(),
                    ]);

                    ArusKas::create([
                        'tipe'       => 'Masuk',
                        'kategori'   => 'Penjualan POS',
                        'nominal'    => $sisaSekarang,
                        'keterangan' => "Pelunasan Kredit - Invoice: {$penjualan->nomor_invoice}",
                        'tanggal'    => now()->toDateString(),
                        'user_id'    => auth()->id(),
                    ]);
                }

                $penjualan->update([
                    'sisa_piutang'      => 0,
                    'status_pembayaran' => 'Lunas',
                ]);

                AuditLog::log("Pelunasan Kredit Invoice {$penjualan->nomor_invoice} (Rp " . number_format($sisaSekarang, 0, ',', '.') . ")", 'Kredit');
            });

            return response()->json([
                'success' => true,
                'message' => "Invoice {$penjualan->nomor_invoice} telah ditandai LUNAS!",
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment history for a credit sale.
     * GET /penjualan/{id}/riwayat-bayar
     */
    public function riwayat(int $id): JsonResponse
    {
        $penjualan = Penjualan::with(['pembayaranKredit.user', 'pelanggan'])->findOrFail($id);

        return response()->json([
            'success'           => true,
            'nomor_invoice'     => $penjualan->nomor_invoice,
            'total_bayar'       => (float) $penjualan->total_bayar,
            'uang_muka'         => (float) $penjualan->uang_muka,
            'sisa_piutang'      => (float) $penjualan->sisa_piutang,
            'status_pembayaran' => $penjualan->status_pembayaran,
            'riwayat'           => $penjualan->pembayaranKredit->map(fn($p) => [
                'id'            => $p->id,
                'jumlah'        => (float) $p->jumlah,
                'keterangan'    => $p->keterangan,
                'tanggal_bayar' => $p->tanggal_bayar?->format('d/m/Y'),
                'user'          => $p->user?->nama_lengkap ?? '-',
            ]),
        ]);
    }
}
