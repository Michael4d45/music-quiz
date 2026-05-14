/**
 * Authenticated stream URL for the current user's uploaded track audio.
 * Same-origin; session cookies are sent by the browser for the native audio element.
 */
export function myMusicTrackUploadAudioUrl(trackId: string): string {
    return `/api/my/music-tracks/${encodeURIComponent(trackId)}/audio`;
}
