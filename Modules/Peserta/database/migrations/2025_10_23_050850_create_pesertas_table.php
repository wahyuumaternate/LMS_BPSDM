<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained('opds');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nama_lengkap');
            $table->string('nip')->nullable()->unique();
            $table->string('pangkat_golongan')->nullable();
            $table->string('jabatan')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->enum('jenis_kelamin', ['laki_laki', 'perempuan'])->nullable();
            $table->enum('pendidikan_terakhir', ['sma', 'd3', 's1', 's2', 's3'])->nullable();
            $table->enum('status_kepegawaian', ['pns', 'pppk', 'kontrak'])->default('pns');
            $table->string('no_telepon')->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_profil')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['username', 'email', 'nip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesertas');
    }
};
