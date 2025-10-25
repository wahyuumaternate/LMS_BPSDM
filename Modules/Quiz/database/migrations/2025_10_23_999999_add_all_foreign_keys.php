<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {


        // Foreign key untuk quiz_questions -> quizzes
        if (Schema::hasTable('quiz_questions') && Schema::hasTable('quizzes')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                $table->foreign('quiz_id')
                    ->references('id')
                    ->on('quizzes')
                    ->onDelete('cascade');
            });
        }

        // Foreign key untuk quiz_options -> quiz_questions
        if (Schema::hasTable('quiz_options') && Schema::hasTable('quiz_questions')) {
            Schema::table('quiz_options', function (Blueprint $table) {
                $table->foreign('question_id')
                    ->references('id')
                    ->on('quiz_questions')
                    ->onDelete('cascade');
            });
        }

        // Tambahkan foreign key lainnya di sini jika ada
    }

    public function down(): void
    {
        // Hapus foreign keys dalam urutan terbalik
        if (Schema::hasTable('quiz_options')) {
            Schema::table('quiz_options', function (Blueprint $table) {
                $table->dropForeign(['question_id']);
            });
        }

        if (Schema::hasTable('quiz_questions')) {
            Schema::table('quiz_questions', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
            });
        }
    }
};
