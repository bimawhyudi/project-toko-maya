<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan hasil pembayaran Core API Midtrans (nomor VA / QRIS, dsb) per pesanan.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->json('pembayaran')->nullable()->after('metode_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
        });
    }
};
