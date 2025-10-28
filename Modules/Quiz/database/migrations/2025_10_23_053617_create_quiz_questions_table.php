<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->text('pertanyaan');

            // Untuk pilihan ganda (A, B, C, D)
            $table->text('pilihan_a'); // DITAMBAH
            $table->text('pilihan_b'); // DITAMBAH
            $table->text('pilihan_c'); // DITAMBAH
            $table->text('pilihan_d'); // DITAMBAH
            $table->enum('jawaban_benar', ['a', 'b', 'c', 'd']); // DITAMBAH

            $table->integer('poin')->default(1); // DIPERBAIKI: bobot_nilai -> poin
            $table->text('pembahasan')->nullable(); // DIPERBAIKI: penjelasan -> pembahasan
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('mudah'); // DITAMBAH

            // Field untuk sistem yang lebih advanced (OPSIONAL - bisa dihapus jika tidak dipakai)
            // $table->enum('tipe', ['pilihan_ganda', 'benar_salah', 'isian'])->default('pilihan_ganda');
            // $table->integer('urutan')->default(0);

            $table->timestamps();

            $table->index(['quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
