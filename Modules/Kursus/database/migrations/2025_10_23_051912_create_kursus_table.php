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
            
            // Foreign Keys
            $table->foreignId('admin_instruktur_id')
                ->constrained('admin_instrukturs')
                ->onDelete('restrict');
            
            $table->foreignId('jenis_kursus_id')
                ->constrained('jenis_kursus')
                ->onDelete('restrict');
            
            // Course Information
            $table->string('kode_kursus')->unique();
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('tujuan_pembelajaran')->nullable();
            $table->text('sasaran_peserta')->nullable();
            
            // Course Settings
            $table->integer('durasi_jam')->default(0);
            $table->integer('kuota_peserta')->default(0);
            $table->decimal('passing_grade', 5, 2)->default(70.00);
            
            // Dates
            $table->date('tanggal_buka_pendaftaran')->nullable();
            $table->date('tanggal_tutup_pendaftaran')->nullable();
            $table->date('tanggal_mulai_kursus')->nullable();
            $table->date('tanggal_selesai_kursus')->nullable();
            
            // Enums
            $table->enum('level', ['dasar', 'menengah', 'lanjut'])->default('dasar');
            $table->enum('tipe', ['daring', 'luring', 'hybrid'])->default('daring');
            $table->enum('status', ['draft', 'aktif', 'nonaktif', 'selesai'])->default('draft');
            
            // Media
            $table->string('thumbnail')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['kode_kursus', 'status', 'tanggal_mulai_kursus']);
            $table->index('status'); // Tambahan untuk query filter status
            $table->index('jenis_kursus_id'); // Tambahan untuk query berdasarkan jenis
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kursus');
    }
};