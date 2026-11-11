<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ==================== PUBLIK (PELANGGAN) ====================
Route::get('/', [KatalogController::class, 'beranda'])->name('beranda');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('/produk/{produk}', [KatalogController::class, 'show'])->name('produk.show');

// ==================== AUTENTIKASI & PROFIL ====================
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dasbor');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== KERANJANG, ALAMAT, CHECKOUT (PELANGGAN) ====================
    Route::middleware('role:pelanggan,pemilik')->group(function () {
        Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang.index');
        Route::post('/keranjang/{produk}', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
        Route::patch('/keranjang/item/{detail}', [KeranjangController::class, 'ubah'])->name('keranjang.ubah');
        Route::delete('/keranjang/item/{detail}', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');
        Route::delete('/keranjang', [KeranjangController::class, 'kosongkan'])->name('keranjang.kosongkan');

        Route::get('/profil/alamat', [AlamatController::class, 'index'])->name('alamat.index');
        Route::post('/profil/alamat', [AlamatController::class, 'store'])->name('alamat.store');
        Route::put('/profil/alamat/{alamat}', [AlamatController::class, 'update'])->name('alamat.update');
        Route::delete('/profil/alamat/{alamat}', [AlamatController::class, 'destroy'])->name('alamat.destroy');
        Route::patch('/profil/alamat/{alamat}/default', [AlamatController::class, 'setDefault'])->name('alamat.default');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'proses'])->name('checkout.proses');
        Route::get('/ongkir/hitung', [CheckoutController::class, 'ongkir'])->name('ongkir.hitung');
    });
});

require __DIR__.'/auth.php';
