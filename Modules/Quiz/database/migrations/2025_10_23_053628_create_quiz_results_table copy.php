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

            $table->decimal('nilai', 5, 2)->default(0);
            $table->integer('jumlah_benar')->default(0);
            $table->integer('jumlah_salah')->default(0);
            $table->integer('total_tidak_jawab')->default(0);

            $table->integer('attempt')->default(1);
            $table->boolean('is_passed')->default(false);

            // Mengubah format jawaban untuk menyimpan ID opsi
            $table->json('jawaban')->nullable(); // Format baru: {"question_id": "option_id", ...}

            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('durasi_pengerjaan_menit')->default(0);

            $table->timestamps();

            $table->index(['quiz_id', 'peserta_id', 'attempt']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
