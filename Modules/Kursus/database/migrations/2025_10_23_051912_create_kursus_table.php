<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kursus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_instruktur_id')->constrained('admin_instrukturs');
            $table->foreignId('kategori_id')->constrained('kategori_kursus');
            $table->string('kode_kursus')->unique();
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('tujuan_pembelajaran')->nullable();
            $table->text('sasaran_peserta')->nullable();
            $table->integer('durasi_jam')->default(0);
            $table->date('tanggal_buka_pendaftaran')->nullable();
            $table->date('tanggal_tutup_pendaftaran')->nullable();
            $table->date('tanggal_mulai_kursus')->nullable();
            $table->date('tanggal_selesai_kursus')->nullable();
            $table->integer('kuota_peserta')->default(0);
            $table->enum('level', ['dasar', 'menengah', 'lanjut'])->default('dasar');
            $table->enum('tipe', ['daring', 'luring', 'hybrid'])->default('daring');
            $table->enum('status', ['draft', 'aktif', 'nonaktif', 'selesai'])->default('draft');
            $table->string('thumbnail')->nullable();
            $table->decimal('passing_grade', 5, 2)->default(70.00);
            $table->timestamps();

            $table->index(['kode_kursus', 'status', 'tanggal_mulai_kursus']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kursus');
    }
};
