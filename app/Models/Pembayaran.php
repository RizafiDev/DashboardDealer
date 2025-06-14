<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'pembelian_id',
        'no_kwitansi',
        'jumlah',
        'jenis', // DP, Cicilan, Pelunasan, Tunai Lunas
        'metode_pembayaran_utama', // Tunai, Transfer, EDC Debit, EDC Kredit, E-Wallet, Cek/Giro
        'nama_bank_pengirim',
        'nomor_rekening_pengirim',
        'nama_pemilik_rekening_pengirim',
        'nama_bank_tujuan',
        'nomor_kartu_edc', // Last 4 digits
        'jenis_mesin_edc',
        'nama_ewallet',
        'nomor_referensi_transaksi', // For transfer, e-wallet, etc.
        'nomor_cek_giro',
        'tanggal_jatuh_tempo_cek_giro',
        'status_cek_giro', // Belum Cair, Cair, Ditolak
        'tanggal_bayar',
        'keterangan',
        'bukti_bayar',
        'untuk_pembayaran', // DP, Angsuran ke-X, Pelunasan, Biaya Admin
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'tanggal_jatuh_tempo_cek_giro' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class);
    }

    // Generate nomor kwitansi otomatis
    public static function generateNoKwitansi()
    {
        $prefix = 'KWT-' . date('Ymd') . '-';
        $lastKwitansi = self::where('no_kwitansi', 'like', $prefix . '%')
                           ->orderBy('no_kwitansi', 'desc')
                           ->first();
        
        if ($lastKwitansi) {
            $lastNumber = intval(substr($lastKwitansi->no_kwitansi, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Format jumlah
    public function getFormattedJumlahAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    // Get jenis text
    public function getJenisTextAttribute()
    {
        $jenis = [
            'dp' => 'Down Payment',
            'pelunasan' => 'Pelunasan',
            'cicilan' => 'Cicilan',
            'tunai_lunas' => 'Tunai Lunas',
        ];
        return $jenis[$this->jenis] ?? $this->jenis;
    }

    // Get metode text
    public function getMetodePembayaranUtamaTextAttribute()
    {
        $metode = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'edc_debit' => 'EDC - Kartu Debit',
            'edc_kredit' => 'EDC - Kartu Kredit',
            'ewallet' => 'E-Wallet',
            'cheque' => 'Cek/Giro',
            'setoran_leasing' => 'Setoran Leasing',
        ];
        return $metode[$this->metode_pembayaran_utama] ?? $this->metode_pembayaran_utama;
    }
}
