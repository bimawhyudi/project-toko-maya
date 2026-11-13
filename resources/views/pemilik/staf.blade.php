<x-layout-staf>
    @section('judul', 'Pemilik — Kelola Staf')

    <x-banner-staf badge="Portal Eksekutif Pemilik Toko" judul="Manajemen Pengguna & Hak Akses"
        sub="Daftar akun terdaftar dan ubah peran staf: pelanggan, kasir, admin gudang, atau pemilik.">
        <x-slot:aksi>
            <a href="{{ route('pemilik.index') }}" class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold ring-1 ring-white/30 transition hover:bg-white/25">Dashboard Utama</a>
        </x-slot:aksi>
    </x-banner-staf>

    <div class="mx-auto max-w-halaman px-4 py-6 sm:px-6">
        <div class="kartu overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-surface text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-start">Nama</th>
                        <th class="px-4 py-3 text-start">Email</th>
                        <th class="px-4 py-3 text-start">No. HP</th>
                        <th class="px-4 py-3 text-start">Peran Saat Ini</th>
                        <th class="px-4 py-3 text-end">Ubah Peran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis/70">
                    @foreach ($pengguna as $u)
                        <tr class="hover:bg-primary-mist/50">
                            <td class="px-4 py-3 font-semibold">{{ $u->nama }} @if ($u->id === auth()->id())<span class="ms-1 text-xs text-gray-400">(Anda)</span>@endif</td>
                            <td class="px-4 py-3 text-gray-500">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $u->no_hp ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ ['pemilik' => 'bg-navy text-white ring-navy', 'kasir' => 'bg-primary text-white ring-primary', 'admin_gudang' => 'bg-primary-soft text-primary-darker ring-primary/30', 'pelanggan' => 'bg-surface text-gray-600 ring-garis'][$u->role] ?? '' }}">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                @if ($u->id !== auth()->id())
                                    <form action="{{ route('pemilik.staf.peran', $u) }}" method="POST" class="flex items-center justify-end gap-2"
                                        x-data @submit.prevent="if (confirm('Ubah peran ' + @js($u->nama) + '?')) $el.submit()">
                                        @csrf
                                        <select name="role" class="rounded-lg border border-garis bg-surface px-2 py-1.5 text-xs outline-none focus:border-primary">
                                            @foreach (['pelanggan', 'kasir', 'admin_gudang', 'pemilik'] as $r)
                                                <option value="{{ $r }}" @selected($u->role === $r)>{{ $r }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-dark">Simpan</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $pengguna->links() }}</div>
    </div>
</x-layout-staf>