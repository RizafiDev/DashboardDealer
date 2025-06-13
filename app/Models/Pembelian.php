<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_faktur',
        'stok_mobil_id',
        'karyawan_id',
        'nama_pembeli',
        'nik_pembeli',
        'telepon_pembeli',
        'email_pembeli',
        'alamat_pembeli',
        'tanggal_lahir_pembeli',
        'jenis_kelamin_pembeli',
        'pekerjaan_pembeli',
        'harga_jual',
        'dp',
        'sisa_pembayaran',
        'metode_pembayaran',
        'bank_kredit',
        'tenor_bulan',
        'cicilan_per_bulan',
        'catatan',
        'tanggal_pembelian',
        'status',
        'dokumen_pembeli',
        'dokumen_kendaraan',
    ];

    protected $casts = [
        'tanggal_lahir_pembeli' => 'date',
        'tanggal_pembelian' => 'date',
        'harga_jual' => 'decimal:2',
        'dp' => 'decimal:2',
        'sisa_pembayaran' => 'decimal:2',
        'cicilan_per_bulan' => 'decimal:2',
        'dokumen_pembeli' => 'array',
        'dokumen_kendaraan' => 'array',
    ];

    public function stokMobil(): BelongsTo
    {
        return $this->belongsTo(StokMobil::class);
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    // Accessor untuk mendapatkan info mobil
    public function getMobilAttribute()
    {
        return $this->stokMobil->mobil ?? null;
    }

    public function getVarianAttribute() 
    {
        return $this->stokMobil->varian ?? null;
    }

    // Generate nomor faktur otomatis
    public static function generateNoFaktur()
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $lastInvoice = self::where('no_faktur', 'like', $prefix . '%')
                          ->orderBy('no_faktur', 'desc')
                          ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->no_faktur, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Scope untuk filter
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByKaryawan($query, $karyawanId)
    {
        return $query->where('karyawan_id', $karyawanId);
    }

    public function scopeByPeriod($query, $start, $end)
    {
        return $query->whereBetween('tanggal_pembelian', [$start, $end]);
    }

    // Helper methods
    public function getTotalBayarAttribute()
    {
        return $this->pembayarans()->sum('jumlah');
    }

    public function getSisaPembayaranActualAttribute()
    {
        return $this->harga_jual - $this->total_bayar;
    }

    public function isLunas()
    {
        return $this->sisa_pembayaran_actual <= 0;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'dp_paid' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'dp_paid' => 'DP Dibayar',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        return $statuses[$this->status] ?? 'Tidak Diketahui';
    }

    // Format harga
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }

    public function getFormattedDpAttribute()
    {
        return 'Rp ' . number_format($this->dp, 0, ',', '.');
    }

    public function getFormattedSisaPembayaranAttribute()
    {
        return 'Rp ' . number_format($this->sisa_pembayaran_actual, 0, ',', '.');
    }
}