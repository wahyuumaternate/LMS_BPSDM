<?php

namespace Modules\Peserta\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PesertaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert OPD
        $opdId = DB::table('opds')->insertGetId([
            'nama_opd' => 'Badan Pengembangan Sumber Daya Manusia',
            'kode_opd' => 'BPSDM',
            'alamat' => 'Jl. Pendidikan No. 123, Ternate',
            'no_telepon' => '0921-123456',
            'email' => 'bpsdm@ternate.go.id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Peserta
        DB::table('pesertas')->insert([
            'opd_id' => $opdId,
            'username' => 'peserta',
            'email' => 'peserta@example.com',
            'password' => Hash::make('password123'),
            'nama_lengkap' => 'Ahmad Hidayat',
            'nip' => '199001012020121001',
            'pangkat_golongan' => 'Penata Muda / III/a',
            'jabatan' => 'Analis Kebijakan',
            'tanggal_lahir' => '1990-01-01',
            'tempat_lahir' => 'Ternate',
            'jenis_kelamin' => 'laki_laki',
            'pendidikan_terakhir' => 's1',
            'status_kepegawaian' => 'pns',
            'no_telepon' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 123, Ternate',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ 1 OPD dan 1 Peserta berhasil ditambahkan!');
        $this->command->info('📧 Email: peserta@example.com');
        $this->command->info('👤 Username: peserta');
        $this->command->info('🔢 NIP: 199001012020121001');
        $this->command->info('🔑 Password: password123');
    }
}
