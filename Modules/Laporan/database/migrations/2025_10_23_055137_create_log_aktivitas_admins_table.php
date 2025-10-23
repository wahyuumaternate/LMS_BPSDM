<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas_admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_instruktur_id')->constrained('admin_instrukturs')->onDelete('cascade');
            $table->string('modul');
            $table->string('aksi');
            $table->text('deskripsi')->nullable();
            $table->text('data_lama')->nullable();
            $table->text('data_baru')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas_admins');
    }
};
