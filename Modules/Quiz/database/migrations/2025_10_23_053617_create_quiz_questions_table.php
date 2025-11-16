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
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('restrict');
            $table->text('pertanyaan');
            $table->integer('poin')->default(1);
            $table->text('pembahasan')->nullable();
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('mudah');

            // Karena selalu pilihan ganda, kita bisa hilangkan field 'tipe'
            // dan tetap menggunakan field urutan untuk urutan pertanyaan
            $table->integer('urutan')->default(0);

            $table->timestamps();
            $table->index(['quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
