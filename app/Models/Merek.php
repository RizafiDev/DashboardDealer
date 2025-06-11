<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merek extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'logo',
        'negara_asal',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function mobils(): HasMany
    {
        return $this->hasMany(Mobil::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}