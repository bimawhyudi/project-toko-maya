<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    /**
     * Halaman bayar dengan snap.js (pop-up Midtrans).
     */
    public function show(Request $request, string $kode): View|RedirectResponse
    {
        $pesanan = $request->user()->pesanan()
            ->where('kode_pesanan', $kode)
            ->with('detail.produk')
            ->firstOrFail();

        abort_unless($pesanan->status_pesanan === Pesanan::STATUS_MENUNGGU_PEMBAYARAN, 403, 'Pesanan ini tidak sedang menunggu pembayaran.');

        $token = null;
        $galat = null;

        if ($this->midtrans->aktif()) {
            try {
                $token = $this->midtrans->buatSnapToken($pesanan);
            } catch (\Throwable $e) {
                $galat = 'Gagal membuat token pembayaran: '.$e->getMessage();
            }
        } else {
            $galat = 'Kunci Midtrans belum diisi di .env. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY, atau gunakan "Simulasi Bayar" di bawah saat pengembangan.';
        }

        return view('pembayaran.show', [
            'pesanan' => $pesanan,
            'token' => $token,
            'galat' => $galat,
            'clientKey' => config('midtrans.client_key'),
            'snapUrl' => config('midtrans.is_production')
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js',
        ]);
    }

    /**
     * Rute SIMULASI bayar — HANYA aktif bila APP_ENV=local.
     * Meniru webhook Midtrans agar alur lunas bisa dites tanpa URL publik.
     */
    public function simulasi(Request $request, string $kode): RedirectResponse
    {
        abort_unless(app()->environment('local'), 404);

        $pesanan = $request->user()->pesanan()
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        $pesanan->tandaiDiprosesDanPotongStok();

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('berhasil', 'Simulasi pembayaran berhasil: pesanan LUNAS, stok dipotong, status menjadi Diproses.');
    }
}
