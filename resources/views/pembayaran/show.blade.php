<x-layout-publik>
    @section('judul', 'Pembayaran ' . $pesanan->kode_pesanan)

    @php
        $namaBank = [
            'bca' => 'BCA Virtual Account',
            'bri' => 'BRI Virtual Account',
            'bni' => 'BNI Virtual Account',
            'permata' => 'Permata Virtual Account',
        ][$pembayaran['bank'] ?? ''] ?? 'Virtual Account';
    @endphp

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
                    <a href="{{ url()->current() }}" class="mt-3 inline-block rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white">Coba Lagi</a>
                </div>
            @elseif (empty($pembayaran))
                <div class="mt-6 rounded-kartu border border-garis bg-surface p-4 text-sm text-gray-500">
                    Menyiapkan pembayaran…
                </div>
            @else
                @if (! empty($pembayaran['va_number']))
                    {{-- ============ VIRTUAL ACCOUNT ============ --}}
                    <div class="mt-6 rounded-kartu border border-garis bg-surface p-5 text-start">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">{{ $namaBank }}</p>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Menunggu Pembayaran</span>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 rounded-lg border border-dashed border-primary/50 bg-white p-3">
                            <p id="nomorVa" class="font-aksen text-2xl font-bold tracking-wider text-primary sm:text-3xl">{{ $pembayaran['va_number'] }}</p>
                            <button id="tombolSalinVa" type="button"
                                class="shrink-0 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:bg-primary-dark">
                                Salin
                            </button>
                        </div>

                        <p class="mt-2 text-xs text-gray-400">Nomor ini khusus untuk pesanan <b>{{ $pesanan->kode_pesanan }}</b> dan berlaku sampai batas waktu di atas.</p>

                        <div class="mt-4 border-t border-garis pt-4">
                            <p class="text-sm font-bold text-gray-700">Cara membayar:</p>
                            <ol class="mt-1 list-decimal space-y-1 ps-5 text-xs leading-5 text-gray-500">
                                <li>Pilih menu <b>Transfer / Virtual Account</b> di ATM, m-banking, atau i-banking.</li>
                                <li>Masukkan nomor Virtual Account di atas sebagai tujuan transfer.</li>
                                <li>Nominal akan terisi otomatis <b>Rp {{ number_format($pesanan->totalTagihan, 0, ',', '.') }}</b> — jangan ubah nominalnya.</li>
                                <li>Halaman ini otomatis memperbarui status setelah pembayaran diterima.</li>
                            </ol>
                        </div>
                    </div>
                @elseif (! empty($pembayaran['qr_url']) || ! empty($pembayaran['qr_string']))
                    {{-- ============ QRIS ============ --}}
                    <div class="mt-6 rounded-kartu border border-garis bg-surface p-5">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-500">QRIS — Semua e-wallet & m-banking</p>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">Menunggu Pembayaran</span>
                        </div>

                        <div class="mx-auto mt-3 w-fit rounded-kartu border border-garis bg-white p-3 shadow-kartu">
                            @if (! empty($pembayaran['qr_url']))
                                <img src="{{ $pembayaran['qr_url'] }}" alt="Kode QRIS pesanan {{ $pesanan->kode_pesanan }}" class="h-56 w-56">
                            @else
                                <p class="p-4 text-xs text-gray-500">Kode QR tidak tersedia, silakan gunakan nomor Virtual Account.</p>
                            @endif
                        </div>

                        <p class="mt-3 text-center text-xs text-gray-400">Bisa dibayar dari GoPay, DANA, OVO, ShopeePay, dan m-banking apa pun yang mendukung QRIS.</p>

                        <div class="mt-4 border-t border-garis pt-4 text-start">
                            <p class="text-sm font-bold text-gray-700">Cara membayar:</p>
                            <ol class="mt-1 list-decimal space-y-1 ps-5 text-xs leading-5 text-gray-500">
                                <li>Buka aplikasi pembayaran (GoPay / DANA / m-banking) dan pilih menu <b>Scan QRIS</b>.</li>
                                <li>Arahkan kamera ke kode QR di atas.</li>
                                <li>Periksa nominal <b>Rp {{ number_format($pesanan->totalTagihan, 0, ',', '.') }}</b> lalu konfirmasi pembayaran.</li>
                            </ol>
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-kartu border border-garis bg-surface p-4 text-sm text-gray-500">
                        Pembayaran <b>{{ $pesanan->metode_pembayaran }}</b> sedang diproses ({{ $pembayaran['payment_type'] ?? '-' }}). Status otomatis diperbarui.
                    </div>
                @endif

                <p class="mt-4 text-xs text-gray-400">Halaman ini akan otomatis diperbarui setelah pembayaran terverifikasi.</p>
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

    @if (! empty($pembayaran['va_number']))
        <script>
            document.getElementById('tombolSalinVa').addEventListener('click', async () => {
                const nomor = document.getElementById('nomorVa').textContent.trim();
                const tombol = document.getElementById('tombolSalinVa');

                try {
                    await navigator.clipboard.writeText(nomor);
                } catch (_) {
                    const bantu = document.createElement('textarea');
                    bantu.value = nomor;
                    document.body.appendChild(bantu);
                    bantu.select();
                    document.execCommand('copy');
                    bantu.remove();
                }

                tombol.textContent = 'Tersalin ✓';
                tombol.classList.add('bg-sukses-teks');
                setTimeout(() => {
                    tombol.textContent = 'Salin';
                    tombol.classList.remove('bg-sukses-teks');
                }, 2000);
            });
        </script>
    @endif
    @endpush
</x-layout-publik>