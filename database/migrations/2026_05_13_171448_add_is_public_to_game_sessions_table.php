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
        Schema::table('game_sessions', function (Blueprint $table): void {
            $table->boolean('is_public')->default(false)->after('max_players');
            $table->index(
                ['is_public', 'status', 'started_at'],
                'game_sessions_public_lobby_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table): void {
            $table->dropIndex('game_sessions_public_lobby_idx');
            $table->dropColumn('is_public');
        });
    }
};
