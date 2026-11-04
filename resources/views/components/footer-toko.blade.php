<footer class="mt-16 border-t border-garis bg-white">
    <div class="mx-auto grid max-w-halaman gap-8 px-4 py-10 sm:px-6 md:grid-cols-3">
        <div>
            <x-logo-toko />
            <p class="mt-3 max-w-xs text-sm text-gray-500">
                Toko baut, perkakas, dan alat teknik lengkap. Pesan online, ambil di toko, atau kirim lewat kurir.
            </p>
        </div>
        <div class="text-sm">
            <p class="font-semibold text-gray-900">Alamat Toko</p>
            <p class="mt-2 text-gray-500">{{ config('toko.alamat') }}</p>
            <p class="mt-1 text-gray-500">Telp/WA: {{ config('toko.no_hp') }}</p>
        </div>
        <div class="text-sm">
            <p class="font-semibold text-gray-900">Jam Buka</p>
            <p class="mt-2 text-gray-500">Senin – Sabtu: 08.00 – 17.00 WIB</p>
            <p class="mt-1 text-gray-500">Minggu: 08.00 – 12.00 WIB</p>
        </div>
    </div>
    <div class="border-t border-garis py-4 text-center text-xs text-gray-400">
        © {{ date('Y') }} {{ config('toko.nama_toko') }} — Pasar Raya Barat, Padang
    </div>
</footer>
