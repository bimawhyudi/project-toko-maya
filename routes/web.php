<?php

use App\Http\Controllers\AlamatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PemilikController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ==================== PUBLIK (PELANGGAN) ====================
Route::get('/', [KatalogController::class, 'beranda'])->name('beranda');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
Route::get('/produk/{produk}', [KatalogController::class, 'show'])->name('produk.show');

// Webhook Midtrans: tanpa CSRF (dikecualikan di bootstrap/app.php) dan tanpa login.
Route::post('/midtrans/webhook', [WebhookController::class, 'terima'])->name('midtrans.webhook');

// ==================== AUTENTIKASI & PROFIL ====================
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dasbor');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== KERANJANG, ALAMAT, CHECKOUT, PESANAN (PELANGGAN) ====================
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

        Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan.index');
        Route::get('/pesanan/{kode}', [PesananController::class, 'show'])->name('pesanan.show');
        Route::post('/pesanan/{kode}/diterima', [PesananController::class, 'diterima'])->name('pesanan.diterima');

        Route::get('/pesanan/{kode}/bayar', [PembayaranController::class, 'show'])->name('pembayaran.show');
        Route::post('/pesanan/{kode}/bayar/simulasi', [PembayaranController::class, 'simulasi'])
            ->name('pembayaran.simulasi'); // hanya aktif saat APP_ENV=local
    });

    // ==================== DASBOR KASIR ====================
    Route::middleware('role:kasir')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/', [KasirController::class, 'index'])->name('index');
        Route::post('/transaksi', [KasirController::class, 'simpanTransaksi'])->name('simpan');
        Route::get('/ambil', [KasirController::class, 'ambil'])->name('ambil');
        Route::post('/ambil/serahkan', [KasirController::class, 'serahkan'])->name('serahkan');
        Route::get('/riwayat', [KasirController::class, 'riwayat'])->name('riwayat');
        Route::get('/struk/{kode}', [KasirController::class, 'struk'])->name('struk');
    });

    // ==================== DASBOR ADMIN GUDANG ====================
    Route::middleware('role:admin_gudang')->prefix('gudang')->name('gudang.')->group(function () {
        Route::get('/', [GudangController::class, 'index'])->name('index');
        Route::get('/pesanan', [GudangController::class, 'pesanan'])->name('pesanan');
        Route::post('/pesanan/{pesanan}/siap', [GudangController::class, 'setSiapDiambil'])->name('siap');
        Route::post('/pesanan/{pesanan}/kirim', [GudangController::class, 'kirim'])->name('kirim');
        Route::get('/stok', [GudangController::class, 'stok'])->name('stok');
        Route::post('/stok/{produk}', [GudangController::class, 'ubahStok'])->name('stok.ubah');
    });

    // ==================== DASBOR PEMILIK ====================
    Route::middleware('role:pemilik')->prefix('pemilik')->name('pemilik.')->group(function () {
        Route::get('/', [PemilikController::class, 'index'])->name('index');
        Route::get('/produk', [PemilikController::class, 'produk'])->name('produk');
        Route::post('/produk', [PemilikController::class, 'simpanProduk'])->name('produk.simpan');
        Route::put('/produk/{produk}', [PemilikController::class, 'ubahProduk'])->name('produk.ubah');
        Route::delete('/produk/{produk}', [PemilikController::class, 'hapusProduk'])->name('produk.hapus');
        Route::post('/kategori', [PemilikController::class, 'simpanKategori'])->name('kategori.simpan');
        Route::delete('/kategori/{kategori}', [PemilikController::class, 'hapusKategori'])->name('kategori.hapus');
        Route::get('/staf', [PemilikController::class, 'staf'])->name('staf');
        Route::post('/staf/{user}/peran', [PemilikController::class, 'ubahPeran'])->name('staf.peran');
        Route::get('/laporan', [PemilikController::class, 'laporan'])->name('laporan');
        Route::get('/laporan/export/excel', [PemilikController::class, 'exportExcel'])->name('laporan.excel');
        Route::get('/laporan/export/pdf', [PemilikController::class, 'exportPdf'])->name('laporan.pdf');
    });
});

require __DIR__.'/auth.php';
