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
            $table->string('nama');
            $table->string('model')->nullable(); // Model spesifik (contoh: Avanza G, Innova V)
            $table->year('tahun_mulai'); // Tahun mulai produksi
            $table->year('tahun_akhir')->nullable(); // Tahun akhir produksi (null jika masih diproduksi)
            $table->foreignId('merek_id')->constrained('mereks')->onDelete('restrict');
            $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('restrict');
            $table->integer('kapasitas_penumpang');
            $table->string('status')->default('active'); // active, discontinued
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            
            // Index untuk pencarian yang sering dilakukan
            $table->index(['merek_id', 'tahun_mulai']);
            $table->index(['kategori_id', 'status']);
        });
        Schema::create('mobil_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobil_id')->constrained('mobils')->onDelete('cascade');
            $table->string('foto_path');
            $table->string('foto_type')->default('gallery'); // gallery, thumbnail, main
            $table->integer('urutan')->default(0); // Untuk sorting foto
            $table->string('alt_text')->nullable(); // Alt text untuk accessibility
            $table->timestamps();
            
            $table->index(['mobil_id', 'foto_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobil_fotos');
        Schema::dropIfExists('mobils');
    }
};
