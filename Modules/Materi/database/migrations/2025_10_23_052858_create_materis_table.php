<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_id')->constrained('moduls')->onDelete('cascade');
            $table->string('judul_materi');
            $table->integer('urutan')->default(0);
            $table->enum('tipe_konten', ['pdf', 'doc', 'video', 'audio', 'gambar', 'link', 'scorm'])->default('pdf');
            $table->string('file_path')->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('durasi_menit')->default(0);
            $table->integer('ukuran_file')->default(0); // dalam KB
            $table->boolean('is_wajib')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['modul_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materis');
    }
};
