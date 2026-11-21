{{-- Navbar utama putih. $aktif: beranda|katalog|pesanan|keranjang --}}
<nav x-data="{ menuTerbuka: false }" class="sticky top-0 z-40 border-b border-garis bg-white/95 backdrop-blur">
    <div class="mx-auto max-w-halaman px-4 sm:px-6">
        <div class="flex h-16 items-center gap-3 md:h-[72px] md:gap-4">
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

            {{-- Menu horizontal (desktop) --}}
            <div class="ms-auto hidden items-center gap-1 md:flex md:gap-2">
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

            <div class="ms-auto hidden items-center md:flex md:ms-2">
                @auth
                    <x-menu-profil />
                @else
                    <a href="{{ route('login') }}"
                        class="rounded-lg bg-primary px-4 py-2.5 font-aksen text-sm font-semibold text-white shadow-kartu transition hover:bg-primary-dark sm:px-5">
                        Masuk
                    </a>
                @endauth
            </div>

            {{-- Tombol menu (HP) --}}
            <button type="button" @click="menuTerbuka = ! menuTerbuka"
                class="ms-auto grid h-10 w-10 place-items-center rounded-lg border border-garis text-gray-700 transition hover:bg-primary-soft md:hidden"
                :class="menuTerbuka ? 'bg-primary text-white' : ''"
                :aria-expanded="menuTerbuka ? 'true' : 'false'" aria-label="Buka menu navigasi">
                <svg x-show="! menuTerbuka" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="menuTerbuka" style="display:none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Panel menu (HP) --}}
    <div x-show="menuTerbuka" x-cloak @click.outside="menuTerbuka = false" @keydown.escape.window="menuTerbuka = false"
        class="border-t border-garis bg-white md:hidden">
        <div class="mx-auto max-w-halaman space-y-1 px-4 py-3 sm:px-6">
            @guest
                <a href="{{ route('login') }}"
                    class="block rounded-lg bg-primary px-3 py-2.5 text-center text-sm font-semibold text-white shadow-kartu">
                    Masuk
                </a>
            @endguest

            <a href="{{ route('beranda') }}"
                @class([
                    'block rounded-lg px-3 py-2.5 text-sm font-semibold',
                    'bg-primary-soft text-primary' => ($aktif ?? '') === 'beranda',
                    'text-gray-700 hover:bg-primary-soft hover:text-primary' => ($aktif ?? '') !== 'beranda',
                ])>Beranda</a>
            <a href="{{ route('katalog.index') }}"
                @class([
                    'block rounded-lg px-3 py-2.5 text-sm font-semibold',
                    'bg-primary-soft text-primary' => ($aktif ?? '') === 'katalog',
                    'text-gray-700 hover:bg-primary-soft hover:text-primary' => ($aktif ?? '') !== 'katalog',
                ])>Katalog Produk</a>

            @auth
                @if (auth()->user()->role === 'pelanggan' && Route::has('pesanan.index'))
                    <a href="{{ route('pesanan.index') }}"
                        @class([
                            'block rounded-lg px-3 py-2.5 text-sm font-semibold',
                            'bg-primary-soft text-primary' => ($aktif ?? '') === 'pesanan',
                            'text-gray-700 hover:bg-primary-soft hover:text-primary' => ($aktif ?? '') !== 'pesanan',
                        ])>Pesanan Saya</a>
                    <a href="{{ route('keranjang.index') }}"
                        @class([
                            'flex items-center justify-between rounded-lg px-3 py-2.5 text-sm font-semibold',
                            'bg-primary-soft text-primary' => ($aktif ?? '') === 'keranjang',
                            'text-gray-700 hover:bg-primary-soft hover:text-primary' => ($aktif ?? '') !== 'keranjang',
                        ])>Keranjang Belanja
                        @php $jumlahItem = auth()->user()->jumlahItemKeranjang(); @endphp
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $jumlahItem > 0 ? 'bg-bahaya text-white' : 'bg-gray-200 text-gray-600' }}">{{ $jumlahItem }}</span>
                    </a>
                @endif

                <div class="my-2 border-t border-garis"></div>
                <a href="{{ route('profile.edit') }}"
                    class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-primary-soft hover:text-primary">Profil Saya</a>
                @if (auth()->user()->role === 'pelanggan')
                    <a href="{{ route('alamat.index') }}"
                        class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-primary-soft hover:text-primary">Buku Alamat</a>
                @endif
                @if (in_array(auth()->user()->role, ['kasir', 'admin_gudang', 'pemilik']))
                    <a href="{{ route('dasbor') }}"
                        class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-primary-soft hover:text-primary">Dasbor Staf</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" @submit="menuTerbuka = false">
                    @csrf
                    <button type="submit"
                        class="w-full rounded-lg px-3 py-2.5 text-start text-sm font-semibold text-bahaya hover:bg-red-50">
                        Keluar
                    </button>
                </form>
            @endauth
        </div>
    </div>
</nav>