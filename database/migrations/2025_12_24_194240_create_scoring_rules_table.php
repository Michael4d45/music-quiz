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
        Schema::create('scoring_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->integer('base_points')->default(1000);
            $table->float('decay_factor')->nullable();
            $table->integer('max_time_ms')->nullable();
            $table->boolean('streak_bonus_enabled')->default(false);
            $table->float('streak_multiplier')->default(1.5);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_rules');
    }
};
