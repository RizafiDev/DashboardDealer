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
        'dp', // This can represent dp_total_dibayar_ke_dealer
        'dp_murni',
        'subsidi_dp',
        'sisa_pembayaran', // This might become less relevant if calculated dynamically
        'metode_pembayaran',
        'bank_kredit', // Old field, can be replaced by nama_leasing_bank
        'nama_leasing_bank',
        'kontak_leasing_bank',
        'tenor_bulan',
        'suku_bunga_tahunan_persen',
        'jenis_bunga',
        'biaya_provisi',
        'tipe_biaya_provisi',
        'biaya_admin_leasing',
        'nama_asuransi',
        'jenis_asuransi',
        'periode_asuransi_tahun',
        'premi_asuransi_total',
        'pembayaran_premi_asuransi',
        'cicilan_per_bulan',
        'pokok_hutang_awal',
        'total_hutang_dengan_bunga',
        'angsuran_pertama_dibayar_kapan',
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
        'dp_murni' => 'decimal:2',
        'subsidi_dp' => 'decimal:2',
        'sisa_pembayaran' => 'decimal:2',
        'suku_bunga_tahunan_persen' => 'decimal:2',
        'biaya_provisi' => 'decimal:2',
        'biaya_admin_leasing' => 'decimal:2',
        'premi_asuransi_total' => 'decimal:2',
        'cicilan_per_bulan' => 'decimal:2',
        'pokok_hutang_awal' => 'decimal:2',
        'total_hutang_dengan_bunga' => 'decimal:2',
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

    // Helper methods for payment calculation
    public function getTotalBayarAttribute()
    {
        // Use the loaded relationship if available, otherwise query it
        return $this->pembayarans->sum('jumlah');
    }

    public function getSisaPembayaranAktualAttribute()
    {
        return $this->harga_jual - $this->getTotalBayarAttribute();
    }

    public function getIsLunasAttribute(): bool
    {
        return $this->sisa_pembayaran_aktual <= 0;
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

    // Update status text to be more descriptive
    public function getStatusTextAttribute()
    {
        $statuses = [
            'booking' => 'Booking',
            'in_progress' => 'Dalam Pembayaran',
            'completed' => 'Selesai (Lunas)',
            'cancelled' => 'Dibatalkan',
            'pending' => 'Pending', // Fallback
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
        return 'Rp ' . number_format($this->sisa_pembayaran_aktual, 0, ',', '.');
    }
}
