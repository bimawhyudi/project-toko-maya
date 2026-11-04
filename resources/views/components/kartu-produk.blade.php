{{-- Kartu produk untuk katalog/beranda --}}
@props(['produk'])

@php
    $terjual = \App\Models\DetailPesanan::where('produk_id', $produk->id)
        ->whereHas('pesanan', fn ($q) => $q->whereIn('status_pesanan', ['diproses', 'siap_diambil', 'dikirim', 'selesai']))
        ->sum('jumlah');
@endphp

<div class="group flex flex-col overflow-hidden rounded-kartu border border-garis bg-white shadow-kartu transition hover:-translate-y-0.5 hover:shadow-terangkat">
    <a href="{{ route('produk.show', $produk) }}" class="block">
        <div class="aspect-square w-full overflow-hidden bg-primary-mist">
            @if ($produk->gambar)
                <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama_produk }}"
                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
            @else
                <div class="grid h-full w-full place-items-center text-primary/40">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-14 w-14">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </div>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col gap-2 p-4">
        <p class="text-[11px] font-medium uppercase tracking-wide text-gray-400">{{ $produk->kategori->nama_kategori }}</p>
        <a href="{{ route('produk.show', $produk) }}"
            class="line-clamp-2 text-sm font-semibold leading-snug text-gray-900 hover:text-primary">
            {{ $produk->nama_produk }}
        </a>

        <div class="mt-auto">
            <p class="font-aksen text-xl font-bold text-primary">
                Rp {{ number_format($produk->harga, 0, ',', '.') }}
            </p>
            <div class="mt-2 flex items-center justify-between gap-2">
                @if ($produk->stok <= 0)
                    <span class="rounded-md bg-bahaya/10 px-2 py-1 text-[11px] font-semibold text-bahaya">Stok Habis</span>
                @elseif ($produk->stokMenipis)
                    <span class="rounded-md bg-rating/20 px-2 py-1 text-[11px] font-semibold text-yellow-800">Stok Menipis</span>
                @else
                    <span class="rounded-md bg-sukses/10 px-2 py-1 text-[11px] font-semibold text-sukses-teks">Stok {{ $produk->stok }}</span>
                @endif
                <span class="text-[11px] text-gray-400">{{ number_format($terjual) }} terjual</span>
            </div>
        </div>

        <a href="{{ route('produk.show', $produk) }}"
            class="block w-full rounded-lg bg-primary-soft px-4 py-2.5 text-center text-sm font-semibold text-primary transition hover:bg-primary hover:text-white">
            Lihat Detail
        </a>

        @auth
            @if (in_array(auth()->user()->role, ['pelanggan', 'pemilik']))
                <form action="{{ route('keranjang.tambah', $produk) }}" method="POST">
                    @csrf
                    <button type="submit" @disabled($produk->stok <= 0)
                        class="w-full rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400">
                        + Keranjang
                    </button>
                </form>
            @endif
        @else
            <a href="{{ route('login') }}"
                class="block w-full rounded-lg border border-primary bg-white px-4 py-2.5 text-center text-sm font-semibold text-primary transition hover:bg-primary hover:text-white">
                Masuk untuk Membeli
            </a>
        @endauth
    </div>
</div>
