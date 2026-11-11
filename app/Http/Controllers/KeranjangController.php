<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeranjangController extends Controller
{
    public function index(): View
    {
        $keranjang = auth()->user()->keranjangAktif()->load('detail.produk.kategori');

        return view('keranjang.index', ['keranjang' => $keranjang]);
    }

    public function tambah(Request $request, Produk $produk): RedirectResponse
    {
        $request->validate(['jumlah' => ['nullable', 'integer', 'min:1']]);

        if ($produk->stok <= 0) {
            return redirect()->route('katalog.index')->with('peringatan', 'Maaf, stok produk tersebut sedang habis.');
        }

        $keranjang = auth()->user()->keranjangAktif();
        $sudahDiKeranjang = (int) $keranjang->detail()->where('produk_id', $produk->id)->value('jumlah');
        $diminta = $request->integer('jumlah', 1);

        if ($sudahDiKeranjang + $diminta > $produk->stok) {
            $diminta = max(1, $produk->stok - $sudahDiKeranjang);
            if ($diminta < 1) {
                return redirect()->route('keranjang.index')->with('peringatan', 'Jumlah di keranjang sudah mencapai batas stok yang tersedia.');
            }
            $keranjang->tambahProduk($produk, $diminta);

            return redirect()->route('keranjang.index')->with('berhasil', 'Jumlah disesuaikan dengan stok yang tersedia.');
        }

        $keranjang->tambahProduk($produk, $diminta);

        return redirect()->route('keranjang.index')->with('berhasil', 'Produk ditambahkan ke keranjang.');
    }

    public function ubah(Request $request, DetailPesanan $detail): RedirectResponse
    {
        abort_unless($detail->pesanan->user_id === auth()->id() && $detail->pesanan->status_pesanan === Pesanan::STATUS_KERANJANG, 403);

        $request->validate(['jumlah' => ['required', 'integer', 'min:1']]);

        if ($request->integer('jumlah') > $detail->produk->stok) {
            return back()->with('peringatan', 'Stok tidak cukup. Tersedia '.$detail->produk->stok.' buah.');
        }

        $detail->pesanan->ubahJumlah($detail, $request->integer('jumlah'));

        return back()->with('berhasil', 'Jumlah produk diperbarui.');
    }

    public function hapus(DetailPesanan $detail): RedirectResponse
    {
        abort_unless($detail->pesanan->user_id === auth()->id() && $detail->pesanan->status_pesanan === Pesanan::STATUS_KERANJANG, 403);

        $detail->pesanan->hapusDetail($detail);

        return back()->with('berhasil', 'Produk dihapus dari keranjang.');
    }

    public function kosongkan(): RedirectResponse
    {
        auth()->user()->keranjangAktif()->kosongkan();

        return back()->with('berhasil', 'Keranjang dikosongkan.');
    }
}
