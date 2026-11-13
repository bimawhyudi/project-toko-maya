<x-layout-staf>
    @section('judul', 'Kasir — Riwayat Transaksi')

    <x-banner-staf badge="Mesin Kasir POS Toko Maya" judul="Riwayat & Pemantauan Transaksi"
        sub="Transaksi fisik kasir dan pesanan daring yang sudah lunas beserta statusnya.">
        <x-slot:aksi>
            <a href="{{ route('kasir.index') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Kembali ke POS</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('kasir.riwayat', ['tab' => 'fisik']) }}"
                @class(['rounded-lg px-4 py-2.5 text-sm font-semibold transition', 'bg-primary text-white shadow-kartu' => $tab === 'fisik', 'text-gray-700 hover:bg-primary-mist' => $tab !== 'fisik'])>
                Transaksi Fisik (POS Kasir)
            </a>
            <a href="{{ route('kasir.riwayat', ['tab' => 'online']) }}"
                @class(['rounded-lg px-4 py-2.5 text-sm font-semibold transition', 'bg-primary text-white shadow-kartu' => $tab === 'online', 'text-gray-700 hover:bg-primary-mist' => $tab !== 'online'])>
                Transaksi Online Masuk
            </a>
        </div>

        @if ($tab === 'fisik')
            <div class="kartu mt-4 overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead class="bg-surface text-start text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-start">No. Nota</th>
                            <th class="px-4 py-3 text-start">Tanggal</th>
                            <th class="px-4 py-3 text-start">Item</th>
                            <th class="px-4 py-3 text-start">Metode Bayar</th>
                            <th class="px-4 py-3 text-end">Total</th>
                            <th class="px-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis/70">
                        @forelse ($fisik as $p)
                            <tr class="hover:bg-primary-mist/50">
                                <td class="px-4 py-3 font-aksen font-bold text-primary">{{ $p->kode_pesanan }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->tanggal_pesanan?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $p->detail->sum('jumlah') }} buah / {{ $p->detail->count() }} item</td>
                                <td class="px-4 py-3"><span class="rounded-md bg-primary-soft px-2 py-1 text-xs font-bold uppercase text-primary-darker">{{ $p->metode_pembayaran }}</span></td>
                                <td class="px-4 py-3 text-end font-semibold">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('kasir.struk', $p->kode_pesanan) }}" class="rounded-lg bg-primary-soft px-3 py-1.5 text-xs font-bold text-primary hover:bg-primary hover:text-white">Struk</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada transaksi fisik.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $fisik->links() }}</div>
        @else
            <div class="kartu mt-4 overflow-x-auto">
                <table class="w-full min-w-[820px] text-sm">
                    <thead class="bg-surface text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-start">Kode Pesanan</th>
                            <th class="px-4 py-3 text-start">Tanggal</th>
                            <th class="px-4 py-3 text-start">Pelanggan</th>
                            <th class="px-4 py-3 text-start">Metode</th>
                            <th class="px-4 py-3 text-start">Status</th>
                            <th class="px-4 py-3 text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis/70">
                        @forelse ($online as $p)
                            <tr class="hover:bg-primary-mist/50">
                                <td class="px-4 py-3 font-aksen font-bold text-primary">{{ $p->kode_pesanan }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->tanggal_pesanan?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $p->user?->nama ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $p->metode_pemenuhan === 'kurir' ? 'Kurir' : 'Ambil di toko' }}</td>
                                <td class="px-4 py-3"><x-badge-status :status="$p->status_pesanan" /></td>
                                <td class="px-4 py-3 text-end font-semibold">Rp {{ number_format($p->totalTagihan, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada pesanan daring lunas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $online->links() }}</div>
        @endif
    </div>
</x-layout-staf>
