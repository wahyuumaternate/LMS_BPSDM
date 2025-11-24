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
        Schema::create('sertifikat', function (Blueprint $table) {
            $table->id();
            
            // Relasi
            $table->foreignId('peserta_id')->constrained('pesertas')->onDelete('cascade');
            $table->foreignId('kursus_id')->constrained('kursus')->onDelete('cascade');
            
            // Identitas Sertifikat
            $table->string('nomor_sertifikat')->unique();
            $table->date('tanggal_terbit');
            $table->string('tempat_terbit')->default('Jakarta');
            
            // Data Penandatangan 1 (Wajib)
            $table->string('nama_penandatangan1');
            $table->string('jabatan_penandatangan1');
            $table->string('nip_penandatangan1')->nullable();
            $table->string('tanda_tangan1_path')->nullable();
            
            // Data Penandatangan 2 (Opsional)
            $table->string('nama_penandatangan2')->nullable();
            $table->string('jabatan_penandatangan2')->nullable();
            $table->string('nip_penandatangan2')->nullable();
            $table->string('tanda_tangan2_path')->nullable();
            
            // File Generated
            $table->string('file_path')->nullable(); // path PDF
            $table->string('qr_code_path')->nullable(); // path QR code
            $table->text('verification_url')->nullable(); // URL verifikasi
            
            // Email Tracking
            $table->boolean('is_sent_email')->default(false);
            $table->timestamp('sent_email_at')->nullable();
            
            // Template & Metadata
            $table->string('template_name')->default('default');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'published', 'revoked'])->default('published');
            
            $table->timestamps();
            $table->softDeletes(); // Untuk soft delete
            
            // Indexes
            $table->index(['peserta_id', 'kursus_id']);
            $table->index('tanggal_terbit');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sertifikat');
    }
};