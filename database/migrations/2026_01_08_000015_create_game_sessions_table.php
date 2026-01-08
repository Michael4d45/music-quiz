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
        Schema::create('game_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('host_id');
            $table->string('room_code', 6)->unique();
            $table->string('status');
            $table->uuid('quiz_mode_id');
            $table->uuid('scoring_rule_id');
            $table->uuid('playlist_id')->nullable();
            $table->integer('max_players')->default(8);
            $table->timestampsTz();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('ended_at')->nullable();

            $table->foreign('host_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('quiz_mode_id')->references('id')->on('quiz_modes')->onDelete('cascade');
            $table->foreign('scoring_rule_id')->references('id')->on('scoring_rules')->onDelete('cascade');
            $table->foreign('playlist_id')->references('id')->on('playlists')->onDelete('set null');
            $table->index('room_code');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
