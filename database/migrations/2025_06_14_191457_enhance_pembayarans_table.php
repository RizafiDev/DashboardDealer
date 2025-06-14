<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // Rename existing fields
            if (Schema::hasColumn('pembayarans', 'metode')) {
                $table->renameColumn('metode', 'metode_pembayaran_utama');
            }
            if (Schema::hasColumn('pembayarans', 'bank')) {
                $table->dropColumn('bank'); // Will be replaced by nama_bank_pengirim / nama_bank_tujuan
            }
            if (Schema::hasColumn('pembayarans', 'no_referensi')) {
                $table->renameColumn('no_referensi', 'nomor_referensi_transaksi');
            }

            // Add new fields
            $table->string('nama_bank_pengirim')->nullable()->after('metode_pembayaran_utama');
            $table->string('nomor_rekening_pengirim')->nullable()->after('nama_bank_pengirim');
            $table->string('nama_pemilik_rekening_pengirim')->nullable()->after('nomor_rekening_pengirim');
            $table->string('nama_bank_tujuan')->nullable()->after('nama_pemilik_rekening_pengirim');
            
            $table->string('nomor_kartu_edc')->nullable()->comment('Last 4 digits or full if PCI compliant')->after('nama_bank_tujuan');
            $table->string('jenis_mesin_edc')->nullable()->after('nomor_kartu_edc');
            
            $table->string('nama_ewallet')->nullable()->after('jenis_mesin_edc');
            // nomor_referensi_transaksi can be used for e-wallet reference

            $table->string('nomor_cek_giro')->nullable()->after('nama_ewallet');
            $table->date('tanggal_jatuh_tempo_cek_giro')->nullable()->after('nomor_cek_giro');
            $table->string('status_cek_giro')->nullable()->comment('Belum Cair, Cair, Ditolak')->after('tanggal_jatuh_tempo_cek_giro');

            $table->string('untuk_pembayaran')->nullable()->comment('DP, Angsuran ke-X, Pelunasan')->after('status_cek_giro');

            // Modify 'jenis' if it's an ENUM to include 'tunai_lunas'
            // If it's VARCHAR, it's fine. If ENUM, you might need a DB-specific way or use Doctrine DBAL
            // For simplicity, assuming it's VARCHAR or you'll handle ENUM modification.
            // $table->string('jenis')->change(); // Example if it was ENUM and you change to VARCHAR
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            // Revert renames
            if (Schema::hasColumn('pembayarans', 'metode_pembayaran_utama')) {
                $table->renameColumn('metode_pembayaran_utama', 'metode');
            }
            if (Schema::hasColumn('pembayarans', 'nomor_referensi_transaksi')) {
                $table->renameColumn('nomor_referensi_transaksi', 'no_referensi');
            }
             // Add back 'bank' column if it was dropped
            $table->string('bank')->nullable()->after('metode');


            $table->dropColumn([
                'nama_bank_pengirim', 'nomor_rekening_pengirim', 'nama_pemilik_rekening_pengirim',
                'nama_bank_tujuan', 'nomor_kartu_edc', 'jenis_mesin_edc', 'nama_ewallet',
                'nomor_cek_giro', 'tanggal_jatuh_tempo_cek_giro', 'status_cek_giro',
                'untuk_pembayaran'
            ]);
        });
    }
};
