<x-layout-staf>
    @section('judul', 'Kasir — Ambil di Toko')

    <x-banner-staf badge="Mesin Kasir POS Toko Maya" judul="Verifikasi Ambil di Toko"
        sub="Ketik kode pesanan pelanggan untuk memverifikasi dan menyerahkan barang yang sudah siap di gudang.">
        <x-slot:aksi>
            <a href="{{ route('kasir.index') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Kembali ke POS</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_420px]">
            {{-- VERIFIKASI KODE --}}
            <div>
                <form method="GET" class="kartu flex gap-2 p-4">
                    <input type="search" name="kode" value="{{ request('kode') }}" placeholder="KETIK KODE AMBIL (contoh: TM-20261030-XXXX)"
                        class="flex-1 rounded-lg border border-garis bg-surface px-4 py-3 text-sm font-semibold uppercase outline-none focus:border-primary focus:ring-2 focus:ring-primary/20" required>
                    <button type="submit" class="rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white transition hover:bg-primary-dark">Cek Kode</button>
                </form>

                @if (request()->filled('kode'))
                    @if ($hasil)
                        <div class="kartu mt-4 overflow-hidden">
                            @if ($hasil->status_pesanan === 'selesai')
                                <div class="bg-sukses/10 px-5 py-4">
                                    <p class="font-aksen font-bold text-sukses-teks">✔ Pesanan telah selesai diserahkan ke pelanggan</p>
                                    <p class="text-sm text-gray-600">Barang sudah diambil; tidak perlu tindakan lagi.</p>
                                </div>
                            @else
                                <div class="bg-rating/15 px-5 py-4">
                                    <p class="font-aksen font-bold text-yellow-800">SIAP DI MEJA KASIR — siap diserahkan</p>
                                    <p class="text-sm text-gray-600">Verifikasi data pelanggan & barang di bawah, lalu tekan tombol serah terima.</p>
                                </div>
                            @endif
                            <div class="p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-aksen text-2xl font-bold text-primary">{{ $hasil->kode_pesanan }}</p>
                                        <p class="text-sm text-gray-500">Pelanggan: {{ $hasil->user?->nama ?? 'Tanpa akun' }} · {{ $hasil->user?->no_hp }}</p>
                                    </div>
                                    <x-badge-status :status="$hasil->status_pesanan" />
                                </div>
                                <p class="mt-4 text-xs font-bold uppercase text-gray-400">Daftar barang yang harus diberikan ke pelanggan:</p>
                                <div class="mt-2 divide-y divide-garis/70 rounded-lg border border-garis">
                                    @foreach ($hasil->detail as $d)
                                        <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                                            <span class="font-semibold">{{ $d->produk->nama_produk }}</span>
                                            <span class="text-gray-500">{{ $d->jumlah }} buah</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 rounded-lg bg-surface p-3 text-sm">
                                    <p>Status pembayaran: <span class="font-bold text-sukses-teks">Lunas Online (bebas tagihan)</span></p>
                                </div>
                                @if ($hasil->status_pesanan === 'siap_diambil')
                                    <form action="{{ route('kasir.serahkan') }}" method="POST" class="mt-4"
                                        x-data @submit.prevent="if (confirm('Konfirmasi barang sudah diserahkan kepada pelanggan?')) $el.submit()">
                                        @csrf
                                        <input type="hidden" name="kode_pesanan" value="{{ $hasil->kode_pesanan }}">
                                        <button type="submit" class="w-full rounded-lg bg-sukses py-3.5 font-aksen text-sm font-bold text-white transition hover:bg-emerald-600">
                                            Serahkan Barang & Tandai Selesai
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="kartu mt-4 border-bahaya/40 p-6 text-center">
                            <p class="font-aksen font-bold text-bahaya">Kode tidak ditemukan / pesanan belum siap</p>
                            <p class="mt-1 text-sm text-gray-500">Pastikan kode benar dan gudang sudah menandai pesanan "Siap Diambil".</p>
                        </div>
                    @endif
                @endif

                {{-- ANTREAN --}}
                <div class="kartu mt-4 p-5">
                    <h2 class="font-aksen font-bold">Siap Di Meja Kasir ({{ $antrean->count() }})</h2>
                    <div class="mt-3 space-y-2">
                        @forelse ($antrean as $p)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-garis p-3">
                                <div>
                                    <a href="{{ route('kasir.ambil', ['kode' => $p->kode_pesanan]) }}" class="font-aksen font-bold text-primary hover:text-primary-dark">{{ $p->kode_pesanan }}</a>
                                    <p class="text-xs text-gray-500">{{ $p->user?->nama }} · {{ $p->detail->sum('jumlah') }} buah</p>
                                </div>
                                <form action="{{ route('kasir.serahkan') }}" method="POST"
                                    x-data @submit.prevent="if (confirm('Serahkan barang untuk ' + '{{ $p->kode_pesanan }}' + '?')) $el.submit()">
                                    @csrf
                                    <input type="hidden" name="kode_pesanan" value="{{ $p->kode_pesanan }}">
                                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-primary-dark">Serahkan</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">Tidak ada pesanan yang menunggu diambil.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- BARU SELESAI --}}
            <aside class="kartu h-fit p-5 lg:sticky lg:top-24">
                <h2 class="font-aksen font-bold">Baru Saja Diserahkan Hari Ini</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($selesaiHariIni as $p)
                        <div class="rounded-lg border border-sukses/30 bg-sukses/5 p-3">
                            <p class="font-aksen text-sm font-bold text-sukses-teks">{{ $p->kode_pesanan }}</p>
                            <p class="text-xs text-gray-500">{{ $p->updated_at->format('H:i') }} · {{ $p->detail->sum('jumlah') }} buah · diserahkan oleh {{ auth()->user()->nama }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Belum ada serah terima hari ini.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>
</x-layout-staf>
