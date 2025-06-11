<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Varian extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobil_id',
        'nama',
        'deskripsi',
        'harga_otr',
        'jenis_mesin',
        'kapasitas_mesin',
        'transmisi',
        'tenaga_hp',
        'torsi_nm',
        'bahan_bakar',
        'konsumsi_bbm_kota',
        'konsumsi_bbm_luar_kota',
        'panjang_mm',
        'lebar_mm',
        'tinggi_mm',
        'berat_kosong_kg',
        'berat_kotor_kg',
        'wheelbase_mm',
        'ground_clearance_mm',
        'kapasitas_bagasi_liter',
        'kapasitas_tangki_liter',
        'airbag',
        'jumlah_airbag',
        'abs',
        'ebd',
        'ba',
        'esc',
        'hill_start_assist',
        'kamera_belakang',
        'sensor_parkir',
        'ac',
        'ac_double_blower',
        'power_steering',
        'power_window',
        'central_lock',
        'audio_system',
        'bluetooth',
        'usb_port',
        'wireless_charging',
        'sunroof',
        'cruise_control',
        'keyless_entry',
        'push_start_button',
        'jenis_velg',
        'ukuran_ban',
        'is_active',
    ];

    protected $casts = [
        'harga_otr' => 'decimal:2',
        'konsumsi_bbm_kota' => 'decimal:2',
        'konsumsi_bbm_luar_kota' => 'decimal:2',
        'airbag' => 'boolean',
        'abs' => 'boolean',
        'ebd' => 'boolean',
        'ba' => 'boolean',
        'esc' => 'boolean',
        'hill_start_assist' => 'boolean',
        'kamera_belakang' => 'boolean',
        'sensor_parkir' => 'boolean',
        'ac' => 'boolean',
        'ac_double_blower' => 'boolean',
        'power_steering' => 'boolean',
        'power_window' => 'boolean',
        'central_lock' => 'boolean',
        'audio_system' => 'boolean',
        'bluetooth' => 'boolean',
        'usb_port' => 'boolean',
        'wireless_charging' => 'boolean',
        'sunroof' => 'boolean',
        'cruise_control' => 'boolean',
        'keyless_entry' => 'boolean',
        'push_start_button' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_otr, 0, ',', '.');
    }
}