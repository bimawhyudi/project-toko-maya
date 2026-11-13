<?php

use App\Console\Commands\BatalkanPesananKedaluwarsa;
use Illuminate\Support\Facades\Schedule;

// Pesanan daring yang tidak dibayar sampai expired_at otomatis menjadi batal.
// Dijalankan setiap 15 menit oleh scheduler (php artisan schedule:work / cron).
Schedule::command(BatalkanPesananKedaluwarsa::class)->everyFifteenMinutes();
