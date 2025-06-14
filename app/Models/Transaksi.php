<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'referensi_id',
        'referensi_type',
        'tanggal',
        'jumlah',
        'tipe',
        'deskripsi',
        'kategori',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }
}
