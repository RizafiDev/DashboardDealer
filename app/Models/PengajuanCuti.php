<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PengajuanCuti extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_hari',
        'alasan',
        'lampiran',
        'status',
        'disetujui_oleh',
        'tanggal_disetujui',
        'alasan_penolakan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_disetujui' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function hitungJumlahHari()
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $mulai = Carbon::parse($this->tanggal_mulai);
            $selesai = Carbon::parse($this->tanggal_selesai);
            
            // Hitung hari kerja (Senin-Jumat)
            $jumlahHari = 0;
            $current = $mulai->copy();
            
            while ($current->lte($selesai)) {
                if ($current->isWeekday()) {
                    $jumlahHari++;
                }
                $current->addDay();
            }
            
            return $jumlahHari;
        }
        
        return 0;
    }
}