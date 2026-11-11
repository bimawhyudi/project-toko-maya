<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanAlamatRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlamatController extends Controller
{
    public function index(): View
    {
        return view('alamat.index', ['daftar' => auth()->user()->alamat ?? []]);
    }

    public function store(SimpanAlamatRequest $request): RedirectResponse
    {
        $baru = $request->user()->tambahAlamat($request->validated());

        return redirect()->route('alamat.index')
            ->with('berhasil', 'Alamat "'.$baru['label'].'" tersimpan.');
    }

    public function update(SimpanAlamatRequest $request, string $alamat): RedirectResponse
    {
        $diubah = $request->user()->ubahAlamat($alamat, $request->validated());

        abort_if($diubah === null, 404, 'Alamat tidak ditemukan.');

        return redirect()->route('alamat.index')->with('berhasil', 'Alamat diperbarui.');
    }

    public function destroy(Request $request, string $alamat): RedirectResponse
    {
        abort_unless($request->user()->hapusAlamat($alamat), 404, 'Alamat tidak ditemukan.');

        return redirect()->route('alamat.index')->with('berhasil', 'Alamat dihapus.');
    }

    public function setDefault(Request $request, string $alamat): RedirectResponse
    {
        abort_unless($request->user()->setAlamatDefault($alamat), 404, 'Alamat tidak ditemukan.');

        return redirect()->route('alamat.index')->with('berhasil', 'Alamat utama diperbarui.');
    }
}
