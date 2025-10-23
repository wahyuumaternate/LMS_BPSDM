<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prasyarats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kursus_id')->constrained('kursus')->onDelete('cascade');
            $table->foreignId('kursus_prasyarat_id')->constrained('kursus');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_wajib')->default(true);
            $table->timestamps();

            $table->index(['kursus_id', 'kursus_prasyarat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prasyarats');
    }
};
