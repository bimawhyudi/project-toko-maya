<x-layout-publik aktif="beranda">
    @php
        $perluRestock = \App\Models\Produk::where('stok', '<=', config('toko.ambang_stok_menipis'))->count();
    @endphp

    {{-- HERO — latar putih/slate, teks hitam + biru, foto toko fisik asli sesuai desain Figma --}}
    <section class="border-b border-garis bg-slate-50 py-4 sm:py-6">
        <div class="mx-auto grid max-w-halaman items-center gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[1.1fr_0.9fr] lg:gap-12 lg:py-14">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-primary-soft px-4 py-1.5 text-[13px] font-semibold text-primary-darker">
                    <span class="h-2 w-2 rounded-full bg-sukses"></span>
                    {{ number_format(\App\Models\Produk::count()) }} produk tersedia di toko
                </span>

                <h1 class="mt-4 font-sans text-3xl font-bold leading-tight tracking-tight text-gray-900 sm:text-4xl lg:text-[42px]">
                    Belanja Alat Bangunan
                    <span class="text-primary">Mudah, Cepat, dan Praktis</span>
                </h1>

                <p class="mt-4 max-w-lg text-base text-gray-500">
                    Pesan online sekarang. Anda bisa <span class="text-primary">ambil sendiri di toko </span>atau <span class="text-primary">pengiriman dengan kurir </span>untuk pesanan luar kota.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('katalog.index') }}"
                        class="rounded-full bg-primary px-7 py-3 font-aksen text-sm font-bold text-white shadow-kartu transition hover:-translate-y-0.5 hover:bg-primary-dark">
                        Mulai Belanja
                    </a>
                    <a href="{{ route('katalog.index', ['urutkan' => 'terlaris']) }}"
                        class="rounded-full border-2 border-primary bg-white px-7 py-3 font-aksen text-sm font-bold text-primary transition hover:bg-primary-mist">
                        Lihat Katalog
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="font-bold text-sukses">✔</span> Bayar aman via Midtrans</span>
                    <span class="flex items-center gap-1.5"><span class="font-bold text-sukses">✔</span> Gratis ongkir ambil di toko</span>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                <div class="absolute -inset-2 rounded-[18px] bg-primary-soft/60" aria-hidden="true"></div>

                <img
                    src="{{ asset('images/toko-asli.png') }}"
                    alt="Toko Maya — Baut, Perbengkelan, Teknik, Pasar Raya Barat Blok B No. 18 Padang"
                    class="relative aspect-video w-full rounded-kartu border border-garis object-cover object-top shadow-terangkat"
                >
            </div>
        </div>
    </section>

    {{-- KATEGORI POPULER --}}
    <section class="mx-auto max-w-halaman px-4 pt-12 sm:px-6">
        <h2 class="font-sans text-2xl font-bold tracking-tight sm:text-3xl">Kategori Populer</h2>
        <p class="mt-1 text-sm text-gray-500">Pilih kategori untuk melihat perkakas dan perlengkapan bangunan.</p>

        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($kategoriPopuler as $k)
                <a href="{{ route('katalog.index', ['kategori' => $k->id]) }}"
                    class="group flex flex-col items-center gap-3 rounded-kartu border border-garis bg-white p-6 text-center shadow-kartu transition hover:-translate-y-0.5 hover:border-primary hover:shadow-terangkat">
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-primary-mist text-primary transition group-hover:bg-primary group-hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085" />
                        </svg>
                    </span>
                    <span class="text-sm font-semibold leading-snug text-gray-900 group-hover:text-primary">{{ $k->nama_kategori }}</span>
                    <span class="text-xs text-gray-400">{{ $k->produk_count }} produk</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- PRODUK TERLARIS --}}
    <section class="mx-auto max-w-halaman px-4 pt-14 sm:px-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="font-sans text-2xl font-bold tracking-tight sm:text-3xl">Produk Terlaris</h2>
                <p class="mt-1 text-sm text-gray-500">Paling banyak dibeli oleh pelanggan.</p>
            </div>
            <a href="{{ route('katalog.index') }}" class="text-sm font-semibold text-primary hover:text-primary-dark">Lihat semua →</a>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @forelse ($terlaris as $p)
                <x-kartu-produk :produk="$p" />
            @empty
                <p class="col-span-full text-sm text-gray-500">Belum ada penjualan tercatat.</p>
            @endforelse
        </div>
    </section>

    {{-- PRODUK TERBARU --}}
    <section class="mx-auto max-w-halaman px-4 pt-14 sm:px-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="font-sans text-2xl font-bold tracking-tight sm:text-3xl">Produk Terbaru</h2>
                <p class="mt-1 text-sm text-gray-500">Produk terbaru yang baru saja ditambahkan.</p>
            </div>
            <a href="{{ route('katalog.index', ['urutkan' => 'terlaris']) }}" class="text-sm font-semibold text-primary hover:text-primary-dark">Lihat semua →</a>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($terbaru as $p)
                <x-kartu-produk :produk="$p" />
            @endforeach
        </div>
    </section>

    {{-- INFO LAYANAN --}}
    <section class="mx-auto max-w-halaman px-4 pt-14 sm:px-6">
        <div class="grid gap-4 rounded-kartu border border-garis bg-white p-6 shadow-kartu sm:grid-cols-3 sm:p-8">
            <div class="flex gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-primary-mist text-primary">🛒</span>
                <div>
                    <p class="font-semibold">Pesan Online</p>
                    <p class="mt-1 text-sm text-gray-500">Bayar aman lewat Midtrans (QRIS / Virtual Account).</p>
                </div>
            </div>
            <div class="flex gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-primary-mist text-primary">🏪</span>
                <div>
                    <p class="font-semibold">Ambil di Toko</p>
                    <p class="mt-1 text-sm text-gray-500">Gratis ongkir. Tunjukkan kode pesanan ke kasir.</p>
                </div>
            </div>
            <div class="flex gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-primary-mist text-primary">🚚</span>
                <div>
                    <p class="font-semibold">Kirim via Kurir</p>
                    <p class="mt-1 text-sm text-gray-500">Ongkir dihitung otomatis</p>
                </div>
            </div>
        </div>
    </section>
</x-layout-publik>