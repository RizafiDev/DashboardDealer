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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur')->unique();
            $table->foreignId('stok_mobil_id')->constrained()->onDelete('cascade');
            $table->foreignId('karyawan_id')->constrained()->onDelete('cascade'); // Sales yang melayani
            
            // Data Pembeli
            $table->string('nama_pembeli');
            $table->string('nik_pembeli', 20);
            $table->string('telepon_pembeli', 15);
            $table->string('email_pembeli')->nullable();
            $table->text('alamat_pembeli');
            $table->date('tanggal_lahir_pembeli');
            $table->enum('jenis_kelamin_pembeli', ['L', 'P']);
            $table->string('pekerjaan_pembeli')->nullable();
            
            // Detail Pembelian
            $table->decimal('harga_jual', 15, 2);
            $table->decimal('dp', 15, 2)->default(0);
            $table->decimal('sisa_pembayaran', 15, 2)->nullable();
            $table->enum('metode_pembayaran', ['cash', 'kredit', 'leasing']);
            $table->string('bank_kredit')->nullable(); // Jika kredit
            $table->integer('tenor_bulan')->nullable(); // Jika kredit
            $table->decimal('cicilan_per_bulan', 15, 2)->nullable();
            
            // Informasi Tambahan
            $table->text('catatan')->nullable();
            $table->date('tanggal_pembelian');
            $table->enum('status', ['pending', 'dp_paid', 'completed', 'cancelled'])->default('pending');
            
            // Dokumen
            $table->json('dokumen_pembeli')->nullable(); // KTP, KK, dll
            $table->json('dokumen_kendaraan')->nullable(); // STNK, BPKB, dll
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tanggal_pembelian', 'status']);
            $table->index('karyawan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};