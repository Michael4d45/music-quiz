<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameSession;
use App\Models\MusicTrack;
use App\Models\Playlist;
use App\Models\QuizQuestion;
use App\Models\SessionParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class GuestUserMergeService
{
    /**
     * Move rows owned by a guest user onto a registered account, resolve duplicate
     * session participation, then remove the guest record.
     */
    public function mergeGuestIntoUser(User $guest, User $target): void
    {
        if (!$guest->is_guest) {
            throw new InvalidArgumentException('Source user must be a guest.');
        }

        if ($target->is_guest) {
            throw new InvalidArgumentException(
                'Target user must not be a guest.',
            );
        }

        if ($guest->id === $target->id) {
            return;
        }

        DB::transaction(function () use ($guest, $target): void {
            Playlist::query()
                ->where('user_id', $guest->id)
                ->update([
                    'user_id' => $target->id,
                ]);

            GameSession::query()
                ->where('host_id', $guest->id)
                ->update([
                    'host_id' => $target->id,
                ]);

            QuizQuestion::query()
                ->where('user_id', $guest->id)
                ->update([
                    'user_id' => $target->id,
                ]);

            MusicTrack::query()
                ->where('user_id', $guest->id)
                ->update([
                    'user_id' => $target->id,
                ]);

            $guestParticipants = SessionParticipant::query()->where(
                'user_id',
                $guest->id,
            )->get();

            foreach ($guestParticipants as $participant) {
                $duplicateExists = SessionParticipant::query()
                    ->where('session_id', $participant->session_id)
                    ->where('user_id', $target->id)
                    ->where('id', '!=', $participant->id)
                    ->exists();

                if ($duplicateExists) {
                    $participant->delete();

                    continue;
                }

                $participant->update(['user_id' => $target->id]);
            }

            $guest->forceDelete();
        });

        session()->forget('guest_user_id');
    }
}
