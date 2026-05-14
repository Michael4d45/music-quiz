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
        Schema::table('session_participants', function (Blueprint $table): void {
            $table->unique(
                ['session_id', 'user_id'],
                'session_participants_session_user_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_participants', function (Blueprint $table): void {
            $table->dropUnique('session_participants_session_user_unique');
        });
    }
};
