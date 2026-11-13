<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GudangController extends Controller
{
    /**
     * Ringkasan gudang: kartu statistik + antrean pesanan masuk.
     */
    public function index(): View
    {
        $jumlah = fn (string $status) => Pesanan::where('status_pesanan', $status)->count();

        return view('gudang.index', [
            'pesananBaru' => $jumlah(Pesanan::STATUS_DIPROSES),
            'sudahDiproses' => $jumlah(Pesanan::STATUS_SIAP_DIAMBIL) + $jumlah(Pesanan::STATUS_DIKIRIM),
            'siapKirim' => $jumlah(Pesanan::STATUS_SIAP_DIAMBIL),
            'stokMenipis' => Produk::whereBetween('stok', [1, config('toko.ambang_stok_menipis', 5)])->count(),
            'stokHabis' => Produk::where('stok', '<=', 0)->count(),
            'antrean' => Pesanan::where('status_pesanan', Pesanan::STATUS_DIPROSES)
                ->with(['detail.produk', 'user'])
                ->terbaru()
                ->take(10)
                ->get(),
        ]);
    }

    /**
     * Daftar pesanan untuk diproses gudang (filter status).
     */
    public function pesanan(Request $request): View
    {
        $status = $request->string('status')->toString() ?: Pesanan::STATUS_DIPROSES;

        $pesanan = Pesanan::whereIn('status_pesanan', [Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SIAP_DIAMBIL, Pesanan::STATUS_DIKIRIM, Pesanan::STATUS_SELESAI])
            ->whereIn('metode_pemenuhan', [Pesanan::PEMENUHAN_AMBIL_DI_TOKO, Pesanan::PEMENUHAN_KURIR])
            ->with(['detail.produk', 'user'])
            ->when(in_array($status, [Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SIAP_DIAMBIL, Pesanan::STATUS_DIKIRIM, Pesanan::STATUS_SELESAI], true), fn ($q) => $q->where('status_pesanan', $status))
            ->terbaru()
            ->paginate(10)
            ->withQueryString();

        return view('gudang.pesanan', ['pesanan' => $pesanan, 'status' => $status]);
    }

    /**
     * Tandai pesanan ambil di toko sudah siap (diproses -> siap_diambil).
     */
    public function setSiapDiambil(Request $request, Pesanan $pesanan): RedirectResponse
    {
        abort_unless($pesanan->status_pesanan === Pesanan::STATUS_DIPROSES, 403, 'Status pesanan tidak bisa diubah dari kondisi sekarang.');
        abort_unless($pesanan->metode_pemenuhan === Pesanan::PEMENUHAN_AMBIL_DI_TOKO, 403, 'Hanya pesanan ambil di toko yang ditandai siap diambil.');

        DB::transaction(fn () => $pesanan->forceFill(['status_pesanan' => Pesanan::STATUS_SIAP_DIAMBIL])->save());

        return back()->with('berhasil', 'Pesanan '.$pesanan->kode_pesanan.' ditandai SIAP DIAMBIL.');
    }

    /**
     * Simpan nomor resi + ekspedisi lalu ubah status menjadi dikirim.
     */
    public function kirim(Request $request, Pesanan $pesanan): RedirectResponse
    {
        $boleh = in_array($pesanan->status_pesanan, [Pesanan::STATUS_DIPROSES, Pesanan::STATUS_DIKIRIM], true);
        abort_unless($boleh, 403, 'Status pesanan tidak bisa diubah dari kondisi sekarang.');
        abort_unless($pesanan->metode_pemenuhan === Pesanan::PEMENUHAN_KURIR, 403, 'Hanya pesanan kurir yang dikirim.');

        $data = $request->validate([
            'ekspedisi' => ['required', 'string', 'max:50'],
            'no_resi' => ['required', 'string', 'max:50'],
        ], [], [
            'ekspedisi' => 'kurir/ekspedisi',
            'no_resi' => 'nomor resi',
        ]);

        DB::transaction(fn () => $pesanan->forceFill([
            'ekspedisi' => $data['ekspedisi'],
            'no_resi' => $data['no_resi'],
            'status_pesanan' => Pesanan::STATUS_DIKIRIM,
        ])->save());

        return back()->with('berhasil', 'Resi tersimpan. Pesanan '.$pesanan->kode_pesanan.' berstatus DIKIRIM.');
    }

    /**
     * Daftar stok barang + ubah stok.
     */
    public function stok(Request $request): View
    {
        $filter = $request->string('filter')->toString();

        $stok = Produk::with('kategori')
            ->when($filter === 'menipis', fn ($q) => $q->whereBetween('stok', [1, config('toko.ambang_stok_menipis', 5)]))
            ->when($filter === 'habis', fn ($q) => $q->where('stok', '<=', 0))
            ->when($filter === 'aman', fn ($q) => $q->where('stok', '>', config('toko.ambang_stok_menipis', 5)))
            ->when($request->filled('q'), fn ($q) => $q->where('nama_produk', 'like', '%'.$request->string('q').'%'))
            ->orderBy('stok')
            ->orderBy('nama_produk')
            ->paginate(20)
            ->withQueryString();

        return view('gudang.stok', ['stok' => $stok, 'filter' => $filter]);
    }

    public function ubahStok(Request $request, Produk $produk): RedirectResponse
    {
        $data = $request->validate(['stok' => ['required', 'integer', 'min:0', 'max:999999']]);

        DB::transaction(fn () => $produk->forceFill(['stok' => $data['stok']])->save());

        return back()->with('berhasil', 'Stok "'.$produk->nama_produk.'" diubah menjadi '.$data['stok'].'.');
    }
}
