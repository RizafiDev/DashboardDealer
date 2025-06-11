<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mobil;
use App\Models\Merek;
use App\Models\Kategori;

class MobilSeeder extends Seeder
{
    public function run(): void
    {
        $mobils = [
            [
                'nama' => 'Avanza',
                'model' => 'Avanza',
                'tahun_mulai' => 2003,
                'tahun_akhir' => null,
                'merek' => 'Toyota',
                'kategori' => 'MPV',
                'kapasitas_penumpang' => 7,
                'status' => 'active',
                'deskripsi' => 'MPV sejuta umat, irit dan mudah perawatan.',
            ],
            [
                'nama' => 'Xenia',
                'model' => 'Xenia',
                'tahun_mulai' => 2004,
                'tahun_akhir' => null,
                'merek' => 'Daihatsu',
                'kategori' => 'MPV',
                'kapasitas_penumpang' => 7,
                'status' => 'active',
                'deskripsi' => 'Kembaran Avanza, harga lebih terjangkau.',
            ],
            [
                'nama' => 'Brio',
                'model' => 'Brio',
                'tahun_mulai' => 2012,
                'tahun_akhir' => null,
                'merek' => 'Honda',
                'kategori' => 'Hatchback',
                'kapasitas_penumpang' => 5,
                'status' => 'active',
                'deskripsi' => 'Hatchback LCGC terlaris.',
            ],
            [
                'nama' => 'Xpander',
                'model' => 'Xpander',
                'tahun_mulai' => 2017,
                'tahun_akhir' => null,
                'merek' => 'Mitsubishi',
                'kategori' => 'MPV',
                'kapasitas_penumpang' => 7,
                'status' => 'active',
                'deskripsi' => 'MPV dengan desain modern dan fitur lengkap.',
            ],
            [
                'nama' => 'Ertiga',
                'model' => 'Ertiga',
                'tahun_mulai' => 2012,
                'tahun_akhir' => null,
                'merek' => 'Suzuki',
                'kategori' => 'MPV',
                'kapasitas_penumpang' => 7,
                'status' => 'active',
                'deskripsi' => 'MPV keluarga dari Suzuki.',
            ],
            [
                'nama' => 'Stargazer',
                'model' => 'Stargazer',
                'tahun_mulai' => 2022,
                'tahun_akhir' => null,
                'merek' => 'Hyundai',
                'kategori' => 'MPV',
                'kapasitas_penumpang' => 7,
                'status' => 'active',
                'deskripsi' => 'MPV Korea dengan fitur canggih.',
            ],
            [
                'nama' => 'Confero',
                'model' => 'Confero',
                'tahun_mulai' => 2017,
                'tahun_akhir' => null,
                'merek' => 'Wuling',
                'kategori' => 'MPV',
                'kapasitas_penumpang' => 8,
                'status' => 'active',
                'deskripsi' => 'MPV murah dengan fitur lengkap.',
            ],
        ];

        foreach ($mobils as $mobil) {
            $merek = Merek::where('nama', $mobil['merek'])->first();
            $kategori = Kategori::where('nama', $mobil['kategori'])->first();

            Mobil::firstOrCreate(
                [
                    'nama' => $mobil['nama'],
                    'merek_id' => $merek->id,
                ],
                [
                    'model' => $mobil['model'],
                    'tahun_mulai' => $mobil['tahun_mulai'],
                    'tahun_akhir' => $mobil['tahun_akhir'],
                    'kategori_id' => $kategori->id,
                    'kapasitas_penumpang' => $mobil['kapasitas_penumpang'],
                    'status' => $mobil['status'],
                    'deskripsi' => $mobil['deskripsi'],
                ]
            );
        }
    }
}