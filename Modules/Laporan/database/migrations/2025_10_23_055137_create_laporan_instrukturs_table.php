<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_instrukturs', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('admin_instruktur_id')->constrained('admin_instrukturs')->onDelete('cascade');
            $table->foreignId('kursus_id')->constrained('kursus')->onDelete('cascade');
            $table->enum('tipe_laporan', ['progress', 'evaluasi', 'kehadiran', 'nilai_akhir', 'lainnya'])->default('progress');
            $table->text('konten_laporan');
            $table->string('file_path')->nullable();
            $table->date('periode_awal')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->timestamps();

            // $table->index(['admin_instruktur_id', 'kursus_id', 'tipe_laporan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_instrukturs');
    }
};
