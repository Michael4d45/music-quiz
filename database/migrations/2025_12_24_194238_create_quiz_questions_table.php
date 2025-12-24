<?php

declare(strict_types=1);

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
        Schema::create('quiz_questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('track_id')->nullable();
            $table->string('question_type');
            $table->text('prompt_text')->nullable();
            $table->string('correct_answer');
            $table->integer('base_points')->default(1000);
            $table->integer('media_start_seconds')->nullable();
            $table->integer('media_end_seconds')->nullable();
            $table->integer('difficulty_level')->default(1);
            $table->timestampsTz();

            $table->foreign('track_id')->references('id')->on('music_tracks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
