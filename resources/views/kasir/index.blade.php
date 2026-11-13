<x-layout-staf>
    @section('judul', 'Kasir POS')

    <x-banner-staf badge="Mesin Kasir POS Toko Maya" judul="Sistem Kasir & Transaksi Toko"
        sub="Transaksi walk-in offline kasir, perhitungan uang kembalian otomatis, pemantauan transaksi online, dan verifikasi pengambilan barang.">
        <x-slot:aksi>
            <a href="{{ route('kasir.ambil') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">
                Ambil di Toko @if ($jumlahAmbil > 0)<span class="ms-1 rounded-full bg-rating px-2 text-xs font-bold text-navy">{{ $jumlahAmbil }}</span>@endif
            </a>
            <a href="{{ route('kasir.riwayat') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Riwayat Transaksi</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6" x-data="posKasir">
        <div class="grid gap-6 lg:grid-cols-[1fr_400px]">
            {{-- KIRI: KATALOG PRODUK --}}
            <div>
                <form method="GET" class="flex flex-wrap gap-2">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Cari produk…"
                        class="min-w-40 flex-1 rounded-lg border border-garis bg-white px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <select name="kategori" class="rounded-lg border border-garis bg-white px-3 py-2.5 text-sm outline-none focus:border-primary">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}" @selected($kategoriAktif === $k->id)>{{ $k->nama_kategori }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark">Cari</button>
                </form>

                <div class="mt-4 grid max-h-[70vh] grid-cols-2 gap-3 overflow-y-auto pe-1 sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($produk as $p)
                        <button type="button" @click="tambah({{ $p->id }})" @disabled($p->stok <= 0)
                            class="flex flex-col rounded-kartu border border-garis bg-white p-3 text-start shadow-kartu transition hover:border-primary hover:shadow-terangkat disabled:opacity-40">
                            <span class="line-clamp-2 min-h-10 text-xs font-semibold uppercase leading-snug">{{ $p->nama_produk }}</span>
                            <span class="mt-1 text-[11px] text-gray-400">Stok: {{ $p->stok }}</span>
                            <span class="mt-2 font-aksen text-base font-bold text-primary">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- KANAN: STRUK / NOTA --}}
            <aside class="h-fit kartu overflow-hidden lg:sticky lg:top-24">
                <div class="flex items-center gap-2 border-b border-garis bg-primary-mist px-5 py-3.5">
                    <span class="h-5 w-5 rounded bg-primary"></span>
                    <h2 class="font-aksen font-bold">Struk Belanja Kasir</h2>
                </div>

                <div class="px-5 py-4">
                    <p class="text-sm text-gray-500">Pelanggan: <span class="font-semibold text-gray-800">Pelanggan Walk-in</span></p>

                    <div x-show="items.length === 0" class="mt-6 rounded-lg border border-dashed border-garis p-6 text-center text-sm text-gray-400">
                        Pilih perkakas di samping untuk menambahkan ke nota kasir
                    </div>

                    <div x-show="items.length > 0" class="mt-3 max-h-64 space-y-2 overflow-y-auto pe-1">
                        <template x-for="(it, idx) in items" :key="it.produk_id">
                            <div class="flex items-center gap-2 rounded-lg border border-garis p-2">
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-xs font-semibold uppercase" x-text="it.nama"></p>
                                    <p class="text-[11px] text-gray-500" x-text="'Rp ' + formatAngka(it.harga) + ' × ' + it.jumlah"></p>
                                </div>
                                <div class="flex items-center rounded-md border border-garis">
                                    <button type="button" @click="kurang(idx)" class="grid h-7 w-7 place-items-center text-gray-500 hover:text-primary">−</button>
                                    <span class="w-6 text-center text-xs font-bold" x-text="it.jumlah"></span>
                                    <button type="button" @click="tambahItem(it)" class="grid h-7 w-7 place-items-center text-gray-500 hover:text-primary">+</button>
                                </div>
                                <p class="w-20 text-end text-xs font-bold" x-text="'Rp ' + formatAngka(it.harga * it.jumlah)"></p>
                                <button type="button" @click="hapus(idx)" class="text-bahaya hover:text-red-700" aria-label="Hapus">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="border-t border-garis px-5 py-4">
                    <div class="flex gap-2">
                        @foreach (['tunai' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit'] as $kode => $label)
                            <button type="button" @click="metode = '{{ $kode }}'"
                                :class="metode === '{{ $kode }}' ? 'bg-primary text-white shadow-kartu' : 'border border-garis bg-white text-gray-700 hover:border-primary'"
                                class="flex-1 rounded-lg py-2.5 text-sm font-semibold transition">{{ $label }}</button>
                        @endforeach
                    </div>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Total Tagihan</dt><dd class="font-aksen text-xl font-bold text-primary" x-text="'Rp ' + formatAngka(total)"></dd></div>
                        <template x-if="metode === 'tunai' && items.length > 0">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <dt class="text-gray-500 whitespace-nowrap">Nominal Uang Bayar:</dt>
                                    <input type="number" min="0" x-model.number="bayar" class="w-32 rounded-lg border border-garis bg-surface px-3 py-2 text-end text-sm outline-none focus:border-primary">
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="n in [50000, 100000, 150000, 200000, 500000]">
                                        <button type="button" @click="bayar += n" class="rounded-md bg-primary-soft px-2.5 py-1 text-[11px] font-bold text-primary-darker hover:bg-primary hover:text-white" x-text="formatAngka(n)"></button>
                                    </template>
                                    <button type="button" @click="bayar = 0" class="rounded-md bg-gray-100 px-2.5 py-1 text-[11px] font-bold text-gray-600 hover:bg-gray-200">Reset</button>
                                </div>
                                <div class="flex justify-between"><dt class="text-gray-500">Kembalian Otomatis:</dt><dd class="font-aksen font-bold text-sukses-teks" x-text="'Rp ' + formatAngka(kembalian)"></dd></div>
                            </div>
                        </template>
                    </dl>

                    <form action="{{ route('kasir.simpan') }}" method="POST" @submit="if (metode === 'tunai' && bayar < total) { event.preventDefault(); alert('Nominal uang bayar kurang dari total tagihan.'); } else { $el.querySelector('[name=items_json]').value = JSON.stringify(items); }">
                        @csrf
                        <input type="hidden" name="items_json" value="[]">
                        <input type="hidden" name="metode" :value="metode">
                        <button type="submit" :disabled="items.length === 0"
                            class="mt-4 w-full rounded-lg bg-primary py-3.5 font-aksen text-sm font-bold text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400">
                            Selesaikan Transaksi & Cetak Struk
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>

    @push('skrip')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posKasir', () => ({
                items: [],
                metode: 'tunai',
                bayar: 0,

                tambah(id) {
                    const src = {{ $produk->map(fn ($p) => ['produk_id' => $p->id, 'nama' => $p->nama_produk, 'harga' => (float) $p->harga, 'stok' => $p->stok])->values()->toJson() }};
                    const p = src.find(x => x.produk_id === id);
                    if (! p) return;
                    this.tambahItem(p);
                },

                tambahItem(p) {
                    const ada = this.items.find(x => x.produk_id === p.produk_id);
                    if (ada) {
                        if (ada.jumlah + 1 > ada.stok) { alert('Stok tidak mencukupi.'); return; }
                        ada.jumlah++;
                    } else {
                        this.items.push({ ...p, jumlah: 1 });
                    }
                },

                kurang(idx) { const it = this.items[idx]; if (it.jumlah > 1) it.jumlah--; else this.hapus(idx); },
                hapus(idx) { this.items.splice(idx, 1); },

                get total() { return this.items.reduce((s, i) => s + i.harga * i.jumlah, 0); },
                get kembalian() { return Math.max(0, this.bayar - this.total); },
                formatAngka(n) { return new Intl.NumberFormat('id-ID').format(n); },
            }));
        });
    </script>
    @endpush
</x-layout-staf>
