<x-layout-publik>
    @section('judul', 'Profil Saya')

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h1 class="judul-halaman">Profil Saya</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola data akun, kata sandi, dan buku alamat Anda.</p>

        <div class="mt-6 space-y-6">
            {{-- INFORMASI AKUN --}}
            <div class="kartu p-6">
                <h2 class="font-aksen text-lg font-bold">Informasi Akun</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="nama" value="Nama Lengkap" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $user->nama)" required autofocus autocomplete="name" />
                        <x-input-error class="mt-2" :messages="$errors->get('nama')" />
                    </div>

                    <div>
                        <x-input-label for="no_hp" value="Nomor HP" />
                        <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full"
                            :value="old('no_hp', $user->no_hp)" placeholder="08xx xxxx xxxx" autocomplete="tel" />
                        <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Alamat Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Simpan Perubahan</x-primary-button>

                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                                class="text-sm font-medium text-sukses-teks">Perubahan tersimpan.</p>
                        @endif
                    </div>
                </form>
            </div>

            {{-- KATA SANDI --}}
            <div class="kartu p-6">
                <h2 class="font-aksen text-lg font-bold">Ubah Kata Sandi</h2>
                <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-5">
                    @csrf
                    @method('put')

                    <div>
                        <x-input-label for="current_password" value="Kata Sandi Saat Ini" />
                        <x-text-input id="current_password" name="current_password" type="password"
                            class="mt-1 block w-full" autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Kata Sandi Baru" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                            autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('updatePassword.password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Ulangi Kata Sandi Baru" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                            class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('updatePassword.password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>Simpan Kata Sandi</x-primary-button>

                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                                class="text-sm font-medium text-sukses-teks">Kata sandi diperbarui.</p>
                        @endif
                    </div>
                </form>
            </div>

            {{-- BUKU ALAMAT (pelanggan) --}}
            @if ($user->role === 'pelanggan')
                <div class="kartu flex items-center justify-between gap-4 p-6">
                    <div>
                        <h2 class="font-aksen text-lg font-bold">Buku Alamat</h2>
                        <p class="mt-1 text-sm text-gray-500">Kelola daftar alamat pengiriman Anda.</p>
                    </div>
                    <a href="{{ route('alamat.index') }}"
                        class="rounded-lg bg-primary-soft px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary hover:text-white">
                        Buka Buku Alamat →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layout-publik>
