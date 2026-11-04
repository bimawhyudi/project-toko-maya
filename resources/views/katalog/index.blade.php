<x-layout-publik aktif="katalog">
    <div class="mx-auto max-w-halaman px-4 py-8 sm:px-6">
        <h1 class="judul-halaman">Katalog Alat Bangunan & Bengkel</h1>
        <p class="mt-1 text-sm text-gray-500">
            Menampilkan {{ $produk->count() }} produk{{ $produk->total() > $produk->count() ? ' — total ' . $produk->total() . ' produk Toko Maya' : ' dari total ' . $produk->total() . ' inventaris Toko Maya' }}
        </p>

        <div class="mt-6 grid gap-6 lg:grid-cols-[260px_1fr]">
            {{-- SIDEBAR KATEGORI --}}
            <aside class="h-fit rounded-kartu border border-garis bg-white p-5 shadow-kartu lg:sticky lg:top-24">
                <h2 class="font-aksen text-lg font-bold tracking-wide">KATEGORI</h2>
                <ul class="mt-4 space-y-1.5">
                    <li>
                        <a href="{{ route('katalog.index', array_filter(request()->only('q', 'urutkan'))) }}"
                            @class([
                                'block rounded-lg px-3 py-2.5 text-sm font-semibold transition',
                                'bg-primary text-white shadow-kartu' => ! $kategoriAktif,
                                'text-gray-700 hover:bg-primary-mist hover:text-primary' => (bool) $kategoriAktif,
                            ])>
                            Semua Kategori
                        </a>
                    </li>
                    @foreach ($kategori as $k)
                        <li>
                            <a href="{{ route('katalog.index', array_filter(['kategori' => $k->id, 'q' => request('q'), 'urutkan' => request('urutkan')])) }}"
                                @class([
                                    'block rounded-lg px-3 py-2.5 text-sm font-semibold transition',
                                    'bg-primary text-white shadow-kartu' => $kategoriAktif?->id === $k->id,
                                    'text-gray-700 hover:bg-primary-mist hover:text-primary' => $kategoriAktif?->id !== $k->id,
                                ])>
                                {{ $k->nama_kategori }}
                                <span class="float-end text-xs opacity-70">{{ $k->produk_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>

            {{-- DAFTAR PRODUK --}}
            <div>
                <div class="flex flex-wrap items-center gap-3 rounded-kartu border border-garis bg-white p-4 shadow-kartu">
                    <form action="{{ route('katalog.index') }}" method="GET" class="flex flex-1 flex-wrap items-center gap-3">
                        @if ($kategoriAktif)
                            <input type="hidden" name="kategori" value="{{ $kategoriAktif->id }}">
                        @endif
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…"
                            class="min-w-40 flex-1 rounded-lg border border-garis bg-surface px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <select name="urutkan"
                            class="rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                            <option value="" @selected(! request('urutkan'))>Urutkan: Nama A–Z</option>
                            <option value="terlaris" @selected(request('urutkan') === 'terlaris')>Terbaru</option>
                            <option value="termurah" @selected(request('urutkan') === 'termurah')>Harga Termurah</option>
                            <option value="termahal" @selected(request('urutkan') === 'termahal')>Harga Tertinggi</option>
                        </select>
                        <button type="submit"
                            class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Terapkan
                        </button>
                    </form>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                    @forelse ($produk as $p)
                        <x-kartu-produk :produk="$p" />
                    @empty
                        <div class="col-span-full grid place-items-center rounded-kartu border border-dashed border-garis bg-white py-16 text-center">
                            <p class="text-lg font-semibold text-gray-700">Produk tidak ditemukan</p>
                            <p class="mt-1 text-sm text-gray-500">Coba kata kunci lain atau pilih kategori yang berbeda.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $produk->links() }}
                </div>
            </div>
        </div>
    </div>
</x-layout-publik>
