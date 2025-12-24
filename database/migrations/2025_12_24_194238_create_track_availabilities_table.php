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
        Schema::create('track_availabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('track_source_link_id');
            $table->string('country_code', 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestampTz('last_checked_at')->nullable();

            $table->foreign('track_source_link_id')->references('id')->on('track_source_links')->onDelete('cascade');
            $table->unique(['track_source_link_id', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_availabilities');
    }
};
