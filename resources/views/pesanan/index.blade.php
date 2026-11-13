<x-layout-publik aktif="pesanan">
    @section('judul', 'Pesanan Saya')

    <div class="mx-auto max-w-halaman px-4 py-8 sm:px-6">
        <h1 class="judul-halaman">Pesanan & Status Pengiriman Saya</h1>
        <p class="mt-1 text-sm text-gray-500">Pantau status pesanan, nomor resi kurir, dan kode pengambilan di toko.</p>

        {{-- TAB STATUS --}}
        <div class="mt-6 flex flex-wrap gap-2">
            <a href="{{ route('pesanan.index') }}"
                @class(['rounded-lg px-4 py-2.5 text-sm font-semibold transition', 'bg-primary text-white shadow-kartu' => $status === '', 'text-gray-700 hover:bg-primary-mist hover:text-primary' => $status !== ''])>
                Semua
            </a>
            @foreach (['menunggu_pembayaran' => 'Menunggu Pembayaran', 'diproses' => 'Diproses', 'siap_diambil' => 'Siap Diambil', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $s => $label)
                <a href="{{ route('pesanan.index', ['status' => $s]) }}"
                    @class(['rounded-lg px-4 py-2.5 text-sm font-semibold transition', 'bg-primary text-white shadow-kartu' => $status === $s, 'text-gray-700 hover:bg-primary-mist hover:text-primary' => $status !== $s])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($pesanan as $p)
                <div class="kartu p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-garis pb-3">
                        <div>
                            <a href="{{ route('pesanan.show', $p->kode_pesanan) }}" class="font-aksen font-bold text-primary hover:text-primary-dark">{{ $p->kode_pesanan }}</a>
                            <p class="text-xs text-gray-400">{{ $p->tanggal_pesanan?->format('d M Y H:i') }}</p>
                        </div>
                        <x-badge-status :status="$p->status_pesanan" />
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-4">
                        <div class="flex -space-x-3">
                            @foreach ($p->detail->take(3) as $d)
                                <div class="grid h-12 w-12 place-items-center rounded-lg border border-garis bg-primary-mist text-xl" title="{{ $d->produk->nama_produk }}">🔧</div>
                            @endforeach
                            @if ($p->detail->count() > 3)
                                <div class="grid h-12 w-12 place-items-center rounded-lg border border-garis bg-surface text-xs font-bold text-gray-500">+{{ $p->detail->count() - 3 }}</div>
                            @endif
                        </div>
                        <div class="min-w-40 flex-1 text-sm">
                            <p class="font-semibold">{{ $p->detail->count() }} produk · {{ $p->detail->sum('jumlah') }} buah</p>
                            <p class="text-gray-500">
                                {{ $p->metode_pemenuhan === 'kurir' ? 'Kirim via ' . ($p->ekspedisi ?? 'kurir') . ($p->no_resi ? ' · Resi: ' . $p->no_resi : '') : ($p->metode_pemenuhan === 'ambil_di_toko' ? 'Ambil di toko' . ($p->kode_pesanan ? ' · Kode: ' . $p->kode_pesanan : '') : 'Beli di kasir') }}
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="text-xs text-gray-400">Total tagihan</p>
                            <p class="font-aksen font-bold text-primary">Rp {{ number_format($p->totalTagihan, 0, ',', '.') }}</p>
                        </div>
                        <a href="{{ route('pesanan.show', $p->kode_pesanan) }}"
                            class="rounded-lg bg-primary-soft px-4 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary hover:text-white">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="kartu grid place-items-center gap-2 py-16 text-center">
                    <span class="text-4xl">📦</span>
                    <p class="font-semibold text-gray-700">Belum ada pesanan{{ $status ? ' dengan status ini' : '' }}</p>
                    <a href="{{ route('katalog.index') }}" class="mt-1 text-sm font-semibold text-primary hover:text-primary-dark">Mulai belanja →</a>
                </div>
            @endforelse
        </div>

        <div class="mt-8">{{ $pesanan->links() }}</div>
    </div>
</x-layout-publik>
