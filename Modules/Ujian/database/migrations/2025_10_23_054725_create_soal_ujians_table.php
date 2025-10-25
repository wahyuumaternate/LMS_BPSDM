<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_ujians', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->text('pertanyaan');
            $table->enum('tipe_soal', ['pilihan_ganda', 'essay', 'benar_salah'])->default('pilihan_ganda');
            $table->text('pilihan_a')->nullable();
            $table->text('pilihan_b')->nullable();
            $table->text('pilihan_c')->nullable();
            $table->text('pilihan_d')->nullable();
            $table->text('jawaban_benar')->nullable();
            $table->integer('poin')->default(1);
            $table->text('pembahasan')->nullable();
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->timestamps();

            // $table->index(['ujian_id', 'tipe_soal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_ujians');
    }
};
