<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRecord extends Model
{
    protected $fillable = [
        'stok_mobil_id', 'tanggal_service', 'jenis_service', 'keterangan', 'harga_service', 'dealer'
    ];

    public function stokMobil()
    {
        return $this->belongsTo(StokMobil::class);
    }
}
