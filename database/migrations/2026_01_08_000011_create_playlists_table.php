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
        Schema::create('playlists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->string('visibility', 20)->default('private');
            $table->json('tags')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->string('target_audience')->nullable();
            $table->string('question_order', 20)->default('fixed');
            $table->integer('default_time_limit_seconds')->nullable();
            $table->uuid('scoring_rule_id')->nullable();
            $table->integer('play_count')->default(0);
            $table->timestampsTz();

            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->index('user_id');
            $table->index(['status', 'visibility']);
            $table->index('scoring_rule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
