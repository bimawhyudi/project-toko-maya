<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPenjualanExport;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PemilikController extends Controller
{
    /**
     * Dasbor pemilik: omzet, grafik penjualan bulanan, stok menipis, top produk.
     */
    public function index(): View
    {
        $lunas = fn ($q) => $q->whereIn('status_pesanan', [
            Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SIAP_DIAMBIL,
            Pesanan::STATUS_DIKIRIM, Pesanan::STATUS_SELESAI,
        ]);

        $omzetHariIni = (float) Pesanan::where('metode_pemenuhan', '!=', Pesanan::PEMENUHAN_BELI_LANGSUNG)
            ->where($lunas)->whereDate('tanggal_pesanan', today())->sum(DB::raw('total_harga + ongkir'));

        $omzetKasirHariIni = (float) Pesanan::where('metode_pemenuhan', Pesanan::PEMENUHAN_BELI_LANGSUNG)
            ->where($lunas)->whereDate('tanggal_pesanan', today())->sum('total_harga');

        $omzetTotal = (float) Pesanan::where($lunas)->sum(DB::raw('total_harga + ongkir'));
        $omzetOnline = (float) Pesanan::where($lunas)->where('metode_pemenuhan', '!=', Pesanan::PEMENUHAN_BELI_LANGSUNG)->sum(DB::raw('total_harga + ongkir'));
        $omzetKasir = (float) Pesanan::where($lunas)->where('metode_pemenuhan', Pesanan::PEMENUHAN_BELI_LANGSUNG)->sum('total_harga');

        // Grafik 12 bulan terakhir: online vs kasir POS.
        $bulanan = [];
        foreach (range(11, 0) as $i) {
            $bulan = now()->subMonths($i)->startOfMonth();
            $akhir = $bulan->copy()->endOfMonth();

            $online = (float) Pesanan::where($lunas)->whereBetween('tanggal_pesanan', [$bulan, $akhir])
                ->where('metode_pemenuhan', '!=', Pesanan::PEMENUHAN_BELI_LANGSUNG)->sum(DB::raw('total_harga + ongkir'));
            $kasir = (float) Pesanan::where($lunas)->whereBetween('tanggal_pesanan', [$bulan, $akhir])
                ->where('metode_pemenuhan', Pesanan::PEMENUHAN_BELI_LANGSUNG)->sum('total_harga');

            $bulanan[] = [
                'label' => $bulan->translatedFormat('M y'),
                'online' => $online,
                'kasir' => $kasir,
            ];
        }
        $maksBulanan = max(1, ...array_merge(
            array_column($bulanan, 'online'),
            array_column($bulanan, 'kasir'),
        ));

        $stokMenipis = Produk::with('kategori')
            ->where('stok', '<=', config('toko.ambang_stok_menipis', 5))
            ->orderBy('stok')->take(8)->get();

        $topProduk = Produk::query()
            ->select('produk.*', DB::raw('COALESCE(SUM(detail_pesanan.jumlah), 0) AS terjual'))
            ->join('detail_pesanan', 'detail_pesanan.produk_id', '=', 'produk.id')
            ->join('pesanan', 'pesanan.id', '=', 'detail_pesanan.pesanan_id')
            ->where('pesanan.status_pesanan', '!=', Pesanan::STATUS_KERANJANG)
            ->groupBy('produk.id')
            ->orderByDesc('terjual')->take(5)->get();

        return view('pemilik.index', [
            'omzetHariIni' => $omzetHariIni,
            'omzetKasirHariIni' => $omzetKasirHariIni,
            'omzetTotal' => $omzetTotal,
            'omzetOnline' => $omzetOnline,
            'omzetKasir' => $omzetKasir,
            'transaksiSelesai' => Pesanan::where($lunas)->count(),
            'jumlahProduk' => Produk::count(),
            'jumlahPengguna' => User::count(),
            'bulanan' => $bulanan,
            'maksBulanan' => $maksBulanan,
            'stokMenipis' => $stokMenipis,
            'topProduk' => $topProduk,
        ]);
    }

    /**
     * CRUD produk (termasuk upload gambar, filter kategori, dan pencarian).
     */
    public function produk(Request $request): View
    {
        $kataKunci = $request->string('q')->trim()->toString();
        $kategoriDipilih = $request->filled('kategori') ? $request->integer('kategori') : null;

        $produk = Produk::with('kategori')
            ->when($kategoriDipilih, fn ($q) => $q->where('kategori_id', $kategoriDipilih))
            ->when($kataKunci !== '', fn ($q) => $q->where('nama_produk', 'like', '%'.$kataKunci.'%'))
            ->orderBy('nama_produk')
            ->paginate(15)
            ->withQueryString();

        return view('pemilik.produk', [
            'produk' => $produk,
            'kategori' => Kategori::orderBy('nama_kategori')->get(),
            'kataKunci' => $kataKunci,
            'kategoriDipilih' => $kategoriDipilih,
        ]);
    }

    public function simpanProduk(Request $request): RedirectResponse
    {
        $data = $this->validasiProduk($request);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        Produk::create($data);

        return redirect()->route('pemilik.produk')->with('berhasil', 'Produk baru ditambahkan ke katalog.');
    }

    public function ubahProduk(Request $request, Produk $produk): RedirectResponse
    {
        $data = $this->validasiProduk($request);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        $produk->update($data);

        return back()->with('berhasil', 'Produk diperbarui.');
    }

    public function hapusProduk(Produk $produk): RedirectResponse
    {
        $produk->delete();

        return back()->with('berhasil', 'Produk dihapus dari katalog.');
    }

    private function validasiProduk(Request $request): array
    {
        return $request->validate([
            'nama_produk' => ['required', 'string', 'max:150'],
            'kategori_id' => ['required', 'exists:kategori,id'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'gambar' => ['nullable', 'image', 'max:2048'],
        ], [], [
            'nama_produk' => 'nama produk',
            'kategori_id' => 'kategori',
        ]);
    }

    /**
     * CRUD kategori ringkas.
     */
    public function simpanKategori(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:kategori,nama_kategori'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ]);

        Kategori::create($data);

        return back()->with('berhasil', 'Kategori "'.$data['nama_kategori'].'" ditambahkan.');
    }

    public function hapusKategori(Kategori $kategori): RedirectResponse
    {
        abort_if($kategori->produk()->count() > 0, 403, 'Kategori masih dipakai produk. Pindahkan produknya dulu.');

        $kategori->delete();

        return back()->with('berhasil', 'Kategori dihapus.');
    }

    /**
     * Kelola staf: daftar akun + ubah peran.
     */
    public function staf(): View
    {
        return view('pemilik.staf', [
            'pengguna' => User::orderBy('role')->orderBy('nama')->paginate(15),
        ]);
    }

    public function ubahPeran(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['pemilik'], true), 403);
        abort_if($user->id === $request->user()->id, 403, 'Tidak bisa mengubah peran akun sendiri.');

        $data = $request->validate(['role' => ['required', 'in:pelanggan,kasir,admin_gudang,pemilik']]);

        $user->update($data);

        return back()->with('berhasil', 'Peran akun '.$user->nama.' diubah menjadi '.$data['role'].'.');
    }

    /**
     * Laporan penjualan per periode + ekspor Excel/CSV & PDF.
     */
    public function laporan(Request $request): View
    {
        return view('pemilik.laporan', $this->dataLaporan($request));
    }

    public function exportExcel(Request $request): RedirectResponse|StreamedResponse|BinaryFileResponse
    {
        $d = $this->dataLaporan($request);

        if (extension_loaded('zip')) {
            return Excel::download(new LaporanPenjualanExport($d['baris'], $d['rekap']), 'laporan-penjualan.xlsx');
        }

        // Bila ekstensi PHP zip belum aktif (umum di Laragon), keluarkan CSV
        // yang tetap bisa dibuka langsung di Excel.
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, $d['rekap']);
        fputcsv($csv, []);
        fputcsv($csv, ['Kode Pesanan', 'Tanggal', 'Pelanggan', 'Kanal', 'Metode Pembayaran', 'Ongkir (Rp)', 'Total (Rp)', 'Status']);
        foreach ($d['baris'] as $b) {
            fputcsv($csv, $b);
        }
        rewind($csv);

        return response()->streamDownload(fn () => fpassthru($csv), 'laporan-penjualan.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $d = $this->dataLaporan($request);

        $pdf = Pdf::loadView('pemilik.laporan-pdf', $d)
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-penjualan.pdf');
    }

    private function dataLaporan(Request $request): array
    {
        $mulai = $request->date('mulai') ?? now()->startOfMonth();
        $sampai = $request->date('sampai') ?? now();

        $pesanan = Pesanan::whereIn('status_pesanan', [
            Pesanan::STATUS_DIPROSES, Pesanan::STATUS_SIAP_DIAMBIL,
            Pesanan::STATUS_DIKIRIM, Pesanan::STATUS_SELESAI,
        ])
            ->whereBetween('tanggal_pesanan', [$mulai->startOfDay(), $sampai->endOfDay()])
            ->with(['detail', 'user'])
            ->orderBy('tanggal_pesanan')
            ->get();

        $online = $pesanan->where('metode_pemenuhan', '!=', Pesanan::PEMENUHAN_BELI_LANGSUNG);
        $kasir = $pesanan->where('metode_pemenuhan', Pesanan::PEMENUHAN_BELI_LANGSUNG);

        $metodeTerfavorit = $pesanan->groupBy('metode_pembayaran')
            ->map(fn ($grup) => $grup->count())
            ->sortDesc()
            ->mapWithKeys(fn ($v, $k) => [($k === Pesanan::PEMENUHAN_BELI_LANGSUNG ? $k : $k) => $v])
            ->keys()->first() ?? '-';

        $rekap = [
            'Periode: '.$mulai->format('d M Y').' s.d. '.$sampai->format('d M Y'),
            'Total omzet: Rp '.number_format($pesanan->sum(fn ($p) => $p->total_harga + $p->ongkir), 0, ',', '.'),
            'Total transaksi: '.$pesanan->count(),
            'Omzet online: Rp '.number_format($online->sum(fn ($p) => $p->total_harga + $p->ongkir), 0, ',', '.'),
            'Omzet kasir: Rp '.number_format($kasir->sum('total_harga'), 0, ',', '.'),
            'Metode pembayaran terfavorit: '.($metodeTerfavorit === Pesanan::PEMENUHAN_BELI_LANGSUNG ? 'Tunai/QRIS Kasir' : $metodeTerfavorit),
        ];

        $baris = $pesanan->map(fn ($p) => [
            $p->kode_pesanan,
            $p->tanggal_pesanan?->format('d/m/Y H:i'),
            $p->user?->nama ?? 'Walk-in',
            $p->metode_pemenuhan === Pesanan::PEMENUHAN_BELI_LANGSUNG ? 'Kasir POS' : ($p->metode_pemenuhan === Pesanan::PEMENUHAN_KURIR ? 'Online - Kurir' : 'Online - Ambil di Toko'),
            $p->metode_pembayaran ?? '-',
            (float) $p->ongkir,
            (float) ($p->total_harga + $p->ongkir),
            $p->status_pesanan,
        ])->all();

        return [
            'pesanan' => $pesanan,
            'rekap' => $rekap,
            'baris' => $baris,
            'mulai' => $mulai,
            'sampai' => $sampai,
        ];
    }
}