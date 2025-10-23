<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujians')->onDelete('cascade');
            $table->foreignId('peserta_id')->constrained('pesertas')->onDelete('cascade');
            $table->json('jawaban')->nullable();
            $table->decimal('nilai', 5, 2)->default(0);
            $table->boolean('is_passed')->default(false);
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->timestamp('tanggal_dinilai')->nullable();
            $table->timestamps();

            $table->unique(['ujian_id', 'peserta_id']);
            $table->index(['ujian_id', 'is_passed']);
            $table->index(['peserta_id', 'is_passed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_results');
    }
};
