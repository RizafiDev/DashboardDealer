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
        Schema::create('stok_mobils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobil_id')->constrained()->cascadeOnDelete();
            $table->foreignId('varian_id')->nullable()->constrained()->nullOnDelete();
            $table->string('warna');
            $table->string('no_rangka')->unique();
            $table->string('no_mesin')->unique();
            $table->year('tahun');
            $table->enum('status', ['ready', 'sold', 'booking', 'indent'])->default('ready');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('harga_jual', 15, 2);
            $table->decimal('laba', 15, 2)->virtualAs('harga_jual - harga_beli');
            $table->date('tanggal_masuk')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_mobils');
    }
};
