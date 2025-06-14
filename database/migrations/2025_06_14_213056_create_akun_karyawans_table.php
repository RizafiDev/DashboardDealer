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
        Schema::create('akun_karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // Tambahkan kolom akun_karyawan_id pada tabel karyawans
        Schema::table('karyawans', function (Blueprint $table) {
            $table->unsignedBigInteger('akun_karyawan_id')->nullable()->unique()->after('id');
            $table->foreign('akun_karyawan_id')->references('id')->on('akun_karyawans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropForeign(['akun_karyawan_id']);
            $table->dropColumn('akun_karyawan_id');
        });
        Schema::dropIfExists('akun_karyawans');
    }
};
