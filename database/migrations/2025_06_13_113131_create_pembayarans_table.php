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
            $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
            $table->string('no_kwitansi')->unique();
            $table->decimal('jumlah', 15, 2);

            $table->enum('jenis', ['dp', 'cicilan', 'pelunasan', 'tunai_lunas', 'biaya_lain'])
                ->default('dp')->comment('Jenis pembayaran: DP, Cicilan, Pelunasan, dll.');
            $table->string('untuk_pembayaran')->nullable()->comment('Deskripsi lebih detail, mis: Angsuran ke-1, Biaya Admin Leasing');

            // Metode Pembayaran Utama
            $table->enum('metode_pembayaran_utama', [
                'cash',
                'transfer',
                'edc_debit',
                'edc_kredit',
                'ewallet',
                'cheque',
                'setoran_leasing'
            ])->comment('Metode pembayaran yang digunakan');

            // Detail untuk Transfer Bank
            $table->string('nama_bank_pengirim')->nullable();
            $table->string('nomor_rekening_pengirim')->nullable();
            $table->string('nama_pemilik_rekening_pengirim')->nullable();
            $table->string('nama_bank_tujuan')->nullable();
            $table->string('nomor_referensi_transaksi')->nullable()->comment('Untuk transfer, e-wallet, dll.');

            // Detail untuk EDC
            $table->string('nomor_kartu_edc')->nullable()->comment('Misal: 4 digit terakhir');
            $table->string('jenis_mesin_edc')->nullable(); // Nama Bank EDC

            // Detail untuk E-Wallet
            $table->string('nama_ewallet')->nullable(); // Misal: GoPay, OVO

            // Detail untuk Cek/Giro
            $table->string('nomor_cek_giro')->nullable();
            $table->date('tanggal_jatuh_tempo_cek_giro')->nullable();
            $table->enum('status_cek_giro', ['belum_cair', 'cair', 'ditolak'])->nullable();

            $table->date('tanggal_bayar');
            $table->text('keterangan')->nullable();
            $table->string('bukti_bayar')->nullable(); // Path ke file bukti bayar

            $table->timestamps();
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
