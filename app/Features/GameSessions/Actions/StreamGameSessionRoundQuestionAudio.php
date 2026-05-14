<?php

declare(strict_types=1);

namespace App\Features\GameSessions\Actions;

use App\Models\GameSession;
use App\Models\SessionRound;
use App\Support\GameSessions\GameSessionRoundMediaAccess;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class StreamGameSessionRoundQuestionAudio
{
    public function __invoke(
        GameSession $gameSession,
        SessionRound $sessionRound,
    ): Response|BinaryFileResponse {
        $user = assertedUser();

        if (!GameSessionRoundMediaAccess::userMayAccessRound(
            $user,
            $gameSession,
            $sessionRound,
        )) {
            abort(403);
        }

        if ((string) $sessionRound->session_id !== (string) $gameSession->id) {
            abort(404);
        }

        $sessionRound->loadMissing([
            'question.track',
        ]);

        $question = $sessionRound->question;
        $track = $question->track;

        if ($track === null || $track->user_upload_path === null) {
            abort(404);
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($track->user_upload_path)) {
            abort(404);
        }

        $absolutePath = $disk->path($track->user_upload_path);

        $rawName =
            $track->user_upload_original_name ?? basename($track->user_upload_path);
        $safeName = $this->safeDispositionFilename($rawName);

        return response()->file($absolutePath, [
            'Content-Type' => $this->guessAudioMimeType($absolutePath),
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
        ]);
    }

    private function guessAudioMimeType(string $absolutePath): string
    {
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        return match ($ext) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a', 'mp4' => 'audio/mp4',
            'flac' => 'audio/flac',
            'ogg' => 'audio/ogg',
            default => 'application/octet-stream',
        };
    }

    private function safeDispositionFilename(string $filename): string
    {
        $base = basename(str_replace(["\0", "\r", "\n"], '', $filename));
        $ascii = Str::ascii($base);
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $ascii) ?? '';

        if ($sanitized === '' || $sanitized === '_') {
            return 'audio';
        }

        return $sanitized;
    }
}
