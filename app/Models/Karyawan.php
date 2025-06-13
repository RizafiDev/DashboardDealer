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

    // Relasi untuk pembelian/penjualan
    public function pembelians(): HasMany
    {
        return $this->hasMany(Pembelian::class);
    }

    public function penjualanCompleted(): HasMany
    {
        return $this->hasMany(Pembelian::class)->where('status', 'completed');
    }

    // Helper methods untuk presensi
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

    // Helper methods untuk penjualan
    public function getTotalPenjualanAttribute()
    {
        return $this->penjualanCompleted()->count();
    }

    public function getTotalOmzetAttribute()
    {
        return $this->penjualanCompleted()->sum('harga_jual');
    }

    public function getFormattedOmzetAttribute()
    {
        return 'Rp ' . number_format($this->total_omzet, 0, ',', '.');
    }

    // Statistik penjualan per periode
    public function rekapPenjualan($bulan = null, $tahun = null)
    {
        $bulan = $bulan ?? date('m');
        $tahun = $tahun ?? date('Y');
        
        return $this->pembelians()
            ->whereMonth('tanggal_pembelian', $bulan)
            ->whereYear('tanggal_pembelian', $tahun)
            ->selectRaw('
                COUNT(*) as total_transaksi,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as transaksi_selesai,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as transaksi_pending,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as transaksi_batal,
                SUM(CASE WHEN status = "completed" THEN harga_jual ELSE 0 END) as total_omzet
            ')
            ->first();
    }

    // Mobil yang pernah dijual dengan detail
    public function mobilTerjual()
    {
        return $this->pembelians()
            ->with(['stokMobil.mobil.merek', 'stokMobil.varian'])
            ->where('status', 'completed')
            ->get()
            ->map(function ($pembelian) {
                return [
                    'no_faktur' => $pembelian->no_faktur,
                    'tanggal' => $pembelian->tanggal_pembelian->format('d/m/Y'),
                    'mobil' => $pembelian->stokMobil->mobil->nama ?? 'N/A',
                    'merek' => $pembelian->stokMobil->mobil->merek->nama ?? 'N/A',
                    'varian' => $pembelian->stokMobil->varian->nama ?? 'N/A',
                    'warna' => $pembelian->stokMobil->warna,
                    'harga' => $pembelian->harga_jual,
                    'pembeli' => $pembelian->nama_pembeli,
                ];
            });
    }

    // Top mobil yang sering dijual
    public function topMobilTerjual($limit = 5)
    {
        return $this->pembelians()
            ->with(['stokMobil.mobil'])
            ->where('status', 'completed')
            ->get()
            ->groupBy(function ($pembelian) {
                return $pembelian->stokMobil->mobil->nama ?? 'Unknown';
            })
            ->map(function ($group) {
                return [
                    'mobil' => $group->first()->stokMobil->mobil->nama ?? 'Unknown',
                    'jumlah_terjual' => $group->count(),
                    'total_omzet' => $group->sum('harga_jual'),
                ];
            })
            ->sortByDesc('jumlah_terjual')
            ->take($limit)
            ->values();
    }
}