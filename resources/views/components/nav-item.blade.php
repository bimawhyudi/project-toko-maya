{{-- Item menu navbar; $aktif menandai menu yang sedang dibuka --}}
@props(['href', 'aktif' => false])

<a href="{{ $href }}"
    @class([
        'rounded-full px-3 py-2 text-sm font-semibold transition sm:px-4',
        'bg-primary text-white shadow-kartu' => $aktif,
        'text-gray-700 hover:bg-primary-soft hover:text-primary' => ! $aktif,
    ])>
    {{ $slot }}
</a>
