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
        Schema::create('source_api_credentials', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('source_id');
            $table->string('credential_type')->nullable();
            $table->string('encrypted_value');
            $table->timestampTz('expires_at')->nullable();
            $table->timestampsTz();

            $table->foreign('source_id')->references('id')->on('music_sources')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_api_credentials');
    }
};
