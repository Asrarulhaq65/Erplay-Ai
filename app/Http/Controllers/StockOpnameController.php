<?php

namespace App\Http\Controllers;

use App\Models\LogStok;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockOpnameController extends Controller
{
    /**
     * Display the stock opname form and history table.
     */
    public function index()
    {
        $produks = Produk::orderBy('nama_produk')->get();

        $history = LogStok::with('produk')
            ->where('tipe_perubahan', 'Penyesuaian_Stok')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.inventory.opname', compact('produks', 'history'));
    }

    /**
     * Process the stock adjustment transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id'        => ['required', 'integer', 'exists:produk,id'],
            'tipe_penyesuaian' => ['required', 'string', Rule::in(['Tambah_Stok', 'Kurang_Stok'])],
            'jumlah_perubahan' => ['required', 'integer', 'min:1'],
            'keterangan'       => ['required', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            $produk = Produk::lockForUpdate()->findOrFail($validated['produk_id']);

            $stokAwal = $produk->stok;
            $jumlah   = $validated['jumlah_perubahan'];
            
            if ($validated['tipe_penyesuaian'] === 'Tambah_Stok') {
                $stokAkhir = $stokAwal + $jumlah;
                $logJumlah = $jumlah;
            } else {
                $stokAkhir = $stokAwal - $jumlah;
                $logJumlah = -$jumlah;
                
                if ($stokAkhir < 0) {
                    throw new \Exception("Stok akhir tidak boleh kurang dari 0. (Tersedia: {$stokAwal})");
                }
            }

            // Update product stock
            $produk->update(['stok' => $stokAkhir]);

            // Insert audit log
            LogStok::create([
                'produk_id'      => $produk->id,
                'tipe_perubahan' => 'Penyesuaian_Stok',
                'jumlah'         => $logJumlah,
                'stok_awal'      => $stokAwal,
                'stok_akhir'     => $stokAkhir,
                'keterangan'     => $validated['keterangan'] . ' (Oleh: ' . (auth()->user()->nama_lengkap ?? 'System') . ')',
            ]);

            DB::commit();

            return redirect()->route('inventory.opname.index')
                ->with('success', "Penyesuaian stok berhasil disimpan. Stok {$produk->nama_produk} kini menjadi {$stokAkhir}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memproses penyesuaian stok: ' . $e->getMessage());
        }
    }
}
