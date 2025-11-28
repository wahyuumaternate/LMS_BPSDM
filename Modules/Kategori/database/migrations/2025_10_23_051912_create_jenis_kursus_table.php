<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_kursus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_kursus_id')
                  ->constrained('kategori_kursus')
                  ->onDelete('cascade');
            $table->string('kode_jenis', 20)->unique();
            $table->string('nama_jenis');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index(['kategori_kursus_id', 'is_active']);
            $table->index('kode_jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_kursus');
    }
};