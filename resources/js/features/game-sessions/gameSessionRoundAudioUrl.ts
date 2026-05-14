/**
 * Same-origin authenticated stream for a session round's question track upload.
 */
export function gameSessionRoundAudioUrl(
    sessionId: string,
    roundId: string,
): string {
    return `/api/game-sessions/${encodeURIComponent(sessionId)}/rounds/${encodeURIComponent(roundId)}/audio`;
}
