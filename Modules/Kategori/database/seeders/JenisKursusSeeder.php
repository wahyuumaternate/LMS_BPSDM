<?php

namespace Modules\Kategori\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisKursusSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID kategori
        $manajerialId = DB::table('kategori_kursus')->where('slug', 'pelatihan-manajerial')->value('id');
        $teknisId = DB::table('kategori_kursus')->where('slug', 'pelatihan-teknis')->value('id');
        $fungsionalId = DB::table('kategori_kursus')->where('slug', 'pelatihan-fungsional')->value('id');

        $jenisKursus = [
            // Pelatihan Manajerial
            [
                'kategori_kursus_id' => $manajerialId,
                'kode_jenis' => 'PKA',
                'nama_jenis' => 'Pelatihan Kepemimpinan Administrator',
                'slug' => 'pka',
                'deskripsi' => 'Pelatihan kepemimpinan untuk tingkat administrator',
                'urutan' => 1,
            ],
            [
                'kategori_kursus_id' => $manajerialId,
                'kode_jenis' => 'PKP',
                'nama_jenis' => 'Pelatihan Kepemimpinan Pengawas',
                'slug' => 'pkp',
                'deskripsi' => 'Pelatihan kepemimpinan untuk tingkat pengawas',
                'urutan' => 2,
            ],
            [
                'kategori_kursus_id' => $manajerialId,
                'kode_jenis' => 'LATSAR',
                'nama_jenis' => 'Latsar CPNS',
                'slug' => 'latsar-cpns',
                'deskripsi' => 'Pelatihan Dasar Calon Pegawai Negeri Sipil',
                'urutan' => 3,
            ],
            [
                'kategori_kursus_id' => $manajerialId,
                'kode_jenis' => 'PKKK',
                'nama_jenis' => 'Orientasi PKKK',
                'slug' => 'orientasi-pkkk',
                'deskripsi' => 'Program Orientasi Pengembangan Kompetensi Kepemimpinan Kolaboratif',
                'urutan' => 4,
            ],
            // Contoh Pelatihan Fungsional
            [
                'kategori_kursus_id' => $fungsionalId,
                'kode_jenis' => 'AUDITOR',
                'nama_jenis' => 'Pelatihan Auditor',
                'slug' => 'pelatihan-auditor',
                'deskripsi' => 'Pelatihan jabatan fungsional auditor',
                'urutan' => 1,
            ],
            [
                'kategori_kursus_id' => $fungsionalId,
                'kode_jenis' => 'ANALIS',
                'nama_jenis' => 'Pelatihan Analis Kebijakan',
                'slug' => 'pelatihan-analis-kebijakan',
                'deskripsi' => 'Pelatihan jabatan fungsional analis kebijakan',
                'urutan' => 2,
            ],
        ];

        foreach ($jenisKursus as $jenis) {
            DB::table('jenis_kursus')->insert([
                'kategori_kursus_id' => $jenis['kategori_kursus_id'],
                'kode_jenis' => $jenis['kode_jenis'],
                'nama_jenis' => $jenis['nama_jenis'],
                'slug' => $jenis['slug'],
                'deskripsi' => $jenis['deskripsi'],
                'is_active' => true,
                'urutan' => $jenis['urutan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}