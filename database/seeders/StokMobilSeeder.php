<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StokMobil;
use App\Models\Mobil;
use App\Models\Varian;

class StokMobilSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'mobil' => 'Avanza',
                'varian' => '1.3 E MT',
                'warna' => 'Hitam',
                'no_rangka' => 'MHKA123456789001',
                'no_mesin' => '1NR1234567',
                'tahun' => 2023,
                'status' => 'ready',
                'harga_beli' => 210_000_000,
                'harga_jual' => 235_000_000,
                'tanggal_masuk' => '2024-01-10',
                'lokasi' => 'Gudang Utama',
                'keterangan' => 'Unit baru, ready stock',
            ],
            [
                'mobil' => 'Brio',
                'varian' => 'Satya S MT',
                'warna' => 'Merah',
                'no_rangka' => 'MHKH123456789002',
                'no_mesin' => 'L12B123456',
                'tahun' => 2024,
                'status' => 'ready',
                'harga_beli' => 150_000_000,
                'harga_jual' => 165_000_000,
                'tanggal_masuk' => '2024-02-15',
                'lokasi' => 'Showroom',
                'keterangan' => 'Promo DP ringan',
            ],
            [
                'mobil' => 'Xpander',
                'varian' => 'Ultimate CVT',
                'warna' => 'Putih',
                'no_rangka' => 'MHKX123456789003',
                'no_mesin' => '4A91123456',
                'tahun' => 2023,
                'status' => 'booking',
                'harga_beli' => 295_000_000,
                'harga_jual' => 320_000_000,
                'tanggal_masuk' => '2024-03-01',
                'lokasi' => 'Gudang 2',
                'keterangan' => 'Sudah booking, menunggu pembayaran',
            ],
            [
                'mobil' => 'Ertiga',
                'varian' => 'GX AT',
                'warna' => 'Abu-abu',
                'no_rangka' => 'MHKE123456789004',
                'no_mesin' => 'K15B123456',
                'tahun' => 2022,
                'status' => 'sold',
                'harga_beli' => 240_000_000,
                'harga_jual' => 260_000_000,
                'tanggal_masuk' => '2023-12-20',
                'lokasi' => 'Showroom',
                'keterangan' => 'Unit sudah terjual',
            ],
            [
                'mobil' => 'Stargazer',
                'varian' => 'Prime IVT',
                'warna' => 'Silver',
                'no_rangka' => 'MHKSG123456789005',
                'no_mesin' => 'G4FJ123456',
                'tahun' => 2024,
                'status' => 'ready',
                'harga_beli' => 285_000_000,
                'harga_jual' => 310_000_000,
                'tanggal_masuk' => '2024-04-05',
                'lokasi' => 'Gudang Utama',
                'keterangan' => null,
            ],
        ];

        foreach ($data as $item) {
            $mobil = Mobil::where('nama', $item['mobil'])->first();
            $varian = Varian::where('nama', $item['varian'])->where('mobil_id', $mobil?->id)->first();

            if ($mobil && $varian) {
                StokMobil::firstOrCreate(
                    [
                        'no_rangka' => $item['no_rangka'],
                        'no_mesin' => $item['no_mesin'],
                    ],
                    [
                        'mobil_id' => $mobil->id,
                        'varian_id' => $varian->id,
                        'warna' => $item['warna'],
                        'tahun' => $item['tahun'],
                        'status' => $item['status'],
                        'harga_beli' => $item['harga_beli'],
                        'harga_jual' => $item['harga_jual'],
                        'tanggal_masuk' => $item['tanggal_masuk'],
                        'lokasi' => $item['lokasi'],
                        'keterangan' => $item['keterangan'],
                    ]
                );
            }
        }
    }
}