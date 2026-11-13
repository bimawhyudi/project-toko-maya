<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $pesanan = $request->user()->pesanan()
            ->bukanKeranjang()
            ->with(['detail.produk', 'user'])
            ->when(in_array($status, $this->statusValid(), true), fn ($q) => $q->where('status_pesanan', $status))
            ->terbaru()
            ->paginate(10)
            ->withQueryString();

        return view('pesanan.index', [
            'pesanan' => $pesanan,
            'status' => $status,
        ]);
    }

    public function show(Request $request, string $kode): View
    {
        $pesanan = $request->user()->pesanan()
            ->bukanKeranjang()
            ->where('kode_pesanan', $kode)
            ->with(['detail.produk.kategori'])
            ->firstOrFail();

        return view('pesanan.show', ['pesanan' => $pesanan]);
    }

    /**
     * Pelanggan menekan "Pesanan diterima" pada pesanan yang sudah dikirim.
     */
    public function diterima(Request $request, string $kode): RedirectResponse
    {
        $pesanan = $request->user()->pesanan()
            ->where('kode_pesanan', $kode)
            ->firstOrFail();

        abort_unless($pesanan->status_pesanan === Pesanan::STATUS_DIKIRIM, 403, 'Pesanan belum dalam status dikirim.');

        DB::transaction(function () use ($pesanan) {
            $pesanan->forceFill(['status_pesanan' => Pesanan::STATUS_SELESAI])->save();
        });

        return redirect()->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('berhasil', 'Terima kasih! Pesanan ditandai sudah diterima.');
    }

    private function statusValid(): array
    {
        return [
            Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
            Pesanan::STATUS_DIPROSES,
            Pesanan::STATUS_SIAP_DIAMBIL,
            Pesanan::STATUS_DIKIRIM,
            Pesanan::STATUS_SELESAI,
            Pesanan::STATUS_BATAL,
        ];
    }
}
