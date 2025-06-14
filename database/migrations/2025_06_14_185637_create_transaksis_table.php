<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->morphs('referensi'); // For Pembayaran, etc.
            $table->date('tanggal');
            $table->decimal('jumlah', 15, 2);
            $table->enum('tipe', ['income', 'expense']);
            $table->string('kategori')->nullable(); // e.g., 'Penjualan Mobil', 'Service'
            $table->text('deskripsi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
