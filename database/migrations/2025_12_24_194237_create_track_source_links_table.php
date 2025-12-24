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
        Schema::create('track_source_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('track_id');
            $table->uuid('source_id');
            $table->string('external_id');
            $table->string('preview_url')->nullable();
            $table->string('full_url')->nullable();
            $table->string('embed_url')->nullable();
            $table->string('album_art_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_available')->default(true);
            $table->timestampTz('last_checked_at')->nullable();
            $table->timestampsTz();

            $table->foreign('track_id')->references('id')->on('music_tracks')->onDelete('cascade');
            $table->foreign('source_id')->references('id')->on('music_sources')->onDelete('cascade');
            $table->unique(['track_id', 'source_id']);
            $table->index('external_id');
            $table->index(['source_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_source_links');
    }
};
