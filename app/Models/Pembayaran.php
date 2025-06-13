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
        'jenis',
        'metode',
        'bank',
        'no_referensi',
        'tanggal_bayar',
        'keterangan',
        'bukti_bayar',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
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
        ];

        return $jenis[$this->jenis] ?? 'Tidak Diketahui';
    }

    // Get metode text
    public function getMetodeTextAttribute()
    {
        $metode = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer Bank',
            'kartu_kredit' => 'Kartu Kredit',
            'kartu_debit' => 'Kartu Debit',
        ];

        return $metode[$this->metode] ?? 'Tidak Diketahui';
    }
}