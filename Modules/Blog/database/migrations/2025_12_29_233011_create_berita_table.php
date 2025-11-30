<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_berita_id')->constrained('kategori_berita')->onDelete('cascade');
            
            // Author ID - akan di-link nanti jika table admin_instruktur ada
            $table->unsignedBigInteger('admin_instruktur_id')->nullable();
            
            // Content
            $table->string('judul', 255);
            $table->string('slug', 255)->unique();
            $table->text('ringkasan')->nullable();
            $table->longText('konten');
            
            // Media
            $table->string('gambar_utama', 255)->nullable();
            $table->string('sumber_gambar', 255)->nullable();
            
            // Publishing
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamp('published_at')->nullable();
            
            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 255)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'published_at']);
            $table->index('kategori_berita_id');
            $table->index('is_featured');
            $table->index('admin_instruktur_id');
        });

        // Add foreign key ONLY if admin_instruktur table exists
        if (Schema::hasTable('admin_instruktur')) {
            Schema::table('berita', function (Blueprint $table) {
                $table->foreign('admin_instruktur_id')
                      ->references('id')
                      ->on('admin_instruktur')
                      ->onDelete('set null'); // Set NULL instead of cascade
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('berita');
    }
};