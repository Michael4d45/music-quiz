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
        Schema::create('player_answers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('round_id');
            $table->uuid('participant_id');
            $table->string('submitted_text')->nullable();
            $table->uuid('selected_option_id')->nullable();
            $table->uuid('matched_variant_id')->nullable();
            $table->boolean('is_correct');
            $table->integer('response_time_ms')->nullable();
            $table->integer('points_awarded')->nullable();
            $table->boolean('host_override')->default(false);
            $table->timestampsTz();

            $table->foreign('round_id')->references('id')->on('session_rounds')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('session_participants')->onDelete('cascade');
            $table->foreign('selected_option_id')->references('id')->on('multiple_choice_options')->onDelete('set null');
            $table->foreign('matched_variant_id')->references('id')->on('answer_variants')->onDelete('set null');
            $table->index('round_id');
            $table->index('participant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_answers');
    }
};
