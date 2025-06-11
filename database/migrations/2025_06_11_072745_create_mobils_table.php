<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mobils', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique()->required();
            $table->year('tahun')->required();
            $table->foreignId('merek_id')->constrained('mereks')->required();
            // Spesifikasi Kendaraan
            $table->integer('kapasitas_penumpang')->required();
            $table->foreignId('kategori_id')->constrained('kategoris')->required(); // Kategori seperti SUV, Sedan, Hatchback, dll;
            $table->timestamps();
        });
        // Schema untuk menyimpan multiple foto
        Schema::create('mobil_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobil_id')->constrained('mobils')->onDelete('cascade');
            $table->string('foto_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobils');
    }
};
