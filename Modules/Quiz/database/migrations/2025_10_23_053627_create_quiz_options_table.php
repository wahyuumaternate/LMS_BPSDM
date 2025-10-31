<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->onDelete('cascade');
            $table->text('teks_opsi');
            $table->boolean('is_jawaban_benar')->default(false);

            // Memastikan opsi selalu urut A, B, C, D...
            $table->enum('label', ['a', 'b', 'c', 'd'])->nullable();
            $table->integer('urutan')->default(0);

            $table->timestamps();
            $table->index(['question_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
    }
};
