<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            ['nama' => 'MPV', 'deskripsi' => 'Multi Purpose Vehicle'],
            ['nama' => 'SUV', 'deskripsi' => 'Sport Utility Vehicle'],
            ['nama' => 'Hatchback', 'deskripsi' => 'Hatchback'],
            ['nama' => 'Sedan', 'deskripsi' => 'Sedan'],
            ['nama' => 'LCGC', 'deskripsi' => 'Low Cost Green Car'],
            ['nama' => 'Pickup', 'deskripsi' => 'Pickup'],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::firstOrCreate(['nama' => $kategori['nama']], $kategori);
        }
    }
}