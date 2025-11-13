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
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->text('pertanyaan');
            // Menghapus enum dan menggunakan string dengan nilai default 'pilihan_ganda'
            $table->string('tipe_soal')->default('pilihan_ganda');
            $table->text('pilihan_a');
            $table->text('pilihan_b');
            $table->text('pilihan_c');
            $table->text('pilihan_d');
            $table->text('jawaban_benar');
            $table->integer('poin')->default(1);
            $table->text('pembahasan')->nullable();
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->timestamps();

            // $table->index(['ujian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_ujians');
    }
};
