<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Penjualan;
use Illuminate\Http\Request;

class RekapPenjualanController extends Controller
{
    /**
     * Display a listing of the sales transactions.
     * Supports multi-filtering by date range, customer, payment method, and status.
     */
    public function index(Request $request)
    {
        $startDate       = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate         = $request->get('end_date', now()->toDateString());
        $pelangganId     = $request->get('pelanggan_id');
        $metode          = $request->get('metode_pembayaran');
        $statusPembayaran = $request->get('status_pembayaran');

        $query = Penjualan::with(['pelanggan', 'user', 'details.produk', 'pembayaranKredit.user'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc');

        if ($pelangganId === 'umum') {
            $query->whereNull('pelanggan_id');
        } elseif ($pelangganId) {
            $query->where('pelanggan_id', $pelangganId);
        }

        if ($metode) {
            $query->where('metode_pembayaran', $metode);
        }

        if ($statusPembayaran) {
            $query->where('status_pembayaran', $statusPembayaran);
        }

        $penjualan  = $query->get();
        $pelanggans = Pelanggan::orderBy('nama_pelanggan')->get();

        // Summary for kredit piutang
        $totalPiutang = Penjualan::where('status_pembayaran', 'Belum Lunas')
            ->where('metode_pembayaran', 'Kredit')
            ->sum('sisa_piutang');

        return view('pages.laporan.rekap-penjualan', compact(
            'penjualan',
            'pelanggans',
            'startDate',
            'endDate',
            'pelangganId',
            'metode',
            'statusPembayaran',
            'totalPiutang'
        ));
    }
}
