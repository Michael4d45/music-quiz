<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('music_tracks', function (Blueprint $table): void {
            $table->string('origin_kind', 32)->nullable()->after('duration_ms');
            $table->string('origin_title')->nullable()->after('origin_kind');
            $table
                ->string('user_upload_path', 512)
                ->nullable()
                ->after('origin_title');
            $table
                ->string('user_upload_original_name', 255)
                ->nullable()
                ->after('user_upload_path');
        });

        $exists = DB::table('music_sources')
            ->where('name', 'user_upload')
            ->exists();

        if (!$exists) {
            DB::table('music_sources')->insert([
                'id' => (string) Str::uuid(),
                'name' => 'user_upload',
                'display_name' => 'My audio file (upload)',
                'icon_url' => null,
                'api_base_url' => null,
                'requires_authentication' => false,
                'is_active' => true,
                'priority' => 99,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('music_tracks', function (Blueprint $table): void {
            $table->dropColumn([
                'origin_kind',
                'origin_title',
                'user_upload_path',
                'user_upload_original_name',
            ]);
        });

        DB::table('music_sources')->where('name', 'user_upload')->delete();
    }
};
