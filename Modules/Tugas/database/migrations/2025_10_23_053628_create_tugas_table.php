<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->constrained('moduls')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('petunjuk')->nullable();
            $table->string('file_tugas')->nullable(); // File tugas yang diupload instruktur
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_deadline')->nullable();
            $table->integer('nilai_maksimal')->default(100);
            $table->integer('bobot_nilai')->default(1);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['modul_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
