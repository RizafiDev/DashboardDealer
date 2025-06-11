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
        Schema::create('mereks', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('logo')->nullable(); // Logo bisa optional
            $table->string('negara_asal')->nullable(); // Negara asal merek
            $table->boolean('is_active')->default(true); // Status aktif merek
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mereks');
    }
};
