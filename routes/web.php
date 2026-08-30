<?php

use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\KelompokProdukController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::prefix('katalog')->name('katalog.')->group(function () {
    Route::get('/{slug}', [\App\Http\Controllers\PublicCatalogController::class, 'index'])->name('index');
    Route::get('/{slug}/api/products', [\App\Http\Controllers\PublicCatalogController::class, 'products'])->name('products');
    Route::post('/{slug}/chat', [\App\Http\Controllers\PublicCatalogController::class, 'chat'])->name('chat');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        $tokoId = auth()->user()->toko_id;
        $startOfMonth = now()->startOfMonth();

        // Sales chart data
        $salesMonthly = \App\Models\Penjualan::withoutGlobalScopes()
            ->where('toko_id', $tokoId)
            ->whereYear('created_at', now()->year)
            ->selectRaw("MONTH(created_at) as bulan, SUM(total_bayar) as total")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $values = [];
        for ($m = 1; $m <= 12; $m++) {
            $values[] = (int) ($salesMonthly[$m] ?? 0);
        }
        $salesChart = ['labels' => $labels, 'values' => $values];

        // Trend indicators: growth this month
        $trends = [
            'produk' => \App\Models\Produk::where('created_at', '>=', $startOfMonth)->count(),
            'pelanggan' => \App\Models\Pelanggan::where('created_at', '>=', $startOfMonth)->count(),
            'kelompok' => \App\Models\KelompokProduk::where('created_at', '>=', $startOfMonth)->count(),
            'kategori' => \App\Models\KategoriProduk::where('created_at', '>=', $startOfMonth)->count(),
        ];

        // Today's summary
        $today = now()->startOfDay();
        $todayPenjualan = \App\Models\Penjualan::withoutGlobalScopes()
            ->where('toko_id', $tokoId)
            ->where('created_at', '>=', $today)
            ->where('status_pembayaran', 'Lunas')
            ->get();
        $todaySummary = [
            'transaksi' => $todayPenjualan->count(),
            'total' => $todayPenjualan->sum('total_bayar'),
        ];

        // Low stock products
        $lowStockCount = \App\Models\Produk::whereColumn('stok', '<=', 'stok_minimum')->where('stok', '>', 0)->count();
        $outOfStockCount = \App\Models\Produk::where('stok', 0)->count();

        // Total Counts for Master Data (Passed to view to prevent in-view DB queries)
        $counts = [
            'produk'    => \App\Models\Produk::count(),
            'pelanggan' => \App\Models\Pelanggan::count(),
            'kelompok'  => \App\Models\KelompokProduk::count(),
            'kategori'  => \App\Models\KategoriProduk::count(),
        ];

        return view('pages.dashboard', compact('salesChart', 'trends', 'todaySummary', 'lowStockCount', 'outOfStockCount', 'counts'));
    })->name('dashboard');

    // ── Akses CMS Master (Hanya Super Admin) ─────────────────────────────────
    Route::middleware(['role:Super Admin'])->prefix('cms')->name('cms.')->group(function () {
        Route::get('/toko', [App\Http\Controllers\CmsController::class, 'index'])->name('toko.index');
        Route::put('/toko/{id}/subscription', [App\Http\Controllers\CmsController::class, 'updateSubscription'])->name('toko.update_subscription');
    });

    // ── Halaman Info Langganan Habis ─────────────────────────────────────────
    Route::get('/langganan', function () {
        return view('pages.langganan.expired');
    })->name('langganan.index');

    Route::get('/langganan/status', function () {
        return view('pages.langganan.status');
    })->name('langganan.status');

// ── Public Customer Self-Service Routes (Mobile-First POS) ─────────────────
Route::prefix('self-service')->name('self-service.')->group(function () {
    Route::get('/', [App\Http\Controllers\SelfServiceController::class, 'index'])->name('index');
    Route::post('/process-voice', [App\Http\Controllers\SelfServiceController::class, 'processVoice'])->name('process-voice');
    Route::post('/process-scan', [App\Http\Controllers\SelfServiceController::class, 'processScan'])->name('process-scan');
    Route::post('/store', [App\Http\Controllers\SelfServiceController::class, 'store'])->name('store');
});

// ── Akses POS (Kasir, Super Admin, Owner, Admin Toko) ──────────────────────────────────
Route::middleware(['role:Super Admin,Owner,Kasir,Admin Toko', 'subscription'])->group(function () {
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/custom', function () { return view('pages.pos.custom'); })->name('custom');
        Route::get('/standard', function () { return view('pages.pos.standard'); })->name('standard');
        Route::get('/scan', [App\Http\Controllers\PosScanController::class, 'showScan'])->name('scan');
        Route::post('/scan/process', [App\Http\Controllers\PosScanController::class, 'processScan'])->name('scan.process');
        Route::get('/scan/status/{resultKey}', [App\Http\Controllers\PosScanController::class, 'scanStatus'])->name('scan.status');
        Route::post('/voice/process', [App\Http\Controllers\VoiceTransactionController::class, 'processVoice'])->name('voice.process');
        Route::get('/print-struk/{id}', [App\Http\Controllers\PenjualanController::class, 'printStruk'])->name('print-struk');
    });
    Route::post('/penjualan/store', [App\Http\Controllers\PenjualanController::class, 'store'])->name('penjualan.store');
    Route::get('/api/penjualan/detail/{id}', [App\Http\Controllers\PenjualanController::class, 'detail'])->name('penjualan.detail');

    // ── Kredit / Partial Payment Routes ───────────────────────────────────────
    Route::post('/penjualan/{id}/bayar', [App\Http\Controllers\PembayaranKreditController::class, 'store'])->name('penjualan.bayar');
    Route::post('/penjualan/{id}/lunas', [App\Http\Controllers\PembayaranKreditController::class, 'lunas'])->name('penjualan.lunas');
    Route::get('/penjualan/{id}/riwayat-bayar', [App\Http\Controllers\PembayaranKreditController::class, 'riwayat'])->name('penjualan.riwayat-bayar');

    // ── Admin Self-Service Orders Verification Panel ───────────────────────
    Route::prefix('admin/self-service')->name('admin.self-service.')->group(function () {
        Route::get('/', [App\Http\Controllers\SelfServiceController::class, 'adminIndex'])->name('index');
        Route::get('/pending-count', [App\Http\Controllers\SelfServiceController::class, 'pendingCount'])->name('pending-count');
        Route::post('/{id}/verify', [App\Http\Controllers\SelfServiceController::class, 'verifyOrder'])->name('verify');
        Route::post('/{id}/reject', [App\Http\Controllers\SelfServiceController::class, 'rejectOrder'])->name('reject');
    });
});


// ── Akses Inventory & Pembelian (Gudang, Super Admin, Owner, Admin Toko) ──────────────
Route::middleware(['role:Super Admin,Owner,Gudang,Admin Toko'])->group(function () {
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/opname', [App\Http\Controllers\StockOpnameController::class, 'index'])->name('opname.index');
        Route::post('/opname', [App\Http\Controllers\StockOpnameController::class, 'store'])->name('opname.store');
    });
    Route::prefix('pembelian')->name('pembelian.')->group(function () {
        Route::get('/', [App\Http\Controllers\PembelianController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\PembelianController::class, 'create'])->name('create');
        Route::post('/store', [App\Http\Controllers\PembelianController::class, 'store'])->name('store');
        Route::get('/{pembelian}', [App\Http\Controllers\PembelianController::class, 'show'])->name('show');
    });
});

// ── Akses Laporan ─────────────────────────────────────────────────────────
Route::prefix('laporan')->name('laporan.')->group(function () {
    // Rekap penjualan bisa diakses Kasir & Admin Toko
    Route::middleware(['role:Super Admin,Owner,Kasir,Admin Toko'])->group(function () {
        Route::get('/rekap-penjualan', [App\Http\Controllers\RekapPenjualanController::class, 'index'])->name('rekap-penjualan');
    });
    // Analytics hanya untuk Owner, Admin Toko & Super Admin
    Route::middleware(['role:Super Admin,Owner,Admin Toko'])->group(function () {
        Route::get('/analytics', [App\Http\Controllers\AnalyticsDashboardController::class, 'index'])->name('analytics');
        Route::get('/analytics/export-csv', [App\Http\Controllers\AnalyticsDashboardController::class, 'exportCsv'])->name('analytics.export-csv');
    });
});

// ── Akses Akuntansi (Super Admin, Owner, Admin Toko) ───────────────────────
Route::middleware(['role:Super Admin,Owner,Admin Toko'])->prefix('akuntansi')->name('akuntansi.')->group(function () {
    Route::get('/accounts', [App\Http\Controllers\AkuntansiController::class, 'accountsIndex'])->name('accounts.index');
    Route::post('/accounts', [App\Http\Controllers\AkuntansiController::class, 'accountsStore'])->name('accounts.store');
    Route::get('/jurnal', [App\Http\Controllers\AkuntansiController::class, 'jurnalIndex'])->name('jurnal.index');
    Route::post('/jurnal', [App\Http\Controllers\AkuntansiController::class, 'jurnalStore'])->name('jurnal.store');
    Route::get('/buku-besar', [App\Http\Controllers\AkuntansiController::class, 'bukuBesar'])->name('buku-besar');
    Route::get('/laba-rugi', [App\Http\Controllers\AkuntansiController::class, 'labaRugi'])->name('laba-rugi');
});

// ── Akses Master Data ─────────────────────────────────────────────────────
Route::prefix('master')->name('master.')->group(function () {
    
    // Master Produk (Kasir, Gudang & Admin Toko boleh akses)
    Route::middleware(['role:Super Admin,Owner,Kasir,Gudang,Admin Toko'])->group(function () {
        Route::resource('kelompok-produk', KelompokProdukController::class)->except(['show', 'create', 'edit']);
        Route::resource('kategori-produk', KategoriProdukController::class)->except(['show', 'create', 'edit']);

        // ── Export & Template routes HARUS sebelum resource() ─────────────────
        // Urutan ini penting: tanpa ini, Laravel akan menganggap 'export-csv'
        // sebagai {produk} parameter dari route model binding.
        Route::get('produk/export-csv',        [App\Http\Controllers\ProdukImportExportController::class, 'exportCsv'])->name('produk.export-csv');
        Route::get('produk/download-template', [App\Http\Controllers\ProdukImportExportController::class, 'downloadTemplate'])->name('produk.download-template');
        Route::get('produk/panduan-export',    [App\Http\Controllers\ProdukImportExportController::class, 'panduanExport'])->name('produk.panduan-export');

        // ── Import CSV routes ────────────────────────────────────────────────
        Route::get('produk/import',  [App\Http\Controllers\ProdukImportExportController::class, 'showImport'])->name('produk.import');
        Route::post('produk/import', [App\Http\Controllers\ProdukImportExportController::class, 'importCsv'])->name('produk.import.process');

        Route::resource('produk', ProdukController::class)->except(['show']);
        Route::get('produk-filter', [ProdukController::class, 'filter'])->name('produk.filter');
    });

    // Master Pelanggan & Supplier (Hanya Super Admin, Owner, Admin Toko)
    Route::middleware(['role:Super Admin,Owner,Admin Toko'])->group(function () {
        Route::get('pelanggan/import', [PelangganController::class, 'importForm'])->name('pelanggan.import');
        Route::post('pelanggan/import', [PelangganController::class, 'import'])->name('pelanggan.import.process');
        Route::patch('pelanggan/{pelanggan}/tier', [PelangganController::class, 'updateTier'])->name('pelanggan.tier.update');
        Route::resource('pelanggan', PelangganController::class)->except(['show', 'create', 'edit']);
        Route::resource('supplier', App\Http\Controllers\SupplierController::class)->except(['show', 'create', 'edit']);
    });
});

// ── Akses Pengaturan (Sistem & Akun) ───────────────────────────────────────
Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
    // Hanya Super Admin, Owner, Admin Toko yang bisa mengatur user dan toko
    Route::middleware(['role:Super Admin,Owner,Admin Toko'])->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class)->except(['show']);
        
        Route::get('toko', [\App\Http\Controllers\TokoController::class, 'edit'])->name('toko.edit');
        Route::put('toko', [\App\Http\Controllers\TokoController::class, 'update'])->name('toko.update');
        Route::get('katalog', [\App\Http\Controllers\PublicCatalogSettingController::class, 'edit'])->name('katalog.edit');
        Route::put('katalog', [\App\Http\Controllers\PublicCatalogSettingController::class, 'update'])->name('katalog.update');
        Route::get('audit-log', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-log.index');

        // ── Pengaturan AI & Vision ───────────────────────────────────────────
        Route::get('ai', [\App\Http\Controllers\AiSettingController::class, 'index'])->name('ai.index');
        Route::put('ai', [\App\Http\Controllers\AiSettingController::class, 'update'])->name('ai.update');
        Route::post('ai/test', [\App\Http\Controllers\AiSettingController::class, 'testConnection'])->name('ai.test');
        Route::post('ai/models', [\App\Http\Controllers\AiSettingController::class, 'discoverModels'])->name('ai.models');
        Route::get('ai/assistant', [\App\Http\Controllers\AiSettingController::class, 'assistant'])->name('ai.assistant');
        Route::put('ai/assistant', [\App\Http\Controllers\AiSettingController::class, 'updateAssistant'])->name('ai.assistant.update');
    });
});

// ── AI Copilot Assistant Route ───────────────────────────────────────────
Route::post('/ai-assistant/chat', [\App\Http\Controllers\AiAssistantController::class, 'chat'])->name('ai.chat');
Route::get('/ai-assistant/history', [\App\Http\Controllers\AiAssistantController::class, 'history'])->name('ai.history');
Route::get('/ai-assistant/suggestions', [\App\Http\Controllers\AiAssistantController::class, 'suggestions'])->name('ai.suggestions');
Route::post('/ai-assistant/action', [\App\Http\Controllers\AiAssistantController::class, 'action'])->name('ai.action');

}); // End of auth middleware group
