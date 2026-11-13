<x-layout-publik>
    @section('judul', 'Buku Alamat')

    <div x-data="bukuAlamat" class="mx-auto max-w-halaman px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Buku Alamat</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola alamat pengiriman; alamat utama dipakai otomatis saat checkout.</p>
            </div>
            <button type="button" @click="bukaForm()"
                class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">
                + Tambah Alamat
            </button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            {{-- FORM --}}
            <div x-show="formTerbuka" x-cloak class="kartu p-5 md:col-span-2">
                <h2 class="font-aksen text-lg font-bold" x-text="sedangEdit ? 'Ubah Alamat' : 'Tambah Alamat Baru'"></h2>
                <form :action="sedangEdit ? '{{ route('alamat.index') }}/' + sedangEdit : '{{ route('alamat.store') }}'"
                    method="POST" class="mt-4 grid gap-4 sm:grid-cols-2" x-ref="formAlamat">
                    <input type="hidden" name="_method" :value="sedangEdit ? 'PUT' : 'POST'">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div>
                        <label class="text-sm font-medium">Label <span class="text-bahaya">*</span></label>
                        <input name="label" required placeholder="Rumah / Kantor / Bengkel" value="Rumah"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Nama Penerima <span class="text-bahaya">*</span></label>
                        <input name="nama_penerima" required placeholder="Nama penerima"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">No. HP <span class="text-bahaya">*</span></label>
                        <input name="no_hp" required placeholder="08xx xxxx xxxx"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Provinsi <span class="text-bahaya">*</span></label>
                        <input name="provinsi" required placeholder="Sumatera Barat"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Kota / Kabupaten <span class="text-bahaya">*</span></label>
                        <input name="kota" required placeholder="Padang"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Kecamatan <span class="text-bahaya">*</span></label>
                        <input name="kecamatan" required placeholder="Kecamatan…"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Kode Pos <span class="text-bahaya">*</span></label>
                        <input name="kode_pos" required placeholder="25100"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium">Alamat Lengkap <span class="text-bahaya">*</span></label>
                        <textarea name="alamat_lengkap" required rows="3" placeholder="Nama jalan, nomor rumah, patokan…"
                            class="mt-1 w-full rounded-lg border border-garis bg-surface px-3 py-2.5 text-sm outline-none focus:border-primary"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm sm:col-span-2">
                        <input type="checkbox" name="is_default" value="1"
                            class="h-4 w-4 rounded border-garis text-primary focus:ring-primary/30">
                        Jadikan alamat utama (otomatis terpilih saat checkout)
                    </label>

                    <div class="flex gap-2 sm:col-span-2">
                        <button type="submit"
                            class="rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Simpan</button>
                        <button type="button" @click="tutupForm()"
                            class="rounded-lg border border-garis px-6 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-surface">Batal</button>
                    </div>
                </form>
            </div>

            {{-- DAFTAR ALAMAT --}}
            @forelse ($daftar as $a)
                <div class="kartu flex flex-col p-5 {{ $a['is_default'] ? 'ring-2 ring-primary' : '' }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded-full bg-primary-soft px-3 py-1 text-xs font-bold text-primary-darker">{{ $a['label'] }}</span>
                        @if ($a['is_default'])
                            <span class="rounded-full bg-sukses/10 px-3 py-1 text-xs font-bold text-sukses-teks">Alamat Utama</span>
                        @endif
                    </div>
                    <p class="mt-3 font-semibold">{{ $a['nama_penerima'] }} <span class="font-normal text-gray-500">· {{ $a['no_hp'] }}</span></p>
                    <p class="mt-1 text-sm leading-relaxed text-gray-600">
                        {{ $a['alamat_lengkap'] }}, {{ $a['kota'] }}, Kec. {{ $a['kecamatan'] }}, {{ $a['provinsi'] }} {{ $a['kode_pos'] }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2 pt-2">
                        @unless ($a['is_default'])
                            <form action="{{ route('alamat.default', $a['id']) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg bg-primary-soft px-3 py-2 text-xs font-semibold text-primary transition hover:bg-primary hover:text-white">
                                    Jadikan Utama
                                </button>
                            </form>
                        @endunless
                        <button type="button" @click="isiForm(@js($a))" class="rounded-lg border border-garis px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-surface">
                            Ubah
                        </button>
                        <form action="{{ route('alamat.destroy', $a['id']) }}" method="POST"
                            @submit.prevent="if (confirm('Hapus alamat ini?')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg bg-bahaya/10 px-3 py-2 text-xs font-semibold text-bahaya transition hover:bg-bahaya hover:text-white">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="kartu grid place-items-center gap-2 py-14 text-center md:col-span-2">
                    <span class="text-4xl">📍</span>
                    <p class="font-semibold text-gray-700">Belum ada alamat tersimpan</p>
                    <p class="text-sm text-gray-500">Tambahkan alamat agar bisa pesan dengan pengiriman kurir.</p>
                </div>
            @endforelse
        </div>
    </div>

    @push('skrip')
    @once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bukuAlamat', () => ({
                formTerbuka: false,
                sedangEdit: null,
                bukaForm() { this.sedangEdit = null; this.formTerbuka = true; this.$nextTick(() => this.$refs.formAlamat?.reset?.()); },
                tutupForm() { this.formTerbuka = false; this.sedangEdit = null; },
                isiForm(a) {
                    this.sedangEdit = a.id;
                    const f = this.$refs.formAlamat;
                    f.querySelector('[name=label]').value = a.label;
                    f.querySelector('[name=nama_penerima]').value = a.nama_penerima;
                    f.querySelector('[name=no_hp]').value = a.no_hp;
                    f.querySelector('[name=provinsi]').value = a.provinsi;
                    f.querySelector('[name=kota]').value = a.kota;
                    f.querySelector('[name=kecamatan]').value = a.kecamatan;
                    f.querySelector('[name=kode_pos]').value = a.kode_pos;
                    f.querySelector('[name=alamat_lengkap]').value = a.alamat_lengkap;
                    f.querySelector('[name=is_default]').checked = !!a.is_default;
                    this.formTerbuka = true;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
            }));
        });
    </script>
    @endonce
    @endpush
</x-layout-publik>
