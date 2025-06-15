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
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur')->unique();
            $table->foreignId('stok_mobil_id')->constrained('stok_mobils')->onDelete('restrict');
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->onDelete('set null'); // Sales

            // Data Pembeli
            $table->string('nama_pembeli');
            $table->string('nik_pembeli')->nullable();
            $table->string('telepon_pembeli')->nullable();
            $table->string('email_pembeli')->nullable();
            $table->text('alamat_pembeli')->nullable();
            $table->date('tanggal_lahir_pembeli')->nullable();
            $table->enum('jenis_kelamin_pembeli', ['L', 'P'])->comment('L: Laki-laki, P: Perempuan');
            $table->string('pekerjaan_pembeli')->nullable();

            // Detail Harga & Pembayaran Awal
            $table->decimal('harga_jual', 15, 2); // Harga OTR atau harga kesepakatan
            $table->decimal('dp', 15, 2)->default(0)->comment('Total DP yang dibayar ke dealer');
            $table->decimal('dp_murni', 15, 2)->nullable()->comment('DP murni dari customer (jika kredit)');
            $table->decimal('subsidi_dp', 15, 2)->nullable()->comment('Subsidi DP dari leasing/dealer');

            // Metode Pembayaran Utama
            $table->enum('metode_pembayaran', [
                'tunai_lunas',      // Sesuai dengan form
                'tunai_bertahap',   // Sesuai dengan form
                'kredit_bank',      // Sesuai dengan form
                'leasing'           // Sesuai dengan form
            ])->default('tunai_lunas'); // Default disesuaikan

            // Detail Kredit/Leasing (jika metode_pembayaran = kredit_*)
            $table->string('nama_leasing_bank')->nullable();
            $table->string('kontak_leasing_bank')->nullable();
            $table->integer('tenor_bulan')->nullable();
            $table->decimal('suku_bunga_tahunan_persen', 5, 2)->nullable();
            $table->enum('jenis_bunga', ['flat', 'efektif', 'anuitas'])->nullable();
            $table->decimal('biaya_provisi', 15, 2)->nullable();
            $table->enum('tipe_biaya_provisi', ['persen', 'nominal'])->nullable();
            $table->decimal('biaya_admin_leasing', 15, 2)->nullable();
            $table->decimal('cicilan_per_bulan', 15, 2)->nullable();
            $table->decimal('pokok_hutang_awal', 15, 2)->nullable();
            $table->decimal('total_hutang_dengan_bunga', 15, 2)->nullable();
            $table->enum('angsuran_pertama_dibayar_kapan', ['adm', 'addb'])->nullable()->comment('ADM: Angsuran Di Muka, ADDB: Angsuran Di Belakang');

            // Detail Asuransi (jika ada, biasanya terkait kredit)
            $table->string('nama_asuransi')->nullable();
            $table->enum('jenis_asuransi', ['all_risk', 'tlo', 'kombinasi'])->nullable();
            $table->integer('periode_asuransi_tahun')->nullable();
            $table->decimal('premi_asuransi_total', 15, 2)->nullable();
            $table->enum('pembayaran_premi_asuransi', ['cash', 'include_dp', 'include_cicilan'])->nullable();

            // Lain-lain
            $table->text('catatan')->nullable();
            $table->date('tanggal_pembelian');
            $table->enum('status', ['booking', 'in_progress', 'completed', 'cancelled', 'pending'])
                ->default('pending')->comment('Status keseluruhan pembelian');

            // Dokumen (disimpan sebagai path atau JSON berisi path)
            $table->json('dokumen_pembeli')->nullable(); // e.g., KTP, KK
            $table->json('dokumen_kendaraan')->nullable(); // e.g., STNK sementara, BPKB (jika sudah ada)

            $table->timestamps();
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
