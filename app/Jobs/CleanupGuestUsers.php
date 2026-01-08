<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanupGuestUsers implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * Clean up guest users that are older than 30 days and have no meaningful data.
     */
    public function handle(): void
    {
        $cutoffDate = now()->subDays(30);

        $guestUsers = User::where('is_guest', true)
            ->where('created_at', '<', $cutoffDate)
            ->whereDoesntHave('gameSessions') // No hosted sessions
            ->whereDoesntHave('participants') // No participations
            ->whereDoesntHave('statistics') // No statistics
            ->get();

        $count = $guestUsers->count();

        if ($count > 0) {
            foreach ($guestUsers as $guest) {
                $guest->delete();
            }

            Log::info("Cleaned up {$count} guest users older than 30 days");
        }
    }
}
