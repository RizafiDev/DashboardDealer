<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Merek;

class MerekSeeder extends Seeder
{
    public function run(): void
    {
        $mereks = [
            ['nama' => 'Toyota', 'negara_asal' => 'Jepang', 'is_active' => true],
            ['nama' => 'Daihatsu', 'negara_asal' => 'Jepang', 'is_active' => true],
            ['nama' => 'Honda', 'negara_asal' => 'Jepang', 'is_active' => true],
            ['nama' => 'Mitsubishi', 'negara_asal' => 'Jepang', 'is_active' => true],
            ['nama' => 'Suzuki', 'negara_asal' => 'Jepang', 'is_active' => true],
            ['nama' => 'Hyundai', 'negara_asal' => 'Korea Selatan', 'is_active' => true],
            ['nama' => 'Wuling', 'negara_asal' => 'Tiongkok', 'is_active' => true],
        ];

        foreach ($mereks as $merek) {
            Merek::firstOrCreate(['nama' => $merek['nama']], $merek);
        }
    }
}