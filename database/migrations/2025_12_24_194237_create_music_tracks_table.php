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
        Schema::create('music_tracks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('artist_name');
            $table->string('album_name')->nullable();
            $table->integer('release_year')->nullable();
            $table->string('genre')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->uuid('sub_category_id');
            $table->uuid('primary_source_id');
            $table->timestampsTz();

            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onDelete('cascade');
            $table->foreign('primary_source_id')->references('id')->on('music_sources')->onDelete('cascade');
            $table->index('sub_category_id');
            $table->index(['artist_name', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music_tracks');
    }
};
