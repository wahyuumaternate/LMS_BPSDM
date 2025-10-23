<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_sertifikats', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->text('design_template')->nullable();
            $table->string('path_background')->nullable();
            $table->text('signature_config')->nullable();
            $table->string('logo_bpsdm_path')->nullable();
            $table->string('logo_pemda_path')->nullable();
            $table->text('footer_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_sertifikats');
    }
};
