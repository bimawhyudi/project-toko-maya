<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Services\KasirService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function __construct(private KasirService $kasir) {}

    /**
     * Halaman utama kasir: POS transaksi fisik + tab lainnya.
     */
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();
        $kategoriId = $request->integer('kategori');

        $produk = Produk::query()
            ->with('kategori')
            ->when($kategoriId, fn ($w) => $w->where('kategori_id', $kategoriId))
            ->when($q !== '', fn ($w) => $w->where('nama_produk', 'like', '%'.$q.'%'))
            ->orderBy('nama_produk')
            ->limit(60)
            ->get();

        $jumlahAmbil = Pesanan::where('status_pesanan', Pesanan::STATUS_SIAP_DIAMBIL)
            ->where('metode_pemenuhan', Pesanan::PEMENUHAN_AMBIL_DI_TOKO)->count();

        return view('kasir.index', [
            'produk' => $produk,
            'kategori' => Kategori::orderBy('nama_kategori')->get(),
            'kategoriAktif' => $kategoriId,
            'q' => $q,
            'jumlahAmbil' => $jumlahAmbil,
        ]);
    }

    /**
     * Simpan transaksi fisik (alur C): stok terpotong, pesanan selesai, struk siap.
     */
    public function simpanTransaksi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items_json' => ['required', 'json'],
            'metode' => ['required', 'in:tunai,qris,debit'],
        ]);

        $items = collect(json_decode($data['items_json'], true))
            ->map(fn ($i) => [
                'produk_id' => (int) ($i['produk_id'] ?? 0),
                'jumlah' => max(1, (int) ($i['jumlah'] ?? 1)),
            ])->all();

        try {
            $pesanan = $this->kasir->transaksiFisik($request->user()->id, $items, $data['metode']);
        } catch (ValidationException $e) {
            return back()->with('peringatan', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('kasir.struk', $pesanan->kode_pesanan)
            ->with('berhasil', 'Transaksi berhasil. Struk siap dicetak.');
    }

    /**
     * Halaman verifikasi kode ambil di toko (alur B akhir).
     */
    public function ambil(Request $request): View
    {
        $hasil = null;

        if ($request->filled('kode')) {
            $hasil = Pesanan::where('kode_pesanan', strtoupper($request->string('kode')->trim()))
                ->whereIn('status_pesanan', [Pesanan::STATUS_SIAP_DIAMBIL, Pesanan::STATUS_SELESAI])
                ->with(['detail.produk', 'user'])
                ->first();
        }

        $antrean = Pesanan::where('status_pesanan', Pesanan::STATUS_SIAP_DIAMBIL)
            ->where('metode_pemenuhan', Pesanan::PEMENUHAN_AMBIL_DI_TOKO)
            ->with('detail.produk')
            ->terbaru()
            ->get();

        $selesaiHariIni = Pesanan::where('status_pesanan', Pesanan::STATUS_SELESAI)
            ->where('metode_pemenuhan', Pesanan::PEMENUHAN_AMBIL_DI_TOKO)
            ->whereDate('updated_at', today())
            ->with('detail.produk')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('kasir.ambil', ['hasil' => $hasil, 'antrean' => $antrean, 'selesaiHariIni' => $selesaiHariIni]);
    }

    /**
     * Kasir menyerahkan barang: siap_diambil -> selesai.
     */
    public function serahkan(Request $request): RedirectResponse
    {
        $data = $request->validate(['kode_pesanan' => ['required', 'string']]);

        $pesanan = Pesanan::where('kode_pesanan', strtoupper($data['kode_pesanan']))
            ->where('metode_pemenuhan', Pesanan::PEMENUHAN_AMBIL_DI_TOKO)
            ->firstOrFail();

        if ($pesanan->status_pesanan === Pesanan::STATUS_SELESAI) {
            return redirect()->route('kasir.ambil', ['kode' => $pesanan->kode_pesanan])
                ->with('peringatan', 'Pesanan ini sudah pernah diserahkan sebelumnya.');
        }

        abort_unless($pesanan->status_pesanan === Pesanan::STATUS_SIAP_DIAMBIL, 403, 'Barang belum ditandai siap oleh gudang.');

        DB::transaction(fn () => $pesanan->forceFill(['status_pesanan' => Pesanan::STATUS_SELESAI])->save());

        return redirect()->route('kasir.ambil', ['kode' => $pesanan->kode_pesanan])
            ->with('berhasil', 'Barang berhasil diserahkan kepada pelanggan. Status pesanan: selesai.');
    }

    /**
     * Daftar transaksi kasir (fisik) + pesanan daring lunas.
     */
    public function riwayat(Request $request): View
    {
        $tab = $request->string('tab')->toString() ?: 'fisik';

        $fisik = Pesanan::where('metode_pemenuhan', Pesanan::PEMENUHAN_BELI_LANGSUNG)
            ->with('detail')
            ->terbaru()->paginate(10, ['*'], 'hal_fisik')->withQueryString();

        $online = Pesanan::whereIn('status_pesanan', [Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SIAP_DIAMBIL, Pesanan::STATUS_DIKIRIM, Pesanan::STATUS_SELESAI])
            ->whereIn('metode_pemenuhan', [Pesanan::PEMENUHAN_AMBIL_DI_TOKO, Pesanan::PEMENUHAN_KURIR])
            ->with(['detail', 'user'])
            ->terbaru()->paginate(10, ['*'], 'hal_online')->withQueryString();

        return view('kasir.riwayat', ['tab' => $tab, 'fisik' => $fisik, 'online' => $online]);
    }

    /**
     * Struk transaksi fisik (siap cetak).
     */
    public function struk(string $kode): View
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode)
            ->where('metode_pemenuhan', Pesanan::PEMENUHAN_BELI_LANGSUNG)
            ->with('detail.produk')
            ->firstOrFail();

        return view('kasir.struk', ['pesanan' => $pesanan]);
    }
}
