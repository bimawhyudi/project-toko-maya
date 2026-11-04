{{-- Ikon keranjang dengan jumlah item untuk pelanggan --}}
@props(['aktif' => false])
@php
    $jumlah = auth()->user()->jumlahItemKeranjang();
@endphp

<a href="{{ route('keranjang.index') }}" aria-label="Keranjang belanja"
    @class([
        'relative grid h-10 w-10 place-items-center rounded-full transition',
        'bg-primary text-white' => $aktif,
        'bg-primary-soft text-primary hover:bg-primary hover:text-white' => ! $aktif,
    ])>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
    </svg>
    @if ($jumlah > 0)
        <span class="absolute -end-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-bahaya px-1 text-[11px] font-bold text-white">
            {{ $jumlah }}
        </span>
    @endif
</a>
