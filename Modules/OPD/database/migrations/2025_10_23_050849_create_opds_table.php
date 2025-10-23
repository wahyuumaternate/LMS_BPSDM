<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opds', function (Blueprint $table) {
            $table->id();
            $table->string('kode_opd')->unique();
            $table->string('nama_opd');
            $table->text('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('nama_kepala')->nullable();
            $table->timestamps();

            $table->index('kode_opd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opds');
    }
};
