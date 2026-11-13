<x-layout-staf>
    @section('judul', 'Gudang — Stok Barang')

    <x-banner-staf badge="Portal Staf Logistik & Gudang" judul="Stok Barang Gudang"
        sub="Pantau stok fisik: cari, filter status stok, dan perbarui jumlah stok hasil hitung fisik.">
        <x-slot:aksi>
            <a href="{{ route('gudang.index') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Ringkasan</a>
            <a href="{{ route('gudang.pesanan') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Daftar Pesanan</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6" x-data="{ modal: null, namaModal: '', stokModal: 0 }">
        <div class="kartu flex flex-wrap items-center gap-3 p-4">
            <form method="GET" class="flex flex-1 flex-wrap items-center gap-3">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk…"
                    class="min-w-40 flex-1 rounded-lg border border-garis bg-surface px-4 py-2.5 text-sm outline-none focus:border-primary">
                <button type="submit" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark">Cari</button>
            </form>
            <div class="flex gap-2">
                @foreach (['' => 'Semua Status Stok', 'aman' => 'Hanya Stok Aman', 'menipis' => 'Hanya Stok Menipis', 'habis' => 'Hanya Stok Habis'] as $f => $label)
                    <a href="{{ route('gudang.stok', array_filter(['filter' => $f, 'q' => request('q')])) }}"
                        @class(['rounded-lg px-3 py-2 text-xs font-semibold transition', 'bg-primary text-white' => $filter === $f, 'bg-surface text-gray-600 ring-1 ring-garis hover:text-primary' => $filter !== $f])>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="kartu mt-4 overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-surface text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-start">Produk</th>
                        <th class="px-4 py-3 text-start">Kategori</th>
                        <th class="px-4 py-3 text-end">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-start">Status Gudang</th>
                        <th class="px-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/70">
                    @forelse ($stok as $s)
                        <tr class="hover:bg-primary-mist/50">
                            <td class="px-4 py-3 font-semibold">{{ $s->nama_produk }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $s->kategori->nama_kategori }}</td>
                            <td class="px-4 py-3 text-end font-aksen font-bold">{{ $s->stok }}</td>
                            <td class="px-4 py-3">
                                @if ($s->stok <= 0)
                                    <span class="rounded-full bg-bahaya/10 px-3 py-1 text-xs font-bold text-bahaya">Stok Habis</span>
                                @elseif ($s->stok <= config('toko.ambang_stok_menipis', 5))
                                    <span class="rounded-full bg-rating/20 px-3 py-1 text-xs font-bold text-yellow-800">Stok Menipis</span>
                                @else
                                    <span class="rounded-full bg-sukses/10 px-3 py-1 text-xs font-bold text-sukses-teks">Aman</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button type="button" @click="modal = {{ $s->id }}; namaModal = @js($s->nama_produk); stokModal = {{ $s->stok }}"
                                    class="rounded-lg bg-primary-soft px-4 py-2 text-xs font-bold text-primary transition hover:bg-primary hover:text-white">
                                    Ubah Stok
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">Tidak ada produk dengan filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $stok->links() }}</div>

        {{-- MODAL UBAH STOK --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/50 p-4" @click.self="modal = null" x-transition>
            <div class="w-full max-w-sm rounded-kartu bg-white p-6 shadow-terangkat">
                <h3 class="font-aksen text-lg font-bold">Ubah Stok</h3>
                <p class="mt-1 truncate text-sm text-gray-500" x-text="namaModal"></p>
                <form :action="'{{ url('gudang/stok') }}/' + modal" method="POST" class="mt-4">
                    @csrf
                    <label class="text-sm font-medium">Jumlah stok baru</label>
                    <input type="number" name="stok" min="0" x-model.number="stokModal" required
                        class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-center font-aksen text-xl font-bold outline-none focus:border-primary">
                    <div class="mt-4 flex gap-2">
                        <button type="submit" class="flex-1 rounded-lg bg-primary py-3 text-sm font-bold text-white hover:bg-primary-dark">Simpan</button>
                        <button type="button" @click="modal = null" class="rounded-lg border border-garis px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-surface">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout-staf>