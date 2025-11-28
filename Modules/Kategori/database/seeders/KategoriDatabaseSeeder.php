<?php

namespace Modules\Kategori\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KategoriKursusSeeder::class,
            JenisKursusSeeder::class,
        ]);
    }
}