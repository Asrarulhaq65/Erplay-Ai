<?php

use App\Http\Controllers\KategoriProdukController;
use App\Http\Controllers\PelangganSearchController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProductSearchController;
use Illuminate\Support\Facades\Route;

// POS Custom Mode Routes (Task 3 & 4)
// Should ideally be inside auth middleware.
Route::prefix('pos')->group(function () {
    Route::get('/search-pelanggan', [PelangganSearchController::class, 'index'])->name('pos.search-pelanggan');
    Route::get('/search-produk', [ProductSearchController::class, 'index'])->name('pos.search-produk');
    Route::post('/checkout', [PenjualanController::class, 'store'])->name('pos.checkout');
});

// Master Data AJAX Routes
Route::prefix('master')->name('api.master.')->group(function () {
    Route::get('/kategori-by-kelompok', [KategoriProdukController::class, 'getByKelompok'])->name('kategori.by-kelompok');
});
