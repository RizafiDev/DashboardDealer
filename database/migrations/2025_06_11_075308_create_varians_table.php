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
            $table->foreignId('mobil_id')->constrained('mobils')->onDelete('cascade');
            $table->string('nama'); // Nama varian (G, V, Q, dll)
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_otr', 15, 2)->nullable(); // Harga OTR saat baru (untuk referensi)
            
            // Spesifikasi Mesin
            $table->enum('jenis_mesin', ['bensin', 'diesel', 'hybrid', 'listrik'])->nullable();
            $table->integer('kapasitas_mesin')->nullable(); // dalam CC
            $table->enum('transmisi', ['manual', 'automatic', 'cvt', 'amt'])->nullable();
            $table->integer('tenaga_hp')->nullable();
            $table->integer('torsi_nm')->nullable();
            $table->string('bahan_bakar'); // Primary fuel type
            $table->decimal('konsumsi_bbm_kota', 5, 2)->nullable(); // km/liter di kota
            $table->decimal('konsumsi_bbm_luar_kota', 5, 2)->nullable(); // km/liter luar kota
            
            // Dimensi
            $table->integer('panjang_mm')->nullable();
            $table->integer('lebar_mm')->nullable();
            $table->integer('tinggi_mm')->nullable();
            $table->integer('berat_kosong_kg')->nullable();
            $table->integer('berat_kotor_kg')->nullable();
            $table->integer('wheelbase_mm')->nullable();
            $table->integer('ground_clearance_mm')->nullable();
            $table->integer('kapasitas_bagasi_liter')->nullable();
            $table->integer('kapasitas_tangki_liter')->nullable();
            
            // Fitur Keselamatan
            $table->boolean('airbag')->default(false);
            $table->integer('jumlah_airbag')->nullable();
            $table->boolean('abs')->default(false);
            $table->boolean('ebd')->default(false); // Electronic Brake Distribution
            $table->boolean('ba')->default(false); // Brake Assist
            $table->boolean('esc')->default(false); // Electronic Stability Control
            $table->boolean('hill_start_assist')->default(false);
            $table->boolean('kamera_belakang')->default(false);
            $table->boolean('sensor_parkir')->default(false);
            
            // Fitur Kenyamanan
            $table->boolean('ac')->default(false);
            $table->boolean('ac_double_blower')->default(false);
            $table->boolean('power_steering')->default(false);
            $table->boolean('power_window')->default(false);
            $table->boolean('central_lock')->default(false);
            $table->boolean('audio_system')->default(false);
            $table->boolean('bluetooth')->default(false);
            $table->boolean('usb_port')->default(false);
            $table->boolean('wireless_charging')->default(false);
            $table->boolean('sunroof')->default(false);
            $table->boolean('cruise_control')->default(false);
            $table->boolean('keyless_entry')->default(false);
            $table->boolean('push_start_button')->default(false);
            
            // Velg dan Ban
            $table->enum('jenis_velg', ['alloy', 'steel'])->nullable();
            $table->string('ukuran_ban')->nullable(); // 185/65 R15
            
            // Status dan metadata
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index untuk query yang sering digunakan
            $table->index(['mobil_id', 'is_active']);
            $table->index(['jenis_mesin', 'transmisi']);
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
