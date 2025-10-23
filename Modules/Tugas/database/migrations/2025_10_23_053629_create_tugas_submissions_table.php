<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
            $table->foreignId('peserta_id')->constrained('pesertas')->onDelete('cascade');
            $table->foreignId('admin_instruktur_id')->nullable()->constrained('admin_instrukturs'); // Penilai
            $table->text('catatan_peserta')->nullable();
            $table->string('file_jawaban')->nullable();
            $table->text('catatan_penilai')->nullable();
            $table->integer('nilai')->nullable();
            $table->timestamp('tanggal_submit')->nullable();
            $table->timestamp('tanggal_dinilai')->nullable();
            $table->enum('status', ['draft', 'submitted', 'graded', 'returned', 'late'])->default('draft');
            $table->timestamps();

            $table->unique(['tugas_id', 'peserta_id']);
            $table->index(['tugas_id', 'status']);
            $table->index(['peserta_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_submissions');
    }
};
