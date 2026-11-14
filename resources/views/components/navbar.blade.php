{{-- Navbar utama putih. $aktif: beranda|katalog|pesanan|keranjang --}}
<nav class="sticky top-0 z-40 border-b border-garis bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-halaman px-4 sm:px-6">
        <div class="flex h-[72px] items-center gap-4">
            <x-logo-toko />

            <form action="{{ route('katalog.index') }}" method="GET" class="ms-2 hidden flex-1 md:block">
                <div class="relative max-w-xl">
                    <input type="search" name="q" value="{{ request('q') }}"
                        placeholder="Cari produk alat bangunan & bengkel…"
                        class="w-full rounded-full border border-garis bg-surface py-2.5 pe-4 ps-4 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20" />
                    <button type="submit" aria-label="Cari"
                        class="absolute end-1 top-1/2 -translate-y-1/2 rounded-full bg-primary px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-primary-dark">
                        Cari
                    </button>
                </div>
            </form>

            <div class="ms-auto flex items-center gap-1 md:gap-2">
                <x-nav-item href="{{ route('beranda') }}" :aktif="($aktif ?? '') === 'beranda'">Beranda</x-nav-item>
                <x-nav-item href="{{ route('katalog.index') }}" :aktif="($aktif ?? '') === 'katalog'">Katalog Produk</x-nav-item>
                @if (Route::has('pesanan.index'))
                    @auth
                        @if (auth()->user()->role === 'pelanggan')
                            <x-nav-item href="{{ route('pesanan.index') }}" :aktif="($aktif ?? '') === 'pesanan'">Pesanan Saya</x-nav-item>
                            <x-keranjang-ikon :aktif="($aktif ?? '') === 'keranjang'" />
                        @endif
                    @endauth
                @endif
            </div>

            <div class="ms-auto flex items-center md:ms-2">
                @auth
                    <x-menu-profil />
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-lg bg-primary px-5 py-2.5 font-aksen text-sm font-semibold text-white shadow-kartu transition hover:bg-primary-dark">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>