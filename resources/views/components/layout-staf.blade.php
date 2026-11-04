@props(['judul' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', $judul ?? 'Dasbor') — {{ config('toko.nama_toko') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
    <x-topbar />
    <x-navbar :aktif="$aktif ?? ''" />

    <main class="flex-1 pb-16">
        {{ $slot }}
    </main>

    <x-footer-toko />

    @if (session('berhasil'))
        <x-flash-pesan :pesan="session('berhasil')" />
    @endif
    @if (session('peringatan'))
        <x-flash-pesan :pesan="session('peringatan')" tipe="peringatan" />
    @endif
    @if ($errors->any())
        <x-flash-pesan :pesan="$errors->first()" tipe="peringatan" />
    @endif

    @stack('skrip')
</body>
</html>