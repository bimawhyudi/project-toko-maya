{{-- Tab navigasi dasbor staf; $aktif menandai tab terpilih --}}
@props(['href', 'aktif' => false])

<a href="{{ $href }}"
    @class([
        'rounded-lg px-4 py-2.5 text-sm font-semibold transition',
        'bg-primary text-white shadow-kartu' => $aktif,
        'text-gray-700 hover:bg-primary-soft hover:text-primary' => ! $aktif,
    ])>
    {{ $slot }}
</a>
