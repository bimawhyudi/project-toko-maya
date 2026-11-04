<x-layout-publik>
    @section('judul', $produk->nama_produk)

    <div class="mx-auto max-w-halaman px-4 py-8 sm:px-6">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500" aria-label="breadcrumb">
            <a href="{{ route('beranda') }}" class="hover:text-primary">Beranda</a>
            <span class="mx-1">/</span>
            <a href="{{ route('katalog.index') }}" class="hover:text-primary">Katalog</a>
            <span class="mx-1">/</span>
            <a href="{{ route('katalog.index', ['kategori' => $produk->kategori_id]) }}" class="hover:text-primary">{{ $produk->kategori->nama_kategori }}</a>
        </nav>

        <div class="mt-4 grid gap-8 lg:grid-cols-[480px_1fr]">
            {{-- GAMBAR --}}
            <div class="overflow-hidden rounded-kartu border border-garis bg-white shadow-kartu">
                <div class="aspect-square w-full bg-primary-mist">
                    @if ($produk->gambar)
                        <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama_produk }}" class="h-full w-full object-cover">
                    @else
                        <div class="grid h-full w-full place-items-center text-primary/30">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" class="h-32 w-32">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- INFO --}}
            <div>
                <span class="rounded-full bg-primary-soft px-3 py-1 text-xs font-semibold text-primary-darker">{{ $produk->kategori->nama_kategori }}</span>
                <h1 class="mt-3 font-sans text-3xl font-bold leading-snug tracking-tight">{{ $produk->nama_produk }}</h1>

                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                    <span class="font-semibold text-gray-700">{{ number_format($terjual) }} terjual</span>
                    <span>•</span>
                    <span>Kode produk: #{{ str_pad($produk->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>

                <p class="mt-5 font-aksen text-4xl font-bold text-primary">
                    Rp {{ number_format($produk->harga, 0, ',', '.') }}
                </p>

                <div class="mt-4">
                    @if ($produk->stok <= 0)
                        <span class="rounded-full bg-bahaya/10 px-4 py-1.5 text-sm font-semibold text-bahaya">Stok Habis</span>
                    @else
                        <span class="rounded-full bg-sukses/10 px-4 py-1.5 text-sm font-semibold text-sukses-teks">
                            Stok tersedia: {{ $produk->stok }} buah
                        </span>
                        @if ($produk->stokMenipis)
                            <span class="ms-2 rounded-full bg-rating/20 px-4 py-1.5 text-sm font-semibold text-yellow-800">Stok menipis, segera pesan</span>
                        @endif
                    @endif
                </div>

                @if ($produk->deskripsi)
                    <div class="mt-5 rounded-kartu border border-garis bg-white p-5">
                        <h2 class="font-semibold">Deskripsi</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $produk->deskripsi }}</p>
                    </div>
                @endif

                {{-- Beli --}}
                <div class="mt-6 rounded-kartu border border-garis bg-white p-5 shadow-kartu">
                    @auth
                        @if (in_array(auth()->user()->role, ['pelanggan', 'pemilik']) && $produk->stok > 0)
                            <form action="{{ route('keranjang.tambah', $produk) }}" method="POST" class="flex flex-wrap items-end gap-3">
                                @csrf
                                <div>
                                    <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah</label>
                                    <input type="number" id="jumlah" name="jumlah" value="1" min="1" max="{{ $produk->stok }}"
                                        class="mt-1 w-24 rounded-lg border border-garis bg-surface px-3 py-2.5 text-center text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                                </div>
                                <button type="submit"
                                    class="rounded-lg bg-primary px-6 py-3 font-aksen text-sm font-semibold text-white transition hover:bg-primary-dark">
                                    Tambahkan ke Keranjang
                                </button>
                                <a href="{{ route('katalog.index') }}"
                                    class="rounded-lg border border-garis px-6 py-3 text-center font-aksen text-sm font-semibold text-gray-700 transition hover:bg-surface">
                                    Kembali ke Katalog
                                </a>
                            </form>
                        @elseif ($produk->stok > 0)
                            <p class="text-sm text-gray-500">Anda masuk sebagai staf — halaman belanja hanya untuk pelanggan.</p>
                        @else
                            <p class="text-sm text-bahaya">Stok habis. Silakan hubungi toko untuk ketersediaan.</p>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-block rounded-lg bg-primary px-6 py-3 font-aksen text-sm font-semibold text-white transition hover:bg-primary-dark">
                            Masuk untuk Membeli
                        </a>
                        <p class="mt-2 text-sm text-gray-500">Pembelian online hanya untuk pelanggan terdaftar.</p>
                    @endauth
                </div>
            </div>
        </div>

        {{-- PRODUK SERUPA --}}
        @if ($serupa->isNotEmpty())
            <section class="mt-12">
                <h2 class="font-sans text-2xl font-bold tracking-tight">Produk Serupa</h2>
                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($serupa as $p)
                        <x-kartu-produk :produk="$p" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layout-publik>
