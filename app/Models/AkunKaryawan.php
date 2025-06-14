<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AkunKaryawan extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'username',
        'email',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    public function karyawan()
    {
        return $this->hasOne(Karyawan::class, 'akun_karyawan_id');
    }

    // Helper method untuk mendapatkan data karyawan
    public function getKaryawanData()
    {
        return $this->karyawan()->first();
    }
}
