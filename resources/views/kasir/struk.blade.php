{{-- Struk transaksi fisik: halaman siap cetak (window.print) --}}
<x-layout-staf>
    @section('judul', 'Struk ' . $pesanan->kode_pesanan)

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6">
        <div class="mb-4 flex items-center justify-between print:hidden">
            <a href="{{ route('kasir.index') }}" class="text-sm font-semibold text-primary hover:text-primary-dark">← Kembali ke POS</a>
            <button type="button" onclick="window.print()"
                class="rounded-lg bg-primary px-6 py-2.5 text-sm font-bold text-white transition hover:bg-primary-dark">🖨 Cetak Struk</button>
        </div>

        <div class="mx-auto w-full max-w-xs bg-white p-6 font-mono text-[13px] leading-relaxed text-gray-900 shadow-terangkat" id="struk">
            <div class="text-center">
                <p class="font-sans text-lg font-bold">{{ strtoupper(config('toko.nama_toko')) }}</p>
                <p class="text-[11px]">{{ config('toko.alamat') }}</p>
                <p class="text-[11px]">Telp: {{ config('toko.no_hp') }}</p>
            </div>
            <div class="my-3 border-t border-dashed border-gray-400"></div>
            <div class="flex justify-between text-[12px]"><span>No. Nota</span><span class="font-bold">{{ $pesanan->kode_pesanan }}</span></div>
            <div class="flex justify-between text-[12px]"><span>Tanggal</span><span>{{ $pesanan->tanggal_pesanan?->format('d/m/Y H:i') }}</span></div>
            <div class="flex justify-between text-[12px]"><span>Kasir</span><span>{{ auth()->user()->nama }}</span></div>
            <div class="flex justify-between text-[12px]"><span>Pelanggan</span><span>{{ $pesanan->user?->nama ?? 'Pelanggan Walk-in' }}</span></div>
            <div class="flex justify-between text-[12px]"><span>Metode Bayar</span><span class="uppercase">{{ $pesanan->metode_pembayaran }}</span></div>
            <div class="my-3 border-t border-dashed border-gray-400"></div>

            @foreach ($pesanan->detail as $d)
                <p class="font-semibold">{{ $d->produk->nama_produk }}</p>
                <div class="flex justify-between"><span>{{ $d->jumlah }} x {{ number_format($d->harga, 0, ',', '.') }}</span><span>{{ number_format($d->subtotal, 0, ',', '.') }}</span></div>
            @endforeach

            <div class="my-3 border-t border-dashed border-gray-400"></div>
            <div class="flex justify-between text-base font-bold"><span>TOTAL:</span><span>Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span></div>
            <div class="my-3 border-t border-dashed border-gray-400"></div>
            <p class="text-center text-[11px]">*** TERIMA KASIH ***</p>
            <p class="text-center text-[10px]">Barang yang sudah dibeli dapat ditukar<br>maksimal 3 hari dengan nota ini.</p>
        </div>

        <style>
            @media print {
                body * { visibility: hidden; }
                #struk, #struk * { visibility: visible; }
                #struk { position: fixed; inset: 0; margin: 0; box-shadow: none; }
            }
        </style>
    </div>
</x-layout-staf>
