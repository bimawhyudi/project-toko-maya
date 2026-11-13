<x-layout-staf>
    @section('judul', 'Pemilik — Laporan Penjualan')

    <x-banner-staf badge="Portal Eksekutif Pemilik Toko" judul="Laporan Penjualan & Keuangan"
        sub="Rekap omzet mingguan & bulanan per periode, lengkap dengan ekspor Excel/CSV dan PDF.">
        <x-slot:aksi>
            <a href="{{ route('pemilik.index') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Dashboard Utama</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6">
        {{-- FILTER PERIODE --}}
        <div class="kartu flex flex-wrap items-end gap-3 p-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500">Dari Tanggal</label>
                    <input type="date" name="mulai" value="{{ $mulai->format('Y-m-d') }}"
                        class="mt-1 rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500">Sampai Tanggal</label>
                    <input type="date" name="sampai" value="{{ $sampai->format('Y-m-d') }}"
                        class="mt-1 rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                </div>
                <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark">Terapkan</button>
            </form>
            <div class="ms-auto flex gap-2">
                <a href="{{ route('pemilik.laporan.excel', request()->only('mulai', 'sampai')) }}"
                    class="rounded-lg border border-sukses px-4 py-2.5 text-sm font-bold text-sukses-teks transition hover:bg-sukses hover:text-white">
                    ⬇ Excel
                </a>
                <a href="{{ route('pemilik.laporan.pdf', request()->only('mulai', 'sampai')) }}"
                    class="rounded-lg border border-bahaya px-4 py-2.5 text-sm font-bold text-bahaya transition hover:bg-bahaya hover:text-white">
                    ⬇ PDF
                </a>
            </div>
        </div>

        {{-- REKAP --}}
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $omzetTotal = collect($rekap)->filter(fn ($r) => str_starts_with($r, 'Total omzet'))->first();
                $omzetOnline = collect($rekap)->filter(fn ($r) => str_starts_with($r, 'Omzet online'))->first();
                $omzetKasir = collect($rekap)->filter(fn ($r) => str_starts_with($r, 'Omzet kasir'))->first();
                $jumlah = collect($rekap)->filter(fn ($r) => str_starts_with($r, 'Total transaksi'))->first();
            @endphp
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Total Omzet</p>
                <p class="mt-1 font-sans text-xl font-bold">{{ str_replace('Total omzet: ', '', $omzetTotal) }}</p>
            </div>
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Omzet Online</p>
                <p class="mt-1 font-sans text-xl font-bold text-sukses-teks">{{ str_replace('Omzet online: ', '', $omzetOnline) }}</p>
            </div>
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Omzet Kasir POS</p>
                <p class="mt-1 font-sans text-xl font-bold text-primary">{{ str_replace('Omzet kasir: ', '', $omzetKasir) }}</p>
            </div>
            <div class="kartu p-5">
                <p class="text-sm text-gray-500">Jumlah Transaksi</p>
                <p class="mt-1 font-sans text-xl font-bold">{{ str_replace('Total transaksi: ', '', $jumlah) }}</p>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="kartu mt-4 overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-surface text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-start">Kode</th>
                        <th class="px-4 py-3 text-start">Tanggal</th>
                        <th class="px-4 py-3 text-start">Pelanggan</th>
                        <th class="px-4 py-3 text-start">Kanal</th>
                        <th class="px-4 py-3 text-start">Metode</th>
                        <th class="px-4 py-3 text-end">Total</th>
                        <th class="px-4 py-3 text-start">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/70">
                    @forelse ($pesanan as $p)
                        <tr class="hover:bg-primary-mist/50">
                            <td class="px-4 py-3 font-aksen font-bold text-primary">{{ $p->kode_pesanan }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $p->tanggal_pesanan?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $p->user?->nama ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $p->metode_pemenuhan === 'beli_langsung_kasir' ? 'Kasir POS' : ($p->metode_pemenuhan === 'kurir' ? 'Online · Kurir' : 'Online · Ambil Toko') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $p->metode_pembayaran ?? '—' }}</td>
                            <td class="px-4 py-3 text-end font-semibold">Rp {{ number_format($p->total_harga + $p->ongkir, 0, ',', '.') }}</td>
                            <td class="px-4 py-3"><x-badge-status :status="$p->status_pesanan" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Tidak ada penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout-staf>
