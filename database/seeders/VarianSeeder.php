<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mobil;
use App\Models\Varian;

class VarianSeeder extends Seeder
{
    public function run(): void
    {
        $varians = [
            // Toyota Avanza
            [
                'mobil' => 'Avanza',
                'nama' => '1.3 E MT',
                'harga_otr' => 235000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1329,
                'transmisi' => 'manual',
                'tenaga_hp' => 98,
                'torsi_nm' => 121,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            [
                'mobil' => 'Avanza',
                'nama' => '1.5 G CVT',
                'harga_otr' => 270000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1496,
                'transmisi' => 'cvt',
                'tenaga_hp' => 106,
                'torsi_nm' => 137,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            // Honda Brio
            [
                'mobil' => 'Brio',
                'nama' => 'Satya S MT',
                'harga_otr' => 165000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1199,
                'transmisi' => 'manual',
                'tenaga_hp' => 90,
                'torsi_nm' => 110,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            [
                'mobil' => 'Brio',
                'nama' => 'RS CVT',
                'harga_otr' => 220000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1199,
                'transmisi' => 'automatic',
                'tenaga_hp' => 90,
                'torsi_nm' => 110,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            // Mitsubishi Xpander
            [
                'mobil' => 'Xpander',
                'nama' => 'GLS MT',
                'harga_otr' => 260000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1499,
                'transmisi' => 'manual',
                'tenaga_hp' => 105,
                'torsi_nm' => 141,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            [
                'mobil' => 'Xpander',
                'nama' => 'Ultimate CVT',
                'harga_otr' => 320000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1499,
                'transmisi' => 'cvt',
                'tenaga_hp' => 105,
                'torsi_nm' => 141,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            // Suzuki Ertiga
            [
                'mobil' => 'Ertiga',
                'nama' => 'GA MT',
                'harga_otr' => 225000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1462,
                'transmisi' => 'manual',
                'tenaga_hp' => 104,
                'torsi_nm' => 138,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            [
                'mobil' => 'Ertiga',
                'nama' => 'GX AT',
                'harga_otr' => 260000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1462,
                'transmisi' => 'automatic',
                'tenaga_hp' => 104,
                'torsi_nm' => 138,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            // Hyundai Stargazer
            [
                'mobil' => 'Stargazer',
                'nama' => 'Active MT',
                'harga_otr' => 250000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1497,
                'transmisi' => 'manual',
                'tenaga_hp' => 115,
                'torsi_nm' => 144,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            [
                'mobil' => 'Stargazer',
                'nama' => 'Prime IVT',
                'harga_otr' => 310000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1497,
                'transmisi' => 'cvt',
                'tenaga_hp' => 115,
                'torsi_nm' => 144,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
            // Wuling Confero
            [
                'mobil' => 'Confero',
                'nama' => '1.5 Double Blower',
                'harga_otr' => 180000000,
                'jenis_mesin' => 'bensin',
                'kapasitas_mesin' => 1485,
                'transmisi' => 'manual',
                'tenaga_hp' => 107,
                'torsi_nm' => 142,
                'bahan_bakar' => 'Bensin',
                'is_active' => true,
            ],
        ];

        foreach ($varians as $varian) {
            $mobil = Mobil::where('nama', $varian['mobil'])->first();
            if ($mobil) {
                Varian::firstOrCreate(
                    [
                        'mobil_id' => $mobil->id,
                        'nama' => $varian['nama'],
                    ],
                    [
                        'harga_otr' => $varian['harga_otr'],
                        'jenis_mesin' => $varian['jenis_mesin'],
                        'kapasitas_mesin' => $varian['kapasitas_mesin'],
                        'transmisi' => $varian['transmisi'],
                        'tenaga_hp' => $varian['tenaga_hp'],
                        'torsi_nm' => $varian['torsi_nm'],
                        'bahan_bakar' => $varian['bahan_bakar'],
                        'is_active' => $varian['is_active'],
                    ]
                );
            }
        }
    }
}