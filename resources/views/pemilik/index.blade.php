<x-layout-staf>
    @section('judul', 'Pemilik — Dashboard')

    <x-banner-staf badge="Portal Eksekutif Pemilik Toko" judul="Dashboard Pemilik Toko Maya"
        sub="Analisis omzet penjualan terpadu (online & POS fisik), kendali inventaris katalog produk, dan manajemen hak akses pengguna.">
        <x-slot:aksi>
            <a href="{{ route('pemilik.produk') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Tambah Produk baru</a>
            <a href="{{ route('pemilik.laporan') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Laporan Penjualan</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6">
        <div class="flex flex-wrap gap-2">
            <span class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-kartu">Dashboard Utama</span>
            <a href="{{ route('pemilik.laporan') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-primary-mist hover:text-primary">Laporan penjualan & keuangan</a>
            <a href="{{ route('pemilik.produk') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-primary-mist hover:text-primary">Manajemen Produk</a>
            <a href="{{ route('pemilik.staf') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-primary-mist hover:text-primary">Manajemen Pengguna</a>
        </div>

        {{-- KARTU STATISTIK --}}
        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Omzet Hari Ini</p>
                <p class="mt-1 font-sans text-3xl font-bold">Rp {{ number_format($omzetHariIni + $omzetKasirHariIni, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-gray-400">Online Rp {{ number_format($omzetHariIni, 0, ',', '.') }} · Kasir POS Rp {{ number_format($omzetKasirHariIni, 0, ',', '.') }}</p>
            </div>
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Total Omzet Penjualan</p>
                <p class="mt-1 font-sans text-3xl font-bold">Rp {{ number_format($omzetTotal, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs text-gray-400">Online Rp {{ number_format($omzetOnline, 0, ',', '.') }} · Kasir POS Rp {{ number_format($omzetKasir, 0, ',', '.') }}</p>
            </div>
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Total Transaksi Lunas</p>
                <p class="mt-1 font-sans text-3xl font-bold">{{ $transaksiSelesai }}</p>
                <p class="mt-2 text-xs text-gray-400">semua kanal, tanpa keranjang</p>
            </div>
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Inventaris & Pengguna</p>
                <p class="mt-1 font-sans text-3xl font-bold">{{ $jumlahProduk }} SKU</p>
                <p class="mt-2 text-xs text-gray-400">{{ $jumlahPengguna }} akun terdaftar</p>
            </div>
        </div>

        {{-- GRAFIK BULANAN --}}
        <div class="kartu mt-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-aksen text-lg font-bold">Grafik Penjualan Bulanan (Online vs Kasir POS)</h2>
                    <p class="mt-0.5 text-sm text-gray-500">Perbandingan omzet per bulan, 12 bulan terakhir.</p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-sukses"></span> Online</span>
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-primary"></span> Kasir POS</span>
                </div>
            </div>

            <div class="mt-6 flex h-56 items-end gap-3 overflow-x-auto border-b border-garis pb-0">
                @foreach ($bulanan as $b)
                    <div class="flex min-w-14 flex-1 flex-col items-center justify-end gap-0">
                        <div class="flex h-48 w-full items-end justify-center gap-1">
                            <div class="w-3.5 rounded-t bg-sukses transition-all hover:opacity-80" style="height: {{ max(2, round($b['online'] / $maksBulanan * 100)) }}%" title="Online: Rp {{ number_format($b['online'], 0, ',', '.') }}"></div>
                            <div class="w-3.5 rounded-t bg-primary transition-all hover:opacity-80" style="height: {{ max(2, round($b['kasir'] / $maksBulanan * 100)) }}%" title="Kasir: Rp {{ number_format($b['kasir'], 0, ',', '.') }}"></div>
                        </div>
                        <span class="mt-2 text-[11px] font-medium text-gray-500">{{ $b['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- STOK MENIPIS --}}
            <div class="kartu p-6">
                <h2 class="font-aksen text-lg font-bold">⚠️ Peringatan Stok Menipis / Habis</h2>
                <p class="mt-0.5 text-sm text-gray-500">Ambang: ≤ {{ config('toko.ambang_stok_menipis', 5) }} buah.</p>
                <div class="mt-4 divide-y divide-garis/70">
                    @forelse ($stokMenipis as $s)
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $s->nama_produk }}</p>
                                <p class="text-xs text-gray-400">{{ $s->kategori->nama_kategori }}</p>
                            </div>
                            @if ($s->stok <= 0)
                                <span class="rounded-full bg-bahaya/10 px-3 py-1 text-xs font-bold text-bahaya">HABIS</span>
                            @else
                                <span class="rounded-full bg-rating/20 px-3 py-1 text-xs font-bold text-yellow-800">{{ $s->stok }} buah</span>
                            @endif
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-400">Semua stok aman. 👍</p>
                    @endforelse
                </div>
                <a href="{{ route('gudang.stok', ['filter' => 'menipis']) }}" class="mt-3 inline-block text-sm font-semibold text-primary hover:text-primary-dark">Kelola stok di gudang →</a>
            </div>

            {{-- TOP PRODUK --}}
            <div class="kartu p-6">
                <h2 class="font-aksen text-lg font-bold">Top 5 Produk Terlaris Toko Maya</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($topProduk as $i => $t)
                        <div class="flex items-center gap-3 rounded-lg border border-garis p-3">
                            <span class="font-aksen text-xl font-bold text-gray-300">{{ $i + 1 }}.</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">{{ $t->nama_produk }}</p>
                                <p class="text-xs text-gray-400">{{ $t->kategori->nama_kategori }} · Rp {{ number_format($t->harga, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-end">
                                <p class="text-sm font-bold text-sukses-teks">{{ number_format($t->terjual) }} terjual</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-400">Belum ada penjualan tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layout-staf>