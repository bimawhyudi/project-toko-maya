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
     * Halaman bayar: langsung menampilkan Virtual Account / QRIS
     * sesuai metode yang dipilih saat checkout (tanpa popup pilih ulang).
     */
    public function show(Request $request, string $kode): View|RedirectResponse
    {
        $pesanan = $request->user()->pesanan()
            ->where('kode_pesanan', $kode)
            ->with('detail.produk')
            ->firstOrFail();

        $this->midtrans->sinkronkanStatus($pesanan);

        if ($pesanan->status_pesanan !== Pesanan::STATUS_MENUNGGU_PEMBAYARAN) {
            return redirect()->route('pesanan.show', $pesanan->kode_pesanan);
        }

        $galat = null;

        if (! $this->midtrans->aktif()) {
            $galat = 'Kunci Midtrans belum diisi di .env. Isi MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY terlebih dahulu.';
        } elseif (empty($pesanan->pembayaran)) {
            // Charge hanya sekali per pesanan: kalau sudah pernah dibuat, tampilkan data tersimpan.
            try {
                $this->midtrans->buatPembayaran($pesanan);
                $pesanan->refresh();
            } catch (\Throwable $e) {
                $galat = 'Gagal membuat pembayaran: '.$e->getMessage();
            }
        }

        return view('pembayaran.show', [
            'pesanan' => $pesanan,
            'pembayaran' => $pesanan->pembayaran ?? [],
            'galat' => $galat,
        ]);
    }
}