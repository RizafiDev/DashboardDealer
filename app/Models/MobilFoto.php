<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MobilFoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobil_id',
        'foto_path',
        'foto_type',
        'urutan',
        'alt_text',
    ];

    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class);
    }

    public function getFotoUrlAttribute()
    {
        return Storage::url($this->foto_path);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($foto) {
            if (Storage::exists($foto->foto_path)) {
                Storage::delete($foto->foto_path);
            }
        });
    }
}
