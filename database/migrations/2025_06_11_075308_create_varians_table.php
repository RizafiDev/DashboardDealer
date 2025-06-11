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
        Schema::create('varians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobil_id')->constrained('mobils')->onDelete('cascade')->required();
            $table->string('nama')->unique()->required();
            $table->text('deskripsi')->nullable();
            // Spesifikasi Mesin
            $table->string('jenis_mesin')->nullable(); // Bensin, Diesel, Hybrid, Listrik
            $table->integer('kapasitas_mesin')->nullable(); // dalam CC
            $table->string('transmisi')->nullable(); // Manual, Automatic, CVT
            $table->integer('tenaga_hp')->nullable(); // Horsepower
            $table->integer('torsi_nm')->nullable(); // Newton Meter
            $table->string('bahan_bakar')->required(); // Bensin, Solar, Listrik, Hybrid
            // dimensi
            $table->integer('panjang_mm')->nullable();
            $table->integer('lebar_mm')->nullable();
            $table->integer('tinggi_mm')->nullable();
            $table->integer('berat_kg')->nullable();
            $table->integer('wheelbase_mm')->nullable();
            $table->integer('ground_clearance_mm')->nullable();
            // Fitur Keselamatan
            $table->boolean('airbag')->default(false);
            $table->integer('jumlah_airbag')->nullable();
            // Fitur Keselamatan Tambahan
            $table->boolean('kamera_belakang')->default(false); // Anti-lock Braking System
            // Fitur Kenyamanan
            $table->boolean('ac')->default(false);
            $table->boolean('power_steering')->default(false);
            $table->boolean('power_window')->default(false);
            $table->boolean('central_lock')->default(false);
            $table->boolean('audio_system')->default(false);
            $table->boolean('bluetooth')->default(false);
            $table->boolean('usb_port')->default(false);
            $table->string('jenis_velg')->nullable(); // Alloy, Steel
            $table->string('ukuran_ban')->nullable(); // 185/65 R15
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('varians');
    }
};
