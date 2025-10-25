<?php

namespace Modules\AdminInstruktur\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\AdminInstruktur\Entities\AdminInstruktur;

class AdminInstrukturSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin default
        AdminInstruktur::create([
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'), // ubah sesuai kebutuhan
            'role' => 'super_admin',
            'nama_lengkap' => 'Super Admin',
            'nip' => null,
            'gelar_depan' => null,
            'gelar_belakang' => null,
            'bidang_keahlian' => null,
            'no_telepon' => null,
            'alamat' => null,
            'foto_profil' => null,
            'email_verified_at' => now(),
        ]);

        // Contoh Instruktur
        AdminInstruktur::create([
            'username' => 'instruktur1',
            'email' => 'instruktur1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'instruktur',
            'nama_lengkap' => 'Instruktur 1',
            'nip' => '123456789',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.Sc',
            'bidang_keahlian' => 'Teknologi Informasi',
            'no_telepon' => '08123456789',
            'alamat' => 'Jalan Contoh No.1',
            'foto_profil' => null,
            'email_verified_at' => now(),
        ]);
    }
}
