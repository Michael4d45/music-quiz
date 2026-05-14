<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('playlists', function (Blueprint $table): void {
            $table->dropIndex(['status', 'visibility']);
            $table->dropColumn('status');
        });

        Schema::table('playlists', function (Blueprint $table): void {
            $table->index('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('playlists', function (Blueprint $table): void {
            $table->dropIndex(['visibility']);
        });

        Schema::table('playlists', function (Blueprint $table): void {
            $table->string('status', 20)->default('draft');
            $table->index(['status', 'visibility']);
        });
    }
};
