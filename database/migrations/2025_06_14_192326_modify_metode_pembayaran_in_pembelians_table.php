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
        Schema::table('pembelians', function (Blueprint $table) {
            // This makes the column flexible enough for 'cash_bertahap', 'kredit_bank', etc.
            $table->string('metode_pembayaran', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            // Revert to a generic string if needed, or your previous definition.
            $table->string('metode_pembayaran', 20)->nullable()->change();
        });
    }
};
