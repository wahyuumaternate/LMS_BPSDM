<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('jadwal_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kursus_id')->constrained('kursus')->onDelete('cascade');
            $table->string('nama_kegiatan');
            $table->timestamp('waktu_mulai_kegiatan');
            $table->timestamp('waktu_selesai_kegiatan');
            $table->string('lokasi')->nullable();
            $table->enum('tipe', ['online', 'offline', 'hybrid'])->default('offline');
            $table->string('link_meeting')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kegiatan');
    }
};
