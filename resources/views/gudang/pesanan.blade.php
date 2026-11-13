<x-layout-staf>
    @section('judul', 'Gudang — Daftar Pesanan')

    <x-banner-staf badge="Portal Staf Logistik & Gudang" judul="Daftar Pesanan Gudang"
        sub="Kelola status pengemasan: tandai siap diambil untuk pesanan ambil di toko, atau input resi untuk pengiriman kurir.">
        <x-slot:aksi>
            <a href="{{ route('gudang.index') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Ringkasan</a>
            <a href="{{ route('gudang.stok') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Stok Barang</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6" x-data="{ modal: null, kodeModal: '', resi: '', ekspedisi: 'JNE Reguler' }">
        <div class="flex flex-wrap gap-2">
            @foreach (['diproses' => 'Perlu Dikemas', 'siap_diambil' => 'Siap Diambil', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai'] as $s => $label)
                <a href="{{ route('gudang.pesanan', ['status' => $s]) }}"
                    @class(['rounded-lg px-4 py-2.5 text-sm font-semibold transition', 'bg-primary text-white shadow-kartu' => $status === $s, 'text-gray-700 hover:bg-primary-mist' => $status !== $s])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mt-5 space-y-4">
            @forelse ($pesanan as $p)
                <div class="kartu p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-garis pb-3">
                        <div>
                            <span class="font-aksen font-bold text-primary">{{ $p->kode_pesanan }}</span>
                            <span class="ms-2 text-xs text-gray-400">{{ $p->tanggal_pesanan?->format('d M Y H:i') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">{{ $p->metode_pemenuhan === 'kurir' ? 'Kurir' : 'Ambil Toko' }}</span>
                            <x-badge-status :status="$p->status_pesanan" />
                        </div>
                    </div>

                    <div class="mt-3 grid gap-4 sm:grid-cols-[1fr_auto]">
                        <div>
                            <p class="text-sm"><span class="text-gray-500">Pelanggan:</span> <span class="font-semibold">{{ $p->user?->nama ?? 'Tanpa akun' }}</span> · {{ $p->user?->no_hp }}</p>
                            @if ($p->alamat_pengiriman)
                                <p class="mt-1 whitespace-pre-line text-sm text-gray-600">{{ $p->alamat_pengiriman }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($p->detail as $d)
                                    <span class="rounded-md bg-surface px-2.5 py-1 text-xs ring-1 ring-garis">
                                        {{ $d->produk->nama_produk }} <b class="text-primary">×{{ $d->jumlah }}</b>
                                    </span>
                                @endforeach
                            </div>
                            @if ($p->no_resi)
                                <p class="mt-3 text-sm text-gray-600">Resi: <span class="font-aksen font-bold">{{ $p->no_resi }}</span> · {{ $p->ekspedisi }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col items-end justify-center gap-2">
                            @if ($p->status_pesanan === 'diproses' && $p->metode_pemenuhan === 'ambil_di_toko')
                                <form action="{{ route('gudang.siap', $p) }}" method="POST"
                                    x-data @submit.prevent="if (confirm('Tandai SIAP DIAMBIL?')) $el.submit()">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white hover:bg-primary-dark">Tandai Siap Diambil</button>
                                </form>
                            @elseif (in_array($p->status_pesanan, ['diproses', 'dikirim']) && $p->metode_pemenuhan === 'kurir')
                                <button type="button" @click="modal = @js($p->id); kodeModal = @js($p->kode_pesanan); resi = @js($p->no_resi); ekspedisi = @js($p->ekspedisi ?? 'JNE Reguler')"
                                    class="rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white hover:bg-primary-dark">
                                    {{ $p->status_pesanan === 'dikirim' ? 'Edit Resi' : 'Input Resi & Kirim' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="kartu grid place-items-center gap-2 py-16 text-center">
                    <span class="text-4xl">📦</span>
                    <p class="font-semibold text-gray-700">Tidak ada pesanan dengan status ini</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $pesanan->links() }}</div>

        {{-- MODAL INPUT RESI --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/50 p-4" @click.self="modal = null" x-transition>
            <div class="w-full max-w-md rounded-kartu bg-white p-6 shadow-terangkat">
                <h3 class="font-aksen text-lg font-bold">Input Ekspedisi & Nomor Resi</h3>
                <p class="mt-1 text-sm text-gray-500" x-text="'Pesanan ' + kodeModal"></p>
                <form :action="'{{ url('gudang/pesanan') }}/' + modal + '/kirim'" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-medium">Kurir / Ekspedisi <span class="text-bahaya">*</span></label>
                        <select name="ekspedisi" x-model="ekspedisi" required class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                            <option value="JNE Reguler">JNE Reguler</option>
                            <option value="J&T Express">J&T Express</option>
                            <option value="SiCepat Reguler">SiCepat Reguler</option>
                            <option value="SiCepat GO">SiCepat GO</option>
                            <option value="AnterAja">AnterAja</option>
                            <option value="ID Express">ID Express</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Nomor Resi Pengiriman <span class="text-bahaya">*</span></label>
                        <input type="text" name="no_resi" x-model="resi" required placeholder="Contoh: JNE-TM5867248"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                        <p class="mt-1 text-xs text-gray-400">Nomor resi wajib dimasukkan agar pelanggan dapat melacak pengiriman pesanan.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 rounded-lg bg-primary py-3 text-sm font-bold text-white hover:bg-primary-dark">Simpan & Ubah Status "Dikirim"</button>
                        <button type="button" @click="modal = null" class="rounded-lg border border-garis px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-surface">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('skrip')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('x', () => ({}));
        });
    </script>
    @endpush
</x-layout-staf>