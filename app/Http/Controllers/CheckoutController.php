<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use App\Services\OngkirInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout) {}

    public function index(): View|RedirectResponse
    {
        $keranjang = auth()->user()->keranjangAktif()->load('detail.produk.kategori');

        if ($keranjang->detail->isEmpty()) {
            return redirect()->route('keranjang.index')
                ->with('peringatan', 'Keranjang Anda masih kosong.');
        }

        return view('checkout.index', [
            'keranjang' => $keranjang,
            'daftarAlamat' => auth()->user()->alamat ?? [],
            'alamatDefault' => auth()->user()->alamatDefault(),
        ]);
    }

    public function proses(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'metode_pemenuhan' => ['required', Rule::in(['ambil_di_toko', 'kurir'])],
            'alamat_id' => ['nullable', 'string'],
            'ekspedisi' => ['nullable', 'string'],
            'metode_pembayaran' => ['required', Rule::in(['midtrans_qris', 'midtrans_va_bca', 'midtrans_va_bni', 'midtrans_va_bri'])],
        ]);

        $pesanan = $this->checkout->proses($request->user(), $data);

        return redirect()
            ->route('pesanan.show', $pesanan->kode_pesanan)
            ->with('berhasil', 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran sebelum '.$pesanan->expired_at->format('d M Y H:i').'.');
    }

    /**
     * Endpoint JSON pilihan ongkir untuk halaman checkout (dipanggil via Alpine).
     */
    public function ongkir(Request $request, OngkirInterface $ongkir): JsonResponse
    {
        $data = $request->validate(['alamat_id' => ['required', 'string']]);

        $alamat = collect($request->user()->alamat ?? [])->firstWhere('id', $data['alamat_id']);

        abort_if(! $alamat, 404, 'Alamat tidak ditemukan.');

        return response()->json([
            'pilihan' => $ongkir->hitung($alamat),
        ]);
    }
}
