<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('judul', 'Masuk') — {{ config('toko.nama_toko') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%23155DFC'/><text x='50' y='68' font-size='52' text-anchor='middle' fill='white' font-family='Arial' font-weight='bold'>TM</text></svg>">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <x-topbar />

        <div class="flex min-h-[calc(100vh-160px)] flex-col items-center justify-center bg-gradient-to-b from-primary-mist to-surface px-4 py-10">
            <div class="mb-6">
                <x-logo-toko />
            </div>

            <div class="w-full max-w-md rounded-kartu border border-garis bg-white p-6 shadow-terangkat sm:p-8">
                {{ $slot }}
            </div>
        </div>

        <footer class="border-t border-garis bg-white py-4 text-center text-xs text-gray-400">
            © {{ date('Y') }} {{ config('toko.nama_toko') }} — Pasar Raya Barat, Padang
        </footer>

        @if (session('berhasil'))
            <x-flash-pesan :pesan="session('berhasil')" />
        @endif
        @if (session('peringatan'))
            <x-flash-pesan :pesan="session('peringatan')" tipe="peringatan" />
        @endif
        @if ($errors->any())
            <x-flash-pesan :pesan="$errors->first()" tipe="peringatan" />
        @endif
    </body>
</html>