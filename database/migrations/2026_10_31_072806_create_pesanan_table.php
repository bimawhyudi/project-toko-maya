<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kode_pesanan', 30)->nullable()->unique();
            $table->dateTime('tanggal_pesanan')->nullable();
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->decimal('ongkir', 12, 2)->default(0);
            $table->string('metode_pemenuhan', 30)->nullable();
            $table->text('alamat_pengiriman')->nullable();
            $table->string('ekspedisi', 50)->nullable();
            $table->string('no_resi', 50)->nullable();
            $table->string('metode_pembayaran', 30)->nullable();
            $table->string('status_pesanan', 30)->default('keranjang');
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();

            $table->index('status_pesanan');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
