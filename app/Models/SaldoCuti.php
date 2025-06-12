<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoCuti extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'tahun',
        'cuti_tahunan_total',
        'cuti_tahunan_terpakai',
        'cuti_sakit_total',
        'cuti_sakit_terpakai',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function sisaCutiTahunan()
    {
        return $this->cuti_tahunan_total - $this->cuti_tahunan_terpakai;
    }

    public function sisaCutiSakit()
    {
        return $this->cuti_sakit_total - $this->cuti_sakit_terpakai;
    }
}