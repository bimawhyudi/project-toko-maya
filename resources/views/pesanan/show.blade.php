<x-layout-publik>
    @section('judul', 'Detail Pesanan ' . $pesanan->kode_pesanan)

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
        <a href="{{ route('pesanan.index') }}" class="text-sm font-semibold text-primary hover:text-primary-dark">← Kembali ke Daftar Pesanan</a>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-aksen text-3xl font-bold text-primary">{{ $pesanan->kode_pesanan }}</h1>
                <p class="mt-1 text-sm text-gray-500">Dibuat {{ $pesanan->tanggal_pesanan?->format('d M Y, H:i') }} WIB</p>
            </div>
            <x-badge-status :status="$pesanan->status_pesanan" />
        </div>

        {{-- KODE AMBIL / STATUS --}}
        @if ($pesanan->metode_pemenuhan === 'ambil_di_toko')
            <div class="mt-6 kartu overflow-hidden">
                <div class="bg-navy p-6 text-center text-white">
                    <p class="text-sm text-primary-soft">Kode Pengambilan untuk Kasir Toko</p>
                    <p class="mt-2 font-aksen text-4xl font-bold tracking-[0.2em]">{{ $pesanan->kode_pesanan }}</p>
                    <p class="mt-3 text-sm text-primary-soft/90">*Tunjukkan kode ini ke Kasir Toko Maya saat datang mengambil barang. Pesanan ambil di toko tidak dikenakan ongkir.</p>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Langkah 1 — Gudang Mengemas</p>
                        <p class="mt-1 text-sm text-gray-600">Staf gudang menyiapkan barang perkakas pesanan Anda.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Langkah 2 — Datang ke Kasir</p>
                        <p class="mt-1 text-sm text-gray-600">Kunjungi toko fisik Toko Maya dan sebutkan kode pengambilan.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-400">Langkah 3 — Kasir Menyerahkan</p>
                        <p class="mt-1 text-sm text-gray-600">Kasir mencocokkan kode, menyerahkan barang, dan menandai selesai.</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- PELACAKAN --}}
        @if ($pesanan->metode_pemenuhan === 'kurir')
            <div class="mt-6 kartu p-6">
                <h2 class="font-aksen text-lg font-bold">Pengiriman & Pelacakan</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-surface p-4">
                        <p class="text-xs font-semibold uppercase text-gray-400">Kurir / Ekspedisi</p>
                        <p class="mt-1 font-semibold">{{ $pesanan->ekspedisi ?? '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-surface p-4">
                        <p class="text-xs font-semibold uppercase text-gray-400">Nomor Resi</p>
                        <p class="mt-1 font-aksen font-bold tracking-wide">{{ $pesanan->no_resi ?? 'Belum tersedia' }}</p>
                    </div>
                </div>
                @if ($pesanan->alamat_pengiriman)
                    <div class="mt-4 rounded-lg bg-surface p-4">
                        <p class="text-xs font-semibold uppercase text-gray-400">Alamat Tujuan</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $pesanan->alamat_pengiriman }}</p>
                    </div>
                @endif
                @if ($pesanan->status_pesanan === 'dikirim')
                    <form action="{{ route('pesanan.diterima', $pesanan->kode_pesanan) }}" method="POST" class="mt-4"
                        x-data @submit.prevent="if (confirm('Konfirmasi pesanan sudah diterima dengan lengkap?')) $el.submit()">
                        @csrf
                        <button type="submit"
                            class="w-full rounded-lg bg-sukses px-6 py-3.5 font-aksen text-sm font-bold text-white transition hover:bg-emerald-600 sm:w-auto">
                            ✔ Pesanan Diterima
                        </button>
                    </form>
                @endif
            </div>
        @endif

        {{-- STATUS PEMBAYARAN --}}
        <div class="mt-6 kartu p-6">
            <h2 class="font-aksen text-lg font-bold">Status Pembayaran</h2>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-surface p-4">
                <div>
                    <p class="text-sm text-gray-500">Metode: <span class="font-semibold text-gray-800">{{ $pesanan->metode_pembayaran ? ['midtrans_qris' => 'QRIS Online', 'midtrans_va_bca' => 'BCA Virtual Account', 'midtrans_va_bni' => 'BNI Virtual Account', 'midtrans_va_bri' => 'BRI Virtual Account'][$pesanan->metode_pembayaran] ?? $pesanan->metode_pembayaran : '—' }}</span></p>
                    @if ($pesanan->status_pesanan === 'menunggu_pembayaran')
                        <p class="mt-1 text-sm text-bahaya">Selesaikan pembayaran sebelum <strong>{{ $pesanan->expired_at?->format('d M Y, H:i') }}</strong> atau pesanan otomatis dibatalkan.</p>
                    @endif
                </div>
                @if ($pesanan->status_pesanan === 'menunggu_pembayaran' && Route::has('pembayaran.show'))
                    <a href="{{ route('pembayaran.show', $pesanan->kode_pesanan) }}"
                        class="rounded-lg bg-primary px-6 py-3 font-aksen text-sm font-bold text-white transition hover:bg-primary-dark">
                        Bayar Sekarang
                    </a>
                @endif
            </div>
        </div>

        {{-- DAFTAR BARANG --}}
        <div class="mt-6 kartu p-6">
            <h2 class="font-aksen text-lg font-bold">Daftar Barang</h2>
            <div class="mt-4 divide-y divide-garis/70">
                @foreach ($pesanan->detail as $d)
                    <div class="flex items-center gap-4 py-3">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-primary-mist text-xl">🔧</div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold">{{ $d->produk->nama_produk }}</p>
                            <p class="text-xs text-gray-500">{{ $d->jumlah }} × Rp {{ number_format($d->harga, 0, ',', '.') }}</p>
                        </div>
                        <p class="font-aksen font-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <dl class="mt-4 space-y-2 border-t border-garis pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="font-semibold">Rp {{ number_format($pesanan->subtotal, 0, ',', '.') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Ongkir</dt><dd class="font-semibold">{{ $pesanan->ongkir > 0 ? 'Rp ' . number_format($pesanan->ongkir, 0, ',', '.') : 'Gratis' }}</dd></div>
                <div class="flex justify-between text-base"><dt class="font-bold">Total Tagihan</dt><dd class="font-aksen font-bold text-primary">Rp {{ number_format($pesanan->totalTagihan, 0, ',', '.') }}</dd></div>
            </dl>
        </div>
    </div>
</x-layout-publik>
