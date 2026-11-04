<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h1 class="font-sans text-3xl font-bold tracking-tight">Masuk ke Toko Maya</h1>
    <p class="mt-1 text-sm text-gray-500">Pusat Alat Bangunan & Perlengkapan Bengkel</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat Email" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email"
                :value="old('email')" required autofocus autocomplete="username"
                placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Kata Sandi" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password"
                required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" value="1"
                    class="h-4 w-4 rounded border-garis text-primary focus:ring-primary/30" @checked(old('remember'))>
                Ingat saya
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-primary hover:text-primary-dark" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-primary-button class="mt-6 w-full">
            MASUK
        </x-primary-button>

        <p class="mt-4 text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-primary-dark">Daftar</a>
        </p>
    </form>
</x-guest-layout>