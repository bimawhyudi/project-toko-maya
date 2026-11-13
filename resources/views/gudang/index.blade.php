<x-layout-staf>
    @section('judul', 'Gudang — Ringkasan')

    <x-banner-staf badge="Portal Staf Logistik & Gudang" judul="Dashboard Admin Gudang Toko Maya"
        sub="Pengelolaan stok fisik, antrean pengepakan barang, input nomor resi, dan pelacakan kurir logistik.">
        <x-slot:aksi>
            <a href="{{ route('gudang.pesanan') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Lihat Pesanan</a>
            <a href="{{ route('gudang.stok') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Stok Barang</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6">
        {{-- KARTU STATISTIK --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="kartu p-4">
                <p class="text-sm text-gray-500">Pesanan Baru</p>
                <p class="mt-1 font-sans text-4xl font-bold">{{ $pesananBaru }}</p>
                <a href="{{ route('gudang.pesanan', ['status' => 'diproses']) }}" class="mt-1 inline-block text-xs font-semibold text-primary hover:text-primary-dark">Perlu dikemas →</a>
            </div>
            <div class="kartu p-4">
                <p class="text-sm text-gray-500">Siap Kirim / Dikemas</p>
                <p class="mt-1 font-sans text-4xl font-bold">{{ $siapKirim }}</p>
                <span class="mt-1 inline-block text-xs text-gray-400">menunggu diambil pelanggan</span>
            </div>
            <div class="kartu p-4">
                <p class="text-sm text-gray-500">Sudah Diproses</p>
                <p class="mt-1 font-sans text-4xl font-bold">{{ $sudahDiproses }}</p>
                <span class="mt-1 inline-block text-xs text-gray-400">siap diambil + dikirim</span>
            </div>
            <div class="kartu p-4 {{ $stokMenipis > 0 ? 'border-rating' : '' }}">
                <p class="text-sm text-gray-500">Stok Menipis</p>
                <p class="mt-1 font-sans text-4xl font-bold {{ $stokMenipis > 0 ? 'text-yellow-600' : '' }}">{{ $stokMenipis }}</p>
                <a href="{{ route('gudang.stok', ['filter' => 'menipis']) }}" class="mt-1 inline-block text-xs font-semibold text-primary hover:text-primary-dark">Perlu restock →</a>
            </div>
            <div class="kartu p-4 {{ $stokHabis > 0 ? 'border-bahaya' : '' }}">
                <p class="text-sm text-gray-500">Stok Habis</p>
                <p class="mt-1 font-sans text-4xl font-bold {{ $stokHabis > 0 ? 'text-bahaya' : '' }}">{{ $stokHabis }}</p>
                <a href="{{ route('gudang.stok', ['filter' => 'habis']) }}" class="mt-1 inline-block text-xs font-semibold text-primary hover:text-primary-dark">Lihat daftar →</a>
            </div>
        </div>

        {{-- ANTREAN --}}
        <div class="kartu mt-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-garis px-5 py-4">
                <h2 class="font-aksen font-bold">Antrean Pesanan Masuk yang Perlu Tindakan Cepat</h2>
                <a href="{{ route('gudang.pesanan') }}" class="text-sm font-semibold text-primary hover:text-primary-dark">Buka Semua Pesanan →</a>
            </div>
            <div class="divide-y divide-garis/70">
                @forelse ($antrean as $p)
                    <div class="flex flex-wrap items-center gap-3 px-5 py-3.5">
                        <span class="font-aksen font-bold text-primary">{{ $p->kode_pesanan }}</span>
                        <span class="text-sm text-gray-600">{{ $p->user?->nama ?? 'Tanpa akun' }}</span>
                        <span class="text-sm text-gray-400">{{ $p->detail->sum('jumlah') }} item (Rp {{ number_format($p->totalTagihan, 0, ',', '.') }})</span>
                        <span class="ms-auto">
                            @if ($p->metode_pemenuhan === 'ambil_di_toko')
                                <form action="{{ route('gudang.siap', $p) }}" method="POST"
                                    x-data @submit.prevent="if (confirm('Tandai ' + '{{ $p->kode_pesanan }}' + ' SIAP DIAMBIL?')) $el.submit()">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:bg-primary-dark">Tandai Siap Diambil</button>
                                </form>
                            @else
                                <a href="{{ route('gudang.pesanan', ['status' => 'diproses']) }}" class="rounded-lg bg-primary-soft px-4 py-2 text-xs font-bold text-primary transition hover:bg-primary hover:text-white">Input Resi →</a>
                            @endif
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-10 text-center text-sm text-gray-400">Tidak ada pesanan menunggu dikemas. Kerja bagus! 🎉</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layout-staf>