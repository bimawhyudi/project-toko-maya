{{-- Label status pesanan dengan warna sesuai makna --}}
@props(['status'])

@php
    $gaya = match ($status) {
        'keranjang', 'menunggu_pembayaran' => 'bg-rating/20 text-yellow-800 ring-rating/40',
        'diproses' => 'bg-primary-soft text-primary-darker ring-primary/30',
        'siap_diambil' => 'bg-primary text-white ring-primary',
        'dikirim' => 'bg-navy text-white ring-navy',
        'selesai' => 'bg-sukses/15 text-sukses-teks ring-sukses/30',
        'batal' => 'bg-bahaya/10 text-bahaya ring-bahaya/30',
        default => 'bg-gray-100 text-gray-600 ring-gray-300',
    };
    $teks = [
        'keranjang' => 'Keranjang',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'diproses' => 'Diproses',
        'siap_diambil' => 'Siap Diambil',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ][$status] ?? $status;
@endphp

<span class="inline-flex items-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $gaya }}">
    {{ $teks }}
</span>
