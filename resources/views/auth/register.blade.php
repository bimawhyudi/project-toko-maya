<x-guest-layout>
    <h1 class="font-sans text-3xl font-bold tracking-tight">Daftar Akun Baru</h1>
    <p class="mt-1 text-sm text-gray-500">Buat akun pelanggan Toko Maya, gratis</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6">
        @csrf

        <div>
            <x-input-label for="nama" value="Nama Lengkap" />
            <x-text-input id="nama" class="mt-1 block w-full" type="text" name="nama"
                :value="old('nama')" required autofocus autocomplete="name" placeholder="Nama Anda" />
            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="no_hp" value="Nomor HP" />
            <x-text-input id="no_hp" class="mt-1 block w-full" type="text" name="no_hp"
                :value="old('no_hp')" placeholder="08xx xxxx xxxx" autocomplete="tel" />
            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email"
                :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password"
                required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="mt-6 w-full">
            DAFTAR SEKARANG
        </x-primary-button>

        <p class="mt-4 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-primary-dark">Masuk</a>
        </p>
    </form>
</x-guest-layout>