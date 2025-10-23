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
            $table->enum('tipe', ['pilihan_ganda', 'benar_salah', 'isian'])->default('pilihan_ganda');
            $table->integer('bobot_nilai')->default(1);
            $table->text('penjelasan')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index(['quiz_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
