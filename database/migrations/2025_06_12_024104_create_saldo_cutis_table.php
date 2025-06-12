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
        Schema::create('saldo_cutis', function (Blueprint $table) {
           $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->year('tahun');
            $table->integer('cuti_tahunan_total')->default(12);
            $table->integer('cuti_tahunan_terpakai')->default(0);
            $table->integer('cuti_sakit_total')->default(12);
            $table->integer('cuti_sakit_terpakai')->default(0);
            $table->timestamps();
            
            $table->unique(['karyawan_id', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_cutis');
    }
};
