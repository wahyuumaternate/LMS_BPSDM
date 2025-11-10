<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kursus_id')->constrained('kursus')->onDelete('cascade');
            $table->string('judul_ujian');
            $table->text('deskripsi')->nullable();
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->integer('durasi_menit')->default(0);
            $table->decimal('bobot_nilai', 5, 2)->default(1.0);
            $table->integer('passing_grade')->default(70);
            $table->integer('jumlah_soal')->default(0);
            $table->boolean('random_soal')->default(false);
            $table->boolean('tampilkan_hasil')->default(true);
            $table->text('aturan_ujian')->nullable();
            $table->timestamps();

            $table->index(['kursus_id', 'waktu_mulai', 'waktu_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujians');
    }
};
