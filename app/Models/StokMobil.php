<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMobil extends Model
{
    protected $fillable = [
        'mobil_id',
        'varian_id',
        'warna',
        'no_rangka',
        'no_mesin',
        'tahun',
        'status',
        'harga_beli',
        'harga_jual',
        'tanggal_masuk',
        'lokasi',
        'keterangan',
        'kelengkapan_mobil',
        'fitur_override' // Tambahkan field baru
    ];

    protected $casts = [
        'fitur_override' => 'array', // Cast JSON ke array
    ];

    public function mobil()
    {
        return $this->belongsTo(Mobil::class);
    }

    public function varian()
    {
        return $this->belongsTo(Varian::class);
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class);
    }

    // Accessor untuk laba bersih (setelah dikurangi biaya service)
    public function getLabaAttribute()
    {
        // Jika kolom 'laba' ada di $attributes (dari virtual column DB), gunakan itu sebagai basis
        // Jika tidak, hitung dari harga jual - harga beli
        $labaDasar = $this->attributes['laba'] ?? (($this->harga_jual ?? 0) - ($this->harga_beli ?? 0));
        $totalService = $this->serviceRecords()->sum('harga_service');
        return $labaDasar - $totalService;
    }
}
