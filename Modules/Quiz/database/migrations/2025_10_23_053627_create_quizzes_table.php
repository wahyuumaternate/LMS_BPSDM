<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->constrained('moduls')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->integer('durasi_menit')->default(0);
            $table->integer('jumlah_soal')->default(0);
            $table->integer('nilai_lulus')->default(70);
            $table->integer('batas_percobaan')->default(0); // 0 = unlimited
            $table->boolean('is_random_question')->default(false);
            $table->boolean('is_show_result')->default(true);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['modul_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
