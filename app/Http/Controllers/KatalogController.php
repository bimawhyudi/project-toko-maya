<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function beranda(): View
    {
        $kategoriPopuler = Kategori::withCount('produk')
            ->orderByDesc('produk_count')
            ->take(5)
            ->get();

        $terlaris = Produk::query()
            ->whereHas('detailPesanan.pesanan', fn ($q) => $q->bukanKeranjang())
            ->withSum(['detailPesanan as terjual' => fn ($q) => $q->whereHas('pesanan', fn ($p) => $p->bukanKeranjang())], 'jumlah')
            ->with('kategori')
            ->orderByDesc('terjual')
            ->take(5)
            ->get();

        $terbaru = Produk::with('kategori')->latest('id')->take(5)->get();

        return view('katalog.beranda', [
            'kategoriPopuler' => $kategoriPopuler,
            'terlaris' => $terlaris,
            'terbaru' => $terbaru,
        ]);
    }

    public function index(Request $request): View
    {
        $kategoriAktif = $request->filled('kategori')
            ? Kategori::whereKey($request->integer('kategori'))->firstOrFail()
            : null;

        $produk = Produk::query()
            ->with('kategori')
            ->when($kategoriAktif, fn ($q) => $q->where('kategori_id', $kategoriAktif->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $kata = '%'.$request->string('q').'%';
                $q->where('nama_produk', 'like', $kata);
            })
            ->when($request->urutkan === 'termurah', fn ($q) => $q->orderBy('harga'))
            ->when($request->urutkan === 'termahal', fn ($q) => $q->orderByDesc('harga'))
            ->when($request->urutkan === 'terlaris', fn ($q) => $q->orderByDesc('id'))
            ->orderBy('nama_produk')
            ->paginate(24)
            ->withQueryString();

        return view('katalog.index', [
            'produk' => $produk,
            'kategoriAktif' => $kategoriAktif,
            'kategori' => Kategori::withCount('produk')->orderBy('nama_kategori')->get(),
        ]);
    }

    public function show(Produk $produk): View
    {
        $produk->load('kategori');

        $terjual = DetailPesanan::where('produk_id', $produk->id)
            ->whereHas('pesanan', fn ($q) => $q->bukanKeranjang())
            ->sum('jumlah');

        $serupa = Produk::where('kategori_id', $produk->kategori_id)
            ->whereKeyNot($produk->id)
            ->with('kategori')
            ->take(4)
            ->get();

        return view('katalog.show', [
            'produk' => $produk,
            'terjual' => $terjual,
            'serupa' => $serupa,
        ]);
    }
}