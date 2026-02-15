<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('visibility', 20)->default('public');
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->index(['visibility', 'user_id']);
            $table->uuid('track_id')->nullable();
            $table->string('question_type');
            $table->text('prompt_text')->nullable();
            $table->text('rich_prompt_text')->nullable();
            $table->text('explanation')->nullable();
            $table->json('hints')->nullable();
            $table->string('correct_answer');
            $table->integer('base_points')->default(10);
            $table->integer('media_start_seconds')->nullable();
            $table->integer('media_end_seconds')->nullable();
            $table->integer('difficulty_level')->default(1);
            $table->boolean('is_draft')->default(false);
            $table->timestampTz('last_tested_at')->nullable();
            $table->timestampsTz();
            $table
                ->foreign('track_id')
                ->references('id')
                ->on('music_tracks')
                ->onDelete('set null');
            $table->index('user_id');
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
