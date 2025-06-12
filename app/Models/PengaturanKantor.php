<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanKantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kantor',
        'alamat_kantor',
        'latitude',
        'longitude',
        'radius_meter',
        'jam_masuk',
        'jam_pulang',
        'toleransi_terlambat',
        'aktif',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
        'aktif' => 'boolean',
    ];
}