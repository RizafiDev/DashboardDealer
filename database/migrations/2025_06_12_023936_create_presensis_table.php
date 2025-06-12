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
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->date('tanggal');
            $table->timestamp('jam_masuk')->nullable();
            $table->timestamp('jam_pulang')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->json('lokasi_masuk')->nullable(); // {lat, lng, alamat}
            $table->json('lokasi_pulang')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'tidak_hadir', 'sakit', 'izin', 'libur'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->decimal('jam_kerja', 4, 2)->nullable();
            $table->boolean('terlambat')->default(false);
            $table->integer('menit_terlambat')->default(0);
            $table->timestamps();
            
            $table->unique(['karyawan_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
