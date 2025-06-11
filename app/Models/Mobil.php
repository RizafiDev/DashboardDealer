<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mobil extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'model',
        'tahun_mulai',
        'tahun_akhir',
        'merek_id',
        'kategori_id',
        'kapasitas_penumpang',
        'status',
        'deskripsi',
    ];

    protected $casts = [
        'tahun_mulai' => 'integer',
        'tahun_akhir' => 'integer',
    ];

    public function merek(): BelongsTo
    {
        return $this->belongsTo(Merek::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function varians(): HasMany
    {
        return $this->hasMany(Varian::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(MobilFoto::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getMainFotoAttribute()
    {
        return $this->fotos()->where('foto_type', 'main')->first();
    }

    public function getThumbnailAttribute()
    {
        return $this->fotos()->where('foto_type', 'thumbnail')->first();
    }
}