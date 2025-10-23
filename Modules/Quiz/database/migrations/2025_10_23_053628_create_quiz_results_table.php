<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('peserta_id')->constrained('pesertas')->onDelete('cascade');
            $table->integer('skor')->default(0);
            $table->integer('total_benar')->default(0);
            $table->integer('total_salah')->default(0);
            $table->integer('total_tidak_jawab')->default(0);
            $table->integer('percobaan_ke')->default(1);
            $table->boolean('is_lulus')->default(false);
            $table->json('jawaban')->nullable(); // Menyimpan jawaban peserta
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('durasi_pengerjaan_detik')->default(0);
            $table->timestamps();

            $table->index(['quiz_id', 'peserta_id', 'percobaan_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
