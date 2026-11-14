<x-layout-publik>
    @section('judul', 'Pembayaran ' . $pesanan->kode_pesanan)

    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6">
        <a href="{{ route('pesanan.show', $pesanan->kode_pesanan) }}" class="text-sm font-semibold text-primary hover:text-primary-dark">← Kembali ke Detail Pesanan</a>

        <div class="mt-4 kartu p-6 text-center">
            <p class="text-sm text-gray-500">Selesaikan pembayaran untuk pesanan</p>
            <p class="font-aksen text-2xl font-bold text-primary">{{ $pesanan->kode_pesanan }}</p>

            <p class="mt-4 text-sm text-gray-500">Total tagihan</p>
            <p class="font-aksen text-4xl font-bold">Rp {{ number_format($pesanan->totalTagihan, 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-bahaya">Batas bayar: {{ $pesanan->expired_at?->format('d M Y, H:i') }} WIB</p>

            @if ($galat)
                <div class="mt-6 rounded-kartu border border-bahaya/30 bg-bahaya/5 p-4 text-start text-sm text-gray-700">
                    <p class="font-semibold text-bahaya">⚠️ Pembayaran Midtrans belum siap</p>
                    <p class="mt-1">{{ $galat }}</p>
                    @if (str_contains($galat, '401'))
                        <p class="mt-2">Kunci Midtrans ditolak server Midtrans (401). Di <b>dashboard sandbox baru</b> (dashboard.sandbox.midtrans.com), kunci API bukan "Signature Secret Key" — caranya:</p>
                        <ol class="mt-1 list-decimal space-y-0.5 ps-5 text-xs text-gray-500">
                            <li>Di halaman <b>Settings → Access Keys</b>, scroll ke bagian <b>Server Keys</b>.</li>
                            <li>Klik <b>Generate server key</b>, lalu salin kunci yang terbentuk.</li>
                            <li>Tempel ke <code>MIDTRANS_SERVER_KEY</code> di <code>.env</code> ("Signature Secret Key" boleh dipisah ke <code>MIDTRANS_SIGNATURE_KEY</code>).</li>
                            <li>Jalankan <code>php artisan config:clear</code> lalu <code>php artisan midtrans:cek</code> untuk memastikan.</li>
                        </ol>
                    @endif
                </div>
            @endif

            @if ($token)
                <button id="tombolBayar"
                    class="mt-6 w-full rounded-lg bg-primary py-4 font-aksen text-base font-bold text-white shadow-terangkat transition hover:bg-primary-dark">
                    Bayar Sekarang (QRIS / Virtual Account)
                </button>
                <p class="mt-3 text-xs text-gray-400">Halaman pembayaran aman dibuka oleh Midtrans. Halaman ini akan otomatis diperbarui setelah pembayaran terverifikasi.</p>
            @endif
        </div>
    </div>

    @push('skrip')
    <script>
        const urlStatusPesanan = '{{ route('pesanan.status', $pesanan->kode_pesanan, false) }}';
        const urlDetailPesanan = '{{ route('pesanan.show', $pesanan->kode_pesanan, false) }}';
        let sedangCekStatus = false;

        async function cekStatusPembayaran() {
            if (sedangCekStatus) return false;
            sedangCekStatus = true;

            try {
                const res = await fetch(urlStatusPesanan, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (! res.ok) return false;

                const data = await res.json();
                if (data.status && data.status !== 'menunggu_pembayaran') {
                    clearInterval(timerStatusPembayaran);
                    window.location.href = urlDetailPesanan;
                    return true;
                }
            } catch (_) {
            } finally {
                sedangCekStatus = false;
            }

            return false;
        }

        const timerStatusPembayaran = setInterval(cekStatusPembayaran, 2500);
        document.addEventListener('visibilitychange', () => {
            if (! document.hidden) cekStatusPembayaran();
        });
    </script>

    @if ($token)
        <script src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
        <script>
            document.getElementById('tombolBayar').addEventListener('click', () => {
                snap.pay('{{ $token }}', {
                    onSuccess: () => window.location.href = urlDetailPesanan,
                    onPending: () => window.location.href = urlDetailPesanan,
                    onError: () => window.location.href = urlDetailPesanan,
                    onClose: async () => {
                        const sudahBerubah = await cekStatusPembayaran();
                        if (! sudahBerubah) {
                            alert('Anda menutup halaman pembayaran. Pesanan masih menunggu pembayaran sampai ' + '{{ $pesanan->expired_at?->format('H:i') }}' + '.');
                        }
                    },
                });
            });
        </script>
    @endif
    @endpush
</x-layout-publik>