<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokMobil extends Model
{
    protected $fillable = [
        'mobil_id', 'varian_id', 'warna', 'no_rangka', 'no_mesin', 'tahun',
        'status', 'harga_beli', 'harga_jual', 'tanggal_masuk', 'lokasi', 'keterangan'
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

    // Accessor untuk laba jika DB tidak support virtual column
    public function getLabaAttribute()
    {
        $totalService = $this->serviceRecords()->sum('harga_service');
        return ($this->harga_jual ?? 0) - ($this->harga_beli ?? 0) - $totalService;
    }
}
