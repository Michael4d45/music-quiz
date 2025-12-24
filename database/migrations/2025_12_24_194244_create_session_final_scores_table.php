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
        Schema::create('session_final_scores', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('session_id');
            $table->uuid('participant_id');
            $table->integer('final_score');
            $table->integer('final_rank');
            $table->integer('questions_answered')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('average_response_time_ms')->nullable();
            $table->integer('longest_streak')->default(0);
            $table->timestampsTz();

            $table->foreign('session_id')->references('id')->on('game_sessions')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('session_participants')->onDelete('cascade');
            $table->index('session_id');
            $table->index(['participant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_final_scores');
    }
};
