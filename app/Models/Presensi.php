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
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return 0;
        }

        // Pastikan menggunakan Carbon untuk parsing yang konsisten
        $masuk = $this->jam_masuk instanceof Carbon ? $this->jam_masuk : Carbon::parse($this->jam_masuk);
        $pulang = $this->jam_pulang instanceof Carbon ? $this->jam_pulang : Carbon::parse($this->jam_pulang);

        // Debug logging untuk melihat nilai aktual
        \Log::info('Perhitungan jam kerja:', [
            'jam_masuk' => $masuk->toDateTimeString(),
            'jam_pulang' => $pulang->toDateTimeString(),
            'jam_masuk_timestamp' => $masuk->timestamp,
            'jam_pulang_timestamp' => $pulang->timestamp
        ]);

        // Jika jam pulang lebih kecil dari jam masuk, berarti melewati tengah malam
        if ($pulang->lt($masuk)) {
            $pulang->addDay();
        }

        // Hitung selisih dalam menit
        $diffInMinutes = $pulang->diffInMinutes($masuk);
        
        // Debug logging
        \Log::info('Selisih waktu:', [
            'diff_in_minutes' => $diffInMinutes,
            'before_round' => $diffInMinutes / 60,
            'after_round' => round($diffInMinutes / 60, 2)
        ]);

        // Konversi ke jam dengan presisi 2 desimal
        $jamKerja = round($diffInMinutes / 60, 2);
        
        // Pastikan jam kerja tidak negatif
        return max(0, $jamKerja);
    }

    /**
     * Method alternatif untuk perhitungan jam kerja dengan presisi lebih tinggi
     */
    public function hitungJamKerjaPrecise()
    {
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return 0;
        }

        $masuk = $this->jam_masuk instanceof Carbon ? $this->jam_masuk : Carbon::parse($this->jam_masuk);
        $pulang = $this->jam_pulang instanceof Carbon ? $this->jam_pulang : Carbon::parse($this->jam_pulang);

        // Jika jam pulang lebih kecil dari jam masuk, berarti melewati tengah malam
        if ($pulang->lt($masuk)) {
            $pulang->addDay();
        }

        // Hitung selisih dalam detik untuk presisi tinggi
        $diffInSeconds = $pulang->diffInSeconds($masuk);
        
        // Konversi ke jam (3600 detik = 1 jam)
        $jamKerja = round($diffInSeconds / 3600, 2);
        
        return max(0, $jamKerja);
    }

    /**
     * Method untuk mendapatkan jam kerja dalam format yang mudah dibaca
     */
    public function getJamKerjaFormatted()
    {
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return '0 jam 0 menit';
        }

        $masuk = $this->jam_masuk instanceof Carbon ? $this->jam_masuk : Carbon::parse($this->jam_masuk);
        $pulang = $this->jam_pulang instanceof Carbon ? $this->jam_pulang : Carbon::parse($this->jam_pulang);

        if ($pulang->lt($masuk)) {
            $pulang->addDay();
        }

        $diffInMinutes = $pulang->diffInMinutes($masuk);
        $jam = intval($diffInMinutes / 60);
        $menit = $diffInMinutes % 60;

        return "{$jam} jam {$menit} menit";
    }

    /**
     * Method untuk mendapatkan total menit kerja
     */
    public function getTotalMenitKerja()
    {
        if (!$this->jam_masuk || !$this->jam_pulang) {
            return 0;
        }

        $masuk = $this->jam_masuk instanceof Carbon ? $this->jam_masuk : Carbon::parse($this->jam_masuk);
        $pulang = $this->jam_pulang instanceof Carbon ? $this->jam_pulang : Carbon::parse($this->jam_pulang);

        if ($pulang->lt($masuk)) {
            $pulang->addDay();
        }

        return $pulang->diffInMinutes($masuk);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($presensi) {
            if ($presensi->jam_masuk && $presensi->jam_pulang) {
                // Gunakan method yang lebih presisi
                $presensi->jam_kerja = $presensi->hitungJamKerjaPrecise();
                
                // Log untuk debugging
                \Log::info('Saving presensi with jam_kerja:', [
                    'karyawan_id' => $presensi->karyawan_id,
                    'tanggal' => $presensi->tanggal,
                    'jam_masuk' => $presensi->jam_masuk,
                    'jam_pulang' => $presensi->jam_pulang,
                    'jam_kerja' => $presensi->jam_kerja,
                    'total_menit' => $presensi->getTotalMenitKerja()
                ]);
            }
        });
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