<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_id')->constrained('sesi_kehadiran')->onDelete('cascade');
            $table->foreignId('peserta_id')->constrained('pesertas')->onDelete('cascade');
            $table->timestamp('waktu_checkin')->nullable();
            $table->timestamp('waktu_checkout')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'tidak_hadir'])->default('tidak_hadir');
            $table->integer('durasi_menit')->nullable();
            $table->string('lokasi_checkin')->nullable();
            $table->string('lokasi_checkout')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['sesi_id', 'peserta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
