<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_kursus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('pesertas');
            $table->foreignId('kursus_id')->constrained('kursus');
            $table->date('tanggal_daftar');
            $table->enum('status', ['pending', 'disetujui', 'ditolak', 'aktif', 'selesai', 'batal'])->default('pending');
            $table->text('alasan_ditolak')->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->enum('predikat', ['sangat_baik', 'baik', 'cukup', 'kurang'])->nullable();
            $table->timestamp('tanggal_disetujui')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->unique(['peserta_id', 'kursus_id']);
            $table->index(['peserta_id', 'status']);
            $table->index(['kursus_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_kursus');
    }
};
