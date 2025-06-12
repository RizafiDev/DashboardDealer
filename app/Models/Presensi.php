<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'foto_masuk',
        'foto_pulang',
        'lokasi_masuk',
        'lokasi_pulang',
        'status',
        'keterangan',
        'jam_kerja',
        'terlambat',
        'menit_terlambat',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'lokasi_masuk' => 'array',
        'lokasi_pulang' => 'array',
        'jam_kerja' => 'decimal:2',
        'terlambat' => 'boolean',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function hitungJamKerja()
    {
        if ($this->jam_masuk && $this->jam_pulang) {
            $masuk = Carbon::parse($this->jam_masuk);
            $pulang = Carbon::parse($this->jam_pulang);
            
            $totalMenit = $pulang->diffInMinutes($masuk);
            
            // Kurangi istirahat 1 jam jika kerja lebih dari 6 jam
            if ($totalMenit > 360) {
                $totalMenit -= 60;
            }
            
            return round($totalMenit / 60, 2);
        }
        
        return 0;
    }

    public function cekTerlambat()
    {
        if (!$this->jam_masuk) return false;
        
        $pengaturanKantor = PengaturanKantor::where('aktif', true)->first();
        if (!$pengaturanKantor) return false;
        
        $jamMasukKerja = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $pengaturanKantor->jam_masuk);
        $jamMasukActual = Carbon::parse($this->jam_masuk);
        
        if ($jamMasukActual->gt($jamMasukKerja->addMinutes($pengaturanKantor->toleransi_terlambat))) {
            $this->terlambat = true;
            $this->menit_terlambat = $jamMasukActual->diffInMinutes($jamMasukKerja->subMinutes($pengaturanKantor->toleransi_terlambat));
            $this->status = 'terlambat';
            return true;
        }
        
        return false;
    }
}