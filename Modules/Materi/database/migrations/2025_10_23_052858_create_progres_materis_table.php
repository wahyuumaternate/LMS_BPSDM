<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progres_materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('pesertas')->onDelete('cascade');
            $table->foreignId('materi_id')->constrained('materis')->onDelete('cascade');
            $table->boolean('is_selesai')->default(false);
            $table->integer('progress_persen')->default(0);
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->integer('durasi_belajar_menit')->default(0);
            $table->timestamps();

            $table->unique(['peserta_id', 'materi_id']);
            $table->index(['materi_id', 'peserta_id', 'is_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progres_materis');
    }
};
