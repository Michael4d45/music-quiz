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
        Schema::create('session_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('session_id');
            $table->string('event_type')->nullable();
            $table->uuid('participant_id')->nullable();
            $table->jsonb('payload')->nullable();
            $table->timestampsTz();

            $table->foreign('session_id')->references('id')->on('game_sessions')->onDelete('cascade');
            $table->foreign('participant_id')->references('id')->on('session_participants')->onDelete('set null');
            $table->index(['session_id', 'created_at']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_events');
    }
};
