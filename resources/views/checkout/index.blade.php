<x-layout-publik>
    @section('judul', 'Checkout')

    <div class="mx-auto max-w-halaman px-4 py-8 sm:px-6" x-data="checkout">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Checkout & Pengambilan / Pengiriman Pesanan</h1>
                <p class="mt-1 text-sm text-gray-500">Pilih metode ambil sendiri di Toko Maya atau kirim ekspedisi kurir, lalu bayar online.</p>
            </div>
            <a href="{{ route('keranjang.index') }}" class="text-sm font-semibold text-primary hover:text-primary-dark">← Kembali ke Keranjang</a>
        </div>

        <form method="POST" action="{{ route('checkout.proses') }}" @submit="$el.querySelector('button[type=submit]').disabled = true" class="mt-6 space-y-6">
            @csrf

            {{-- RINGKASAN PESANAN --}}
            <section class="kartu p-5 sm:p-6">
                <h2 class="font-aksen text-lg font-bold">Ringkasan Pesanan ({{ $keranjang->detail->count() }} Produk)</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($keranjang->detail as $d)
                        <div class="flex items-center gap-4">
                            <div class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden rounded-lg bg-primary-mist">
                                @if ($d->produk->gambar)
                                    <img src="{{ asset('storage/' . $d->produk->gambar) }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <span class="text-xl">🔧</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold leading-snug">{{ $d->produk->nama_produk }}</p>
                                <p class="text-xs text-gray-500">{{ $d->jumlah }} buah × Rp {{ number_format($d->harga, 0, ',', '.') }}</p>
                            </div>
                            <p class="font-aksen font-bold">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex justify-between rounded-lg bg-primary-mist px-4 py-3">
                    <span class="font-semibold">Subtotal</span>
                    <span class="font-aksen font-bold">Rp {{ number_format($keranjang->total_harga, 0, ',', '.') }}</span>
                </div>
            </section>

            {{-- METODE PEMENUHAN --}}
            <section class="kartu p-5 sm:p-6">
                <h2 class="font-aksen text-lg font-bold">Metode Pengambilan / Pengiriman Barang</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label @click="pilih('ambil_di_toko')"
                        :class="metode === 'ambil_di_toko' ? 'border-primary bg-primary text-white ring-2 ring-primary' : 'border-garis bg-white hover:border-primary' "
                        class="cursor-pointer rounded-kartu border-2 p-5 transition">
                        <div class="flex items-center justify-between">
                            <span class="grid h-10 w-10 place-items-center rounded-lg bg-white/20">🏪</span>
                            <span :class="metode === 'ambil_di_toko' ? 'bg-white text-primary-dark' : 'bg-primary-soft text-primary-darker'"
                                class="rounded-full px-3 py-1 text-[11px] font-bold">GRATIS ONGKIR</span>
                        </div>
                        <p class="mt-3 font-aksen font-bold">Ambil Sendiri di Toko</p>
                        <p class="mt-1 text-sm" :class="metode === 'ambil_di_toko' ? 'text-white/80' : 'text-gray-500'">
                            Jemput langsung di toko fisik Toko Maya. Gudang akan mengemas, lalu tunjukkan kode pesanan ke kasir.
                        </p>
                        <div class="mt-3 flex justify-between text-sm font-semibold" :class="metode === 'ambil_di_toko' ? 'text-white' : ''">
                            <span>Biaya:</span><span>Rp 0</span>
                        </div>
                        <input type="radio" name="metode_pemenuhan" value="ambil_di_toko" class="hidden" x-model="metode">
                    </label>

                    <label @click="pilih('kurir')"
                        :class="metode === 'kurir' ? 'border-primary bg-primary text-white ring-2 ring-primary' : 'border-garis bg-white hover:border-primary' "
                        class="cursor-pointer rounded-kartu border-2 p-5 transition">
                        <div class="flex items-center justify-between">
                            <span class="grid h-10 w-10 place-items-center rounded-lg bg-white/20">🚚</span>
                            <span :class="metode === 'kurir' ? 'bg-white text-primary-dark' : 'bg-primary-soft text-primary-darker'"
                                class="rounded-full px-3 py-1 text-[11px] font-bold">EKSPEDISI KURIR</span>
                        </div>
                        <p class="mt-3 font-aksen font-bold">Kirim via Kurir Ekspedisi</p>
                        <p class="mt-1 text-sm" :class="metode === 'kurir' ? 'text-white/80' : 'text-gray-500'">
                            Dikirim ke alamat Anda / luar kota via JNE, J&T, atau SiCepat dengan nomor resi pelacakan.
                        </p>
                        <div class="mt-3 flex justify-between text-sm font-semibold" :class="metode === 'kurir' ? 'text-white' : ''">
                            <span>Mulai dari:</span><span>Rp 8.000</span>
                        </div>
                        <input type="radio" name="metode_pemenuhan" value="kurir" class="hidden" x-model="metode">
                    </label>
                </div>

                {{-- ALAMAT (hanya kurir) --}}
                <div x-show="metode === 'kurir'" x-cloak class="mt-5">
                    <h3 class="font-semibold">Alamat Lengkap Tujuan Pengiriman (Rumah / Proyek / Bengkel)</h3>
                    @if ($daftarAlamat)
                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            @foreach ($daftarAlamat as $a)
                                <label @click="muatOngkir('{{ $a['id'] }}')"
                                    :class="alamatId === '{{ $a['id'] }}' ? 'border-primary bg-primary-mist ring-2 ring-primary' : 'border-garis hover:border-primary'"
                                    class="cursor-pointer rounded-kartu border-2 p-4 transition">
                                    <div class="flex items-center justify-between">
                                        <span class="rounded-full bg-primary-soft px-2.5 py-0.5 text-[11px] font-bold text-primary-darker">{{ $a['label'] }}</span>
                                        @if ($a['is_default'])
                                            <span class="text-[11px] font-bold text-sukses-teks">UTAMA</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm font-semibold">{{ $a['nama_penerima'] }} · {{ $a['no_hp'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-600">{{ $a['alamat_lengkap'] }}, {{ $a['kota'] }}, {{ $a['provinsi'] }} {{ $a['kode_pos'] }}</p>
                                    <input type="radio" name="alamat_id" value="{{ $a['id'] }}" class="hidden" x-model="alamatId" @change="muatOngkir('{{ $a['id'] }}')">
                                </label>
                            @endforeach
                        </div>
                        <a href="{{ route('alamat.index') }}" class="mt-3 inline-block text-sm font-semibold text-primary hover:text-primary-dark">+ Kelola alamat lainnya</a>

                        {{-- EKSPEDISI --}}
                        <div class="mt-4">
                            <h3 class="font-semibold">Pilih Layanan Ekspedisi</h3>
                            <div x-show="memuatOngkir" class="mt-2 text-sm text-gray-500">Menghitung ongkir…</div>
                            <div x-show="!memuatOngkir && pilihanOngkir.length === 0" class="mt-2 text-sm text-gray-500">
                                Pilih alamat terlebih dahulu untuk melihat tarif ongkir.
                            </div>
                            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                <template x-for="o in pilihanOngkir" :key="o.kode">
                                    <label :class="ekspedisi === o.kode ? 'border-primary bg-primary-mist ring-2 ring-primary' : 'border-garis hover:border-primary'"
                                        class="cursor-pointer rounded-kartu border-2 p-3 transition">
                                        <input type="radio" name="ekspedisi" :value="o.kode" class="hidden" x-model="ekspedisi" @change="ekspedisi = o.kode">
                                        <p class="text-sm font-bold" x-text="o.kurir + ' ' + o.layanan"></p>
                                        <p class="mt-0.5 font-aksen text-sm font-bold text-primary" x-text="'Rp ' + formatAngka(o.biaya)"></p>
                                        <p class="text-[11px] text-gray-500" x-text="'Estimasi ' + o.estimasi"></p>
                                    </label>
                                </template>
                            </div>
                        </div>
                    @else
                        <div class="mt-3 rounded-kartu border border-dashed border-garis bg-white p-6 text-center">
                            <p class="font-semibold text-gray-700">Anda belum punya alamat tersimpan</p>
                            <p class="mt-1 text-sm text-gray-500">Tambahkan alamat di Buku Alamat untuk pengiriman kurir.</p>
                            <a href="{{ route('alamat.index') }}" class="mt-3 inline-block rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark">Isi Alamat Sekarang</a>
                        </div>
                    @endif
                </div>

                {{-- INFO AMBIL DI TOKO --}}
                <div x-show="metode === 'ambil_di_toko'" x-cloak class="mt-4 rounded-kartu border border-garis bg-primary-mist p-4 text-sm">
                    <p class="font-semibold">Lokasi Penjemputan Pesanan:</p>
                    <p class="mt-1">{{ config('toko.nama_toko') }} — {{ config('toko.alamat') }}</p>
                    <p class="mt-1 text-gray-600">Setelah pesanan dibuat, gudang langsung mengemas barang. Anda cukup datang ke kasir dan menunjukkan Kode Pengambilan.</p>
                </div>
            </section>

            {{-- METODE PEMBAYARAN --}}
            <section class="kartu p-5 sm:p-6">
                <h2 class="font-aksen text-lg font-bold">Metode Pembayaran</h2>
                <div class="mt-4 space-y-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-kartu border-2 p-4 transition"
                        :class="pembayaran === 'midtrans_qris' ? 'border-primary bg-primary-mist' : 'border-garis hover:border-primary'">
                        <input type="radio" name="metode_pembayaran" value="midtrans_qris" class="mt-1 h-4 w-4 text-primary focus:ring-primary/30" x-model="pembayaran">
                        <div>
                            <p class="font-semibold">QRIS Online (GoPay, OVO, Dana, ShopeePay, dll.)</p>
                            <p class="text-sm text-gray-500">Bayar langsung online sebelum datang ke toko / sebelum barang dikirim.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-kartu border-2 p-4 transition"
                        :class="pembayaran === 'midtrans_va_bca' ? 'border-primary bg-primary-mist' : 'border-garis hover:border-primary'">
                        <input type="radio" name="metode_pembayaran" value="midtrans_va_bca" class="mt-1 h-4 w-4 text-primary focus:ring-primary/30" x-model="pembayaran">
                        <div>
                            <p class="font-semibold">BCA Virtual Account</p>
                            <p class="text-sm text-gray-500">Transfer bank otomatis, terverifikasi dalam hitungan detik.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-kartu border-2 p-4 transition"
                        :class="pembayaran === 'midtrans_va_bri' ? 'border-primary bg-primary-mist' : 'border-garis hover:border-primary'">
                        <input type="radio" name="metode_pembayaran" value="midtrans_va_bri" class="mt-1 h-4 w-4 text-primary focus:ring-primary/30" x-model="pembayaran">
                        <div>
                            <p class="font-semibold">BRI Virtual Account</p>
                            <p class="text-sm text-gray-500">Transfer bank otomatis, terverifikasi dalam hitungan detik.</p>
                        </div>
                    </label>
                </div>
                <p class="mt-3 text-xs text-gray-400">* Pembayaran diproses aman melalui Midtrans. Data kartu/PIN tidak pernah disimpan di sistem toko.</p>
            </section>

            {{-- TOTAL & KIRIM --}}
            <section class="kartu sticky bottom-4 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Total yang dibayarkan</p>
                        <p class="font-aksen text-3xl font-bold text-primary">
                            Rp <span x-text="formatAngka(total)"></span>
                        </p>
                        <p class="text-xs text-gray-400">
                            Subtotal Rp {{ number_format($keranjang->total_harga, 0, ',', '.') }}
                            <span x-show="metode === 'kurir' && ongkirTerpilih">+ Ongkir <span x-text="formatAngka(ongkir)"></span></span>
                        </p>
                    </div>
                    <button type="submit"
                        class="rounded-lg bg-primary px-8 py-4 font-aksen text-base font-bold text-white shadow-terangkat transition hover:bg-primary-dark disabled:opacity-60">
                        Proses Pesanan & Bayar →
                    </button>
                </div>
            </section>
        </form>
    </div>

    @push('skrip')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkout', () => ({
                metode: 'ambil_di_toko',
                alamatId: @js($alamatDefault['id'] ?? null),
                ekspedisi: null,
                pembayaran: 'midtrans_qris',
                pilihanOngkir: [],
                memuatOngkir: false,
                subtotal: {{ $keranjang->total_harga }},
                ongkir: 0,

                init() {
                    if (this.alamatId) this.muatOngkir(this.alamatId, true);
                },

                pilih(m) { this.metode = m; },

                muatOngkir(id, senyap = false) {
                    this.alamatId = id;
                    if (! senyap) this.pilihanOngkir = [];
                    this.memuatOngkir = true;
                    fetch('{{ route('ongkir.hitung') }}?alamat_id=' + id, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(j => {
                            this.pilihanOngkir = j.pilihan;
                            if (! j.pilihan.some(o => o.kode === this.ekspedisi)) this.ekspedisi = null;
                        })
                        .finally(() => this.memuatOngkir = false);
                },

                get ongkirTerpilih() {
                    const o = this.pilihanOngkir.find(o => o.kode === this.ekspedisi);
                    if (o) this.ongkir = o.biaya;
                    return !! o;
                },

                get total() {
                    return this.subtotal + (this.metode === 'kurir' ? (this.ongkirTerpilih ? this.ongkir : 0) : 0);
                },

                formatAngka(n) { return new Intl.NumberFormat('id-ID').format(n); },
            }));
        });
    </script>
    @endpush
</x-layout-publik>
