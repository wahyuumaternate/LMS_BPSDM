<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moduls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kursus_id')->constrained('kursus')->onDelete('cascade');
            $table->string('nama_modul');
            $table->integer('urutan')->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['kursus_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moduls');
    }
};
