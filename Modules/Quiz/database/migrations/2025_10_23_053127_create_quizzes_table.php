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
            $table->string('judul_quiz'); // DIPERBAIKI: judul -> judul_quiz
            $table->text('deskripsi')->nullable();
            $table->integer('durasi_menit')->default(0);
            $table->decimal('bobot_nilai', 5, 2)->nullable(); // DITAMBAH
            $table->integer('passing_grade')->default(70); // DIPERBAIKI: nilai_lulus -> passing_grade
            $table->integer('jumlah_soal')->default(0);
            $table->boolean('random_soal')->default(false); // DIPERBAIKI: is_random_question -> random_soal
            $table->boolean('tampilkan_hasil')->default(true); // DIPERBAIKI: is_show_result -> tampilkan_hasil
            $table->integer('max_attempt')->default(0); // DIPERBAIKI: batas_percobaan -> max_attempt (0 = unlimited)
            $table->boolean('is_published')->default(false); // OPSIONAL - bisa dihapus jika tidak dipakai
            $table->timestamp('published_at')->nullable(); // OPSIONAL - bisa dihapus jika tidak dipakai
            $table->timestamps();

            $table->index(['modul_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
