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
        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_mobil_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal_service');
            $table->string('jenis_service');
            $table->text('keterangan')->nullable();
            $table->decimal('harga_service', 15, 2);
            $table->string('dealer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_records');
    }
};
