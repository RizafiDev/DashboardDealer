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
            // Change to VARCHAR(50) to accommodate 'booking', 'in_progress', 'completed', 'cancelled' etc.
            // The default('booking') ensures new records get this status if not otherwise set.
            $table->string('status', 50)->default('booking')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            // Revert to a previous state if necessary. 
            // This is a general revert; adjust if your original schema was different.
            // For example, if it was an ENUM or a shorter VARCHAR.
            $table->string('status', 20)->nullable()->change(); // Example revert
        });
    }
};