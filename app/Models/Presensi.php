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

        // Pastikan tanggal dalam format string Y-m-d
        if ($this->tanggal instanceof Carbon) {
            $tanggalString = $this->tanggal->format('Y-m-d');
        } else {
            // Jika tanggal sudah string, parse dulu untuk memastikan format yang benar
            $tanggalString = Carbon::parse($this->tanggal)->format('Y-m-d');
        }

        // Pastikan jam_masuk dari pengaturan kantor dalam format HH:MM:SS
        $jamMasukKantorString = $pengaturanKantor->jam_masuk;
        
        // Jika jam_masuk sudah termasuk tanggal, ambil hanya bagian waktu
        if (strlen($jamMasukKantorString) > 8) {
            $jamMasukKantorString = Carbon::parse($jamMasukKantorString)->format('H:i:s');
        }

        // Gabungkan tanggal dengan jam masuk kantor
        $jamMasukKerja = Carbon::parse($tanggalString . ' ' . $jamMasukKantorString);
        $jamMasukActual = Carbon::parse($this->jam_masuk);

        // Tambahkan toleransi terlambat ke jam masuk kerja
        $batasTerlambat = $jamMasukKerja->copy()->addMinutes($pengaturanKantor->toleransi_terlambat);

        if ($jamMasukActual->gt($batasTerlambat)) {
            $this->terlambat = true;
            $this->menit_terlambat = $jamMasukActual->diffInMinutes($jamMasukKerja);
            $this->status = 'terlambat';
            return true;
        }

        $this->terlambat = false;
        $this->menit_terlambat = 0;
        $this->status = 'hadir';
        return false;
    }
}