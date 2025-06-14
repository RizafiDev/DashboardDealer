<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            // Leasing/Bank Info
            $table->string('nama_leasing_bank')->nullable()->after('bank_kredit');
            $table->string('kontak_leasing_bank')->nullable()->after('nama_leasing_bank');

            // Financing Terms
            $table->decimal('suku_bunga_tahunan_persen', 5, 2)->nullable()->after('tenor_bulan');
            $table->string('jenis_bunga')->nullable()->comment('Flat, Efektif, Anuitas')->after('suku_bunga_tahunan_persen');
            $table->decimal('biaya_provisi', 15, 2)->nullable()->after('jenis_bunga'); // Can be nominal or calculated
            $table->string('tipe_biaya_provisi')->nullable()->comment('persen, nominal')->after('biaya_provisi');
            $table->decimal('biaya_admin_leasing', 15, 2)->nullable()->after('tipe_biaya_provisi');
            
            // Insurance Details
            $table->string('nama_asuransi')->nullable()->after('biaya_admin_leasing');
            $table->string('jenis_asuransi')->nullable()->comment('All Risk, TLO')->after('nama_asuransi');
            $table->integer('periode_asuransi_tahun')->nullable()->after('jenis_asuransi');
            $table->decimal('premi_asuransi_total', 15, 2)->nullable()->after('periode_asuransi_tahun');
            $table->string('pembayaran_premi_asuransi')->nullable()->comment('Termasuk angsuran, Bayar di muka')->after('premi_asuransi_total');

            // DP Breakdown (dp field already exists, can be dp_total_dibayar_ke_dealer)
            $table->decimal('dp_murni', 15, 2)->nullable()->after('dp');
            $table->decimal('subsidi_dp', 15, 2)->nullable()->after('dp_murni');
            // 'dp' field can represent dp_total_dibayar_ke_dealer

            // Loan Details
            $table->decimal('pokok_hutang_awal', 15, 2)->nullable()->after('cicilan_per_bulan');
            $table->decimal('total_hutang_dengan_bunga', 15, 2)->nullable()->after('pokok_hutang_awal');
            $table->string('angsuran_pertama_dibayar_kapan')->nullable()->comment('ADDM, ADDB')->after('total_hutang_dengan_bunga');
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn([
                'nama_leasing_bank', 'kontak_leasing_bank', 'suku_bunga_tahunan_persen', 'jenis_bunga',
                'biaya_provisi', 'tipe_biaya_provisi', 'biaya_admin_leasing', 'nama_asuransi', 'jenis_asuransi',
                'periode_asuransi_tahun', 'premi_asuransi_total', 'pembayaran_premi_asuransi',
                'dp_murni', 'subsidi_dp', 'pokok_hutang_awal', 'total_hutang_dengan_bunga',
                'angsuran_pertama_dibayar_kapan'
            ]);
        });
    }
};
