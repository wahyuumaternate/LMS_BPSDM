<?php

namespace Modules\Kategori\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriKursusSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            [
                'nama_kategori' => 'Pelatihan Teknis',
                'slug' => 'pelatihan-teknis',
                'deskripsi' => 'Pelatihan yang berfokus pada pengembangan keterampilan teknis dan keahlian khusus',
                'icon' => 'ti ti-tools',
                'urutan' => 1,
            ],
            [
                'nama_kategori' => 'Pelatihan Manajerial',
                'slug' => 'pelatihan-manajerial',
                'deskripsi' => 'Pelatihan kepemimpinan dan manajerial untuk pengembangan kompetensi kepemimpinan',
                'icon' => 'ti ti-users',
                'urutan' => 2,
            ],
            [
                'nama_kategori' => 'Pelatihan Fungsional',
                'slug' => 'pelatihan-fungsional',
                'deskripsi' => 'Pelatihan untuk pengembangan kompetensi jabatan fungsional',
                'icon' => 'ti ti-certificate',
                'urutan' => 3,
            ],
        ];

        foreach ($kategoris as $kategori) {
            DB::table('kategori_kursus')->insert([
                'nama_kategori' => $kategori['nama_kategori'],
                'slug' => $kategori['slug'],
                'deskripsi' => $kategori['deskripsi'],
                'icon' => $kategori['icon'],
                'urutan' => $kategori['urutan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}