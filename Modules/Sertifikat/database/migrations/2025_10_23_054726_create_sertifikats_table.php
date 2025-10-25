<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('pesertas')->onDelete('cascade');
            $table->foreignId('kursus_id')->constrained('kursus')->onDelete('cascade');
            // $table->foreignId('template_id')->constrained('template_sertifikats')->onDelete('cascade');
            $table->string('nomor_sertifikat')->unique();
            $table->date('tanggal_terbit');
            $table->string('file_path')->nullable();
            $table->string('qr_code')->nullable();
            $table->text('signature_digital')->nullable();
            $table->string('nama_penandatangan')->nullable();
            $table->string('jabatan_penandatangan')->nullable();
            $table->boolean('is_sent_email')->default(false);
            $table->timestamps();

            $table->unique(['peserta_id', 'kursus_id']);
            $table->index(['nomor_sertifikat', 'tanggal_terbit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikats');
    }
};
