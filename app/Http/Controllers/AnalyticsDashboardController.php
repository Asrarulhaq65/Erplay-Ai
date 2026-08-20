<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardController extends Controller
{
    /**
     * Display the Executive Analytics Dashboard with Custom Date Range.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        $tokoId = auth()->check() ? auth()->user()->toko_id : 1;

        // 1. Total Revenue
        $totalRevenue = Penjualan::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('total_bayar');

        // 2. Total Piutang (Kredit & Belum Lunas)
        $totalPiutang = Penjualan::where('metode_pembayaran', 'Kredit')
            ->where('status_pembayaran', 'Belum Lunas')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum(DB::raw('total_bayar - nominal_uang'));

        // 3. Net Profit
        $netProfit = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->join('produk', 'produk.id', '=', 'penjualan_detail.produk_id')
            ->where('penjualan.toko_id', $tokoId)
            ->whereDate('penjualan.created_at', '>=', $startDate)
            ->whereDate('penjualan.created_at', '<=', $endDate)
            ->sum(DB::raw('(penjualan_detail.harga_satuan - produk.harga_modal) * penjualan_detail.qty'));

        // 4. Department Performance
        $performance = DB::table('penjualan_detail')
            ->join('penjualan', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->join('produk', 'produk.id', '=', 'penjualan_detail.produk_id')
            ->join('kategori_produk', 'kategori_produk.id', '=', 'produk.kategori_id')
            ->join('kelompok_produk', 'kelompok_produk.id', '=', 'kategori_produk.kelompok_id')
            ->where('penjualan.toko_id', $tokoId)
            ->whereDate('penjualan.created_at', '>=', $startDate)
            ->whereDate('penjualan.created_at', '<=', $endDate)
            ->select(
                'kelompok_produk.nama_kelompok',
                DB::raw('SUM(penjualan_detail.qty) as total_qty'),
                DB::raw('SUM(penjualan_detail.subtotal) as total_revenue'),
                DB::raw('SUM((penjualan_detail.harga_satuan - produk.harga_modal) * penjualan_detail.qty) as total_profit')
            )
            ->groupBy('kelompok_produk.id', 'kelompok_produk.nama_kelompok')
            ->orderByDesc('total_profit')
            ->get();

        // 5. Daily Sales Trend Chart Data
        $dailySalesRaw = Penjualan::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(total_bayar) as omzet'),
                DB::raw('COUNT(id) as total_trx')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('tanggal', 'asc')
            ->get()
            ->keyBy('tanggal');

        $chartLabels = [];
        $chartOmzet  = [];
        $chartTrx    = [];

        $current = \Carbon\Carbon::parse($startDate);
        $end     = \Carbon\Carbon::parse($endDate);

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $chartLabels[] = $current->format('d M');
            $chartOmzet[]  = (float) ($dailySalesRaw[$dateStr]->omzet ?? 0);
            $chartTrx[]    = (int) ($dailySalesRaw[$dateStr]->total_trx ?? 0);
            $current->addDay();
        }

        $dailySales = [
            'labels' => $chartLabels,
            'omzet'  => $chartOmzet,
            'trx'    => $chartTrx,
        ];

        return view('pages.laporan.dashboard-analytics', compact(
            'totalRevenue',
            'totalPiutang',
            'netProfit',
            'performance',
            'startDate',
            'endDate',
            'dailySales'
        ));
    }

    /**
     * Export Analytics Data to CSV
     */
    public function exportCsv(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());
        $tokoId    = auth()->check() ? auth()->user()->toko_id : 1;

        $penjualan = Penjualan::with(['pelanggan', 'user'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at', 'desc')
            ->get();

        AuditLog::log("Export Laporan Analytics CSV ({$startDate} s/d {$endDate})", 'Analytics');

        $fileName = 'Laporan_Analytics_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($penjualan) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['No', 'No Invoice', 'Tanggal', 'Pelanggan', 'Metode Pembayaran', 'Status', 'Total Bayar', 'Kasir'], ';');

            foreach ($penjualan as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->nomor_invoice,
                    $row->created_at->format('Y-m-d H:i'),
                    $row->pelanggan->nama_pelanggan ?? 'Umum',
                    $row->metode_pembayaran,
                    $row->status_pembayaran,
                    number_format($row->total_bayar, 0, ',', '.'),
                    $row->user->nama_lengkap ?? 'Kasir',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
