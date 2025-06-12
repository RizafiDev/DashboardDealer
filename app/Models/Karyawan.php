<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'nama',
        'email',
        'telepon',
        'jabatan',
        'departemen',
        'gaji_pokok',
        'tanggal_masuk',
        'status',
        'alamat',
        'foto',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'gaji_pokok' => 'decimal:2',
    ];

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    public function pengajuanCutis(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class);
    }

    public function saldoCuti(): HasOne
    {
        return $this->hasOne(SaldoCuti::class)->where('tahun', date('Y'));
    }

    public function saldoCutis(): HasMany
    {
        return $this->hasMany(SaldoCuti::class);
    }

    // Helper methods
    public function presensiHariIni()
    {
        return $this->presensis()->whereDate('tanggal', today())->first();
    }

    public function rekapBulanan($bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? date('m');
        $tahun = $tahun ?? date('Y');
        
        return $this->presensis()
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->selectRaw('
                COUNT(*) as total_hari,
                SUM(CASE WHEN status = "hadir" THEN 1 ELSE 0 END) as hari_hadir,
                SUM(CASE WHEN status = "terlambat" THEN 1 ELSE 0 END) as hari_terlambat,
                SUM(CASE WHEN status = "tidak_hadir" THEN 1 ELSE 0 END) as hari_tidak_hadir,
                SUM(CASE WHEN status IN ("sakit", "izin") THEN 1 ELSE 0 END) as hari_cuti,
                SUM(jam_kerja) as total_jam_kerja
            ')
            ->first();
    }
}