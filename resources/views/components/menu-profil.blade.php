{{-- Menu akun (dropdown) untuk pengguna yang sudah masuk --}}
<div x-data="{ open: false }" class="relative">
    <button @click="open = ! open" class="flex items-center gap-2 rounded-full bg-primary px-4 py-2.5 font-aksen text-sm font-semibold text-white shadow-kartu transition hover:bg-primary-dark">
        <span>{{ mb_substr(auth()->user()->nama, 0, 2) }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak
        class="absolute end-0 z-50 mt-2 w-56 overflow-hidden rounded-kartu border border-garis bg-white shadow-terangkat">
        <div class="border-b border-garis px-4 py-3">
            <p class="truncate text-sm font-semibold">{{ auth()->user()->nama }}</p>
            <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-mist hover:text-primary">Profil Saya</a>
        @if (auth()->user()->role === 'pelanggan')
            <a href="{{ route('alamat.index') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-mist hover:text-primary">Buku Alamat</a>
        @endif
        @if (in_array(auth()->user()->role, ['kasir', 'admin_gudang', 'pemilik']))
            <a href="{{ route('dasbor') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-mist hover:text-primary">Dasbor Staf</a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full px-4 py-2.5 text-start text-sm text-bahaya hover:bg-red-50">Keluar</button>
        </form>
    </div>
</div>
