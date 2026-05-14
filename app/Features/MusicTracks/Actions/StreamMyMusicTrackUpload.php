<?php

declare(strict_types=1);

namespace App\Features\MusicTracks\Actions;

use App\Models\MusicTrack;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class StreamMyMusicTrackUpload
{
    public function __invoke(MusicTrack $musicTrack): Response|BinaryFileResponse
    {
        Gate::authorize('view', $musicTrack);

        if ($musicTrack->user_upload_path === null) {
            abort(404);
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($musicTrack->user_upload_path)) {
            abort(404);
        }

        $absolutePath = $disk->path($musicTrack->user_upload_path);

        $rawName =
            $musicTrack->user_upload_original_name ?? basename($musicTrack->user_upload_path);
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
