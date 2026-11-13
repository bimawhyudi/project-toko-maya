<x-layout-publik aktif="keranjang">
    @section('judul', 'Keranjang Belanja')

    <div class="mx-auto max-w-halaman px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Keranjang Belanja Anda</h1>
                <p class="mt-1 text-sm text-gray-500">Periksa item alat bangunan & bengkel sebelum melanjutkan ke pembayaran</p>
            </div>
            @if ($keranjang->detail->isNotEmpty())
                <form action="{{ route('keranjang.kosongkan') }}" method="POST"
                    x-data @submit.prevent="if (confirm('Kosongkan seluruh keranjang?')) this.submit()">
                    @csrf
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-bahaya transition hover:bg-bahaya/10">
                        Kosongkan Keranjang
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="kartu divide-y divide-garis/70">
                @forelse ($keranjang->detail as $d)
                    <div class="flex flex-wrap items-center gap-4 p-4 sm:p-5">
                        <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-lg bg-primary-mist">
                            @if ($d->produk->gambar)
                                <img src="{{ asset('storage/' . $d->produk->gambar) }}" alt="" class="h-full w-full object-cover">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8 text-primary/40">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-40 flex-1">
                            <a href="{{ route('produk.show', $d->produk) }}" class="text-sm font-semibold uppercase leading-snug hover:text-primary">
                                {{ $d->produk->nama_produk }}
                            </a>
                            <p class="mt-0.5 text-sm text-gray-500">Harga: Rp {{ number_format($d->harga, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-400">Stok tersedia: {{ $d->produk->stok }} buah</p>
                        </div>

                        <form action="{{ route('keranjang.ubah', $d) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <div class="flex items-center rounded-lg border border-garis">
                                <button type="submit" name="jumlah" value="{{ $d->jumlah - 1 }}"
                                    @disabled($d->jumlah <= 1)
                                    class="grid h-9 w-9 place-items-center text-gray-500 transition hover:text-primary disabled:opacity-30">−</button>
                                <span class="w-10 text-center text-sm font-semibold">{{ $d->jumlah }}</span>
                                <button type="submit" name="jumlah" value="{{ $d->jumlah + 1 }}"
                                    @disabled($d->jumlah >= $d->produk->stok)
                                    class="grid h-9 w-9 place-items-center text-gray-500 transition hover:text-primary disabled:opacity-30">+</button>
                            </div>
                        </form>

                        <div class="w-28 text-end">
                            <p class="text-xs text-gray-400">Subtotal</p>
                            <p class="font-aksen text-base font-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</p>
                        </div>

                        <form action="{{ route('keranjang.hapus', $d) }}" method="POST"
                            x-data @submit.prevent="if (confirm('Hapus produk ini dari keranjang?')) this.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" aria-label="Hapus"
                                class="grid h-9 w-9 place-items-center rounded-lg bg-bahaya/10 text-bahaya transition hover:bg-bahaya hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="grid place-items-center gap-3 py-16 text-center">
                        <span class="text-5xl">🛒</span>
                        <p class="text-lg font-semibold text-gray-700">Keranjang Anda masih kosong</p>
                        <p class="text-sm text-gray-500">Yuk, pilih perkakas andalan Anda di katalog produk.</p>
                        <a href="{{ route('katalog.index') }}"
                            class="mt-2 rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Mulai Belanja
                        </a>
                    </div>
                @endforelse
            </div>

            <aside class="h-fit kartu p-5 lg:sticky lg:top-24">
                <h2 class="font-aksen text-lg font-bold">Ringkasan Belanja</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-500">Total produk</dt>
                        <dd class="font-semibold">({{ $keranjang->detail->count() }} item) {{ $keranjang->detail->sum('jumlah') }} buah</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-500">Subtotal</dt>
                        <dd class="font-semibold">Rp {{ number_format($keranjang->total_harga, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-gray-500">Estimasi ongkir</dt>
                        <dd class="text-xs text-gray-400">Dihitung saat checkout</dd>
                    </div>
                    <div class="flex justify-between gap-2 border-t border-garis pt-3 text-base">
                        <dt class="font-semibold">Total Pembayaran</dt>
                        <dd class="font-aksen font-bold text-primary">Rp {{ number_format($keranjang->total_harga, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <a href="{{ route('checkout.index') }}"
                    @class([
                        'mt-5 block w-full rounded-lg px-4 py-3.5 text-center font-aksen text-sm font-semibold text-white transition',
                        'bg-primary hover:bg-primary-dark shadow-kartu' => $keranjang->detail->isNotEmpty(),
                        'pointer-events-none bg-gray-200 text-gray-400' => $keranjang->detail->isEmpty(),
                    ])>
                    Lanjutkan ke Checkout
                </a>
                <a href="{{ route('katalog.index') }}" class="mt-2 block w-full rounded-lg border border-garis px-4 py-3 text-center text-sm font-semibold text-gray-700 transition hover:bg-surface">
                    Tambah produk lain
                </a>
            </aside>
        </div>
    </div>
</x-layout-publik>
