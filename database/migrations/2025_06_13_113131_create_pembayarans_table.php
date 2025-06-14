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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained()->onDelete('cascade');
            $table->string('no_kwitansi')->unique();
            $table->decimal('jumlah', 15, 2);
            $table->enum('jenis', ['dp', 'pelunasan', 'cicilan', 'tunai_lunas']);
            $table->enum('metode', [
                'cash',
                'transfer',
                'edc_debit',
                'edc_kredit',
                'ewallet',
                'cheque',
                'setoran_leasing'
            ]);
            $table->string('bank')->nullable();
            $table->string('no_referensi')->nullable(); // No. transfer, no. kartu, dll
            $table->date('tanggal_bayar');
            $table->text('keterangan')->nullable();
            $table->string('bukti_bayar')->nullable(); // File path
            $table->timestamps();

            $table->index('pembelian_id');
            $table->index('tanggal_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};