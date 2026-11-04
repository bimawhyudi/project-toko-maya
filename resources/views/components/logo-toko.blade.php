{{-- Logo & nama toko, dipakai di navbar dan halaman login --}}
<a href="{{ route('beranda') }}" class="inline-flex items-center gap-2">
    <span class="grid h-10 w-10 place-items-center rounded-lg bg-primary text-white shadow-kartu">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
        </svg>
    </span>
    <span class="leading-tight">
        <span class="block font-aksen text-xl font-bold tracking-wide text-primary">TOKO MAYA</span>
        <span class="block text-[12px] font-medium text-gray-500">{{ config('toko.tagline') }}</span>
    </span>
</a>
