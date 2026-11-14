<x-layout-staf>
    @section('judul', 'Pemilik — Manajemen Produk')

    <x-banner-staf badge="Portal Eksekutif Pemilik Toko" judul="Manajemen Produk & Kategori"
        sub="Tambah perkakas baru, ubah harga, stok, deskripsi, foto produk, kelola kategori, atau nonaktifkan produk.">
        <x-slot:aksi>
            <a href="{{ route('pemilik.index') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Dashboard Utama</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6" x-data="manajemenProduk">
        <div class="flex flex-wrap justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <span class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-kartu">Daftar Produk ({{ $produk->total() }})</span>
            </div>
            <button type="button" @click="bukaTambah()" class="rounded-lg bg-primary px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary-dark">
                + Tambah Produk Baru
            </button>
        </div>

        {{-- FORM TAMBAH --}}
        <div x-show="panelTambah" x-cloak class="kartu mt-4 p-6">
            <h2 class="font-aksen text-lg font-bold">Tambah Produk Baru ke Katalog</h2>
            <form action="{{ route('pemilik.produk.simpan') }}" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium">Nama Produk <span class="text-bahaya">*</span></label>
                    <input name="nama_produk" required maxlength="150" placeholder="Contoh: MATA BOR SET HSS 13 PCS"
                        class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-sm font-medium">Kategori <span class="text-bahaya">*</span></label>
                    <select name="kategori_id" required class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Harga (Rp) <span class="text-bahaya">*</span></label>
                    <input type="number" name="harga" required min="0" step="0.01" placeholder="25000"
                        class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-sm font-medium">Stok <span class="text-bahaya">*</span></label>
                    <input type="number" name="stok" required min="0" placeholder="20"
                        class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                </div>
                <div>
                    <label class="text-sm font-medium">Foto Produk (maks 2 MB)</label>
                    <input type="file" name="gambar" accept="image/*"
                        class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2 text-sm file:me-3 file:rounded-md file:border-0 file:bg-primary-soft file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-primary">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium">Deskripsi / Spesifikasi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Spesifikasi teknis, isi kemasan, dsb."
                        class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary"></textarea>
                </div>
                <div class="flex gap-2 sm:col-span-2">
                    <button type="submit" class="rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white hover:bg-primary-dark">Simpan Produk</button>
                    <button type="button" @click="panelTambah = false" class="rounded-lg border border-garis px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-surface">Batal</button>
                </div>
            </form>
        </div>

        {{-- KATEGORI --}}
        <div class="kartu mt-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-aksen text-lg font-bold">Kategori ({{ $kategori->count() }})</h2>
                <form action="{{ route('pemilik.kategori.simpan') }}" method="POST" class="flex gap-2">
                    @csrf
                    <input name="nama_kategori" required placeholder="Nama kategori baru" maxlength="100"
                        class="w-56 rounded-lg border border-garis bg-surface px-3 py-2 text-sm outline-none focus:border-primary">
                    <button type="submit" class="rounded-lg bg-navy px-4 py-2 text-xs font-bold text-white hover:bg-primary-darker">+ Kategori</button>
                </form>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($kategori as $k)
                    <span class="flex items-center gap-2 rounded-full bg-primary-mist px-3 py-1.5 text-sm">
                        {{ $k->nama_kategori }}
                        <form action="{{ route('pemilik.kategori.hapus', $k) }}" method="POST"
                            x-data @submit.prevent="if (confirm('Hapus kategori ini?')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-bahaya hover:text-red-700" title="Hapus kategori">×</button>
                        </form>
                    </span>
                @endforeach
            </div>
        </div>

        {{-- FILTER & PENCARIAN --}}
        <div class="kartu mt-6 p-4">
            <form method="GET" action="{{ route('pemilik.produk') }}" class="flex flex-wrap items-center gap-3">
                <input
                    type="search"
                    name="q"
                    value="{{ $kataKunci }}"
                    placeholder="Cari nama produk…"
                    class="min-w-48 flex-1 rounded-lg border border-garis bg-surface px-4 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                >
                <select
                    name="kategori"
                    onchange="this.form.submit()"
                    class="rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Semua Kategori</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" @selected((int) $kategoriDipilih === $k->id)>
                            {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                    class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    Cari
                </button>
                @if ($kataKunci !== '' || $kategoriDipilih)
                    <a href="{{ route('pemilik.produk') }}"
                        class="rounded-lg border border-garis px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-surface">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- TABEL PRODUK --}}
        <div class="kartu mt-4 overflow-x-auto">
            <table class="w-full min-w-[920px] text-sm">
                <thead class="bg-surface text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-start">Produk</th>
                        <th class="px-4 py-3 text-start">Kategori</th>
                        <th class="px-4 py-3 text-end">Harga</th>
                        <th class="px-4 py-3 text-end">Stok</th>
                        <th class="px-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/70">
                    @forelse ($produk as $p)
                        <tr class="hover:bg-primary-mist/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-10 w-10 shrink-0 place-items-center overflow-hidden rounded-lg bg-primary-mist">
                                        @if ($p->gambar)<img src="{{ asset('storage/' . $p->gambar) }}" alt="" class="h-full w-full rounded-lg object-cover">@else<span class="text-lg">🔧</span>@endif
                                    </div>
                                    <span class="font-semibold">{{ $p->nama_produk }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $p->kategori->nama_kategori }}</td>
                            <td class="px-4 py-3 text-end font-semibold">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-end {{ $p->stok <= config('toko.ambang_stok_menipis', 5) ? 'font-bold text-bahaya' : '' }}">{{ $p->stok }}</td>
                            <td class="px-4 py-3 text-end">
                                <button type="button" @click="bukaUbah(@js($p->toArray()), @js($p->kategori_id))"
                                    class="rounded-lg bg-primary-soft px-3 py-1.5 text-xs font-bold text-primary transition hover:bg-primary hover:text-white">
                                    Ubah
                                </button>
                                <form action="{{ route('pemilik.produk.hapus', $p) }}" method="POST" class="inline"
                                    x-data @submit.prevent="if (confirm('Hapus produk ini dari katalog?')) $el.submit()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-bahaya/10 px-3 py-1.5 text-xs font-bold text-bahaya transition hover:bg-bahaya hover:text-white">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                                {{ ($kataKunci !== '' || $kategoriDipilih) ? 'Tidak ada produk yang cocok dengan pencarian/filter.' : 'Katalog kosong.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $produk->links() }}</div>

        {{-- MODAL UBAH PRODUK --}}
        <div x-show="modalUbah" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 p-4" @click.self="modalUbah = false" x-transition>
            <div class="mx-auto mt-8 mb-8 w-full max-w-2xl rounded-kartu bg-white p-6 shadow-terangkat">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-aksen text-lg font-bold">Ubah Produk</h3>
                    <button type="button" @click="modalUbah = false" class="text-2xl leading-none text-gray-400 hover:text-gray-700" aria-label="Tutup">&times;</button>
                </div>

                <div class="mt-4 flex items-center gap-4 rounded-kartu border border-garis bg-surface p-4">
                    <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-lg bg-primary-mist">
                        <template x-if="gambarLama">
                            <img :src="'{{ asset('storage') }}/' + gambarLama" alt="" class="h-full w-full object-cover">
                        </template>
                        <template x-if="! gambarLama">
                            <span class="text-2xl">🔧</span>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500">Foto saat ini. Unggah foto baru di bawah bila ingin mengganti (kosongkan bila tetap).</p>
                </div>

                <form :action="'{{ route('pemilik.produk') }}/' + editId" method="POST" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium">Nama Produk <span class="text-bahaya">*</span></label>
                        <input name="nama_produk" required maxlength="150" x-model="f.nama"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Kategori <span class="text-bahaya">*</span></label>
                        <select name="kategori_id" required x-model="f.kategori_id"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                            @foreach ($kategori as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Harga (Rp) <span class="text-bahaya">*</span></label>
                        <input type="number" name="harga" required min="0" step="0.01" x-model.number="f.harga"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Stok <span class="text-bahaya">*</span></label>
                        <input type="number" name="stok" required min="0" x-model.number="f.stok"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Ganti Foto (opsional, maks 2 MB)</label>
                        <input type="file" name="gambar" accept="image/*"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2 text-sm file:me-3 file:rounded-md file:border-0 file:bg-primary-soft file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium">Deskripsi / Spesifikasi</label>
                        <textarea name="deskripsi" rows="3" x-model="f.deskripsi"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary"></textarea>
                    </div>
                    <div class="flex gap-2 sm:col-span-2">
                        <button type="submit" class="rounded-lg bg-primary px-6 py-3 text-sm font-bold text-white hover:bg-primary-dark">Simpan Perubahan</button>
                        <button type="button" @click="modalUbah = false" class="rounded-lg border border-garis px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-surface">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('skrip')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('manajemenProduk', () => ({
                panelTambah: false,
                modalUbah: false,
                editId: null,
                gambarLama: null,
                f: { nama: '', kategori_id: '', harga: 0, stok: 0, deskripsi: '' },

                bukaTambah() {
                    this.panelTambah = ! this.panelTambah;
                    this.modalUbah = false;
                },

                bukaUbah(p, kategoriId) {
                    this.editId = p.id;
                    this.gambarLama = p.gambar;
                    this.f = {
                        nama: p.nama_produk,
                        kategori_id: String(kategoriId),
                        harga: p.harga,
                        stok: p.stok,
                        deskripsi: p.deskripsi || '',
                    };
                    this.modalUbah = true;
                    this.panelTambah = false;
                },
            }));
        });
    </script>
    @endpush
</x-layout-staf>