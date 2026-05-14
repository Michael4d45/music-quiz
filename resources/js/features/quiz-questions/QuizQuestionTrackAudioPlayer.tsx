import { TrackUploadAudioPlayer } from '@/features/music-tracks/TrackUploadAudioPlayer';
import type { MusicTrackData } from '@/schemas/App/Data/Models/MusicTrackData';
import type { QuizQuestionData } from '@/schemas/App/Data/Models/QuizQuestionData';

export interface QuizQuestionTrackAudioPlayerProps {
    /** Resolved track row (from question embed or tracks list); used for title and upload detection. */
    readonly track: MusicTrackData | null | undefined;
    readonly trackId: string | null | undefined;
    readonly className?: string;
}

/**
 * Audio preview for a quiz question's linked track (uploaded audio only),
 * using the authenticated my-tracks stream URL.
 */
export function QuizQuestionTrackAudioPlayer({
    track,
    trackId,
    className,
}: QuizQuestionTrackAudioPlayerProps) {
    const id = trackId?.trim() ?? '';
    if (id === '') {
        return null;
    }

    const hasUpload = Boolean(track?.user_upload_path);
    const trackTitle =
        track != null
            ? `${track.title} — ${track.artist_name}`
            : 'Linked track';

    return (
        <TrackUploadAudioPlayer
            trackId={id}
            trackTitle={trackTitle}
            hasUpload={hasUpload}
            className={className}
        />
    );
}

export function QuizQuestionTrackAudioPlayerFromQuestion({
    question,
    className,
}: {
    readonly question: QuizQuestionData | null | undefined;
    readonly className?: string;
}) {
    if (question == null) {
        return null;
    }
    return (
        <QuizQuestionTrackAudioPlayer
            trackId={question.track_id}
            track={question.track ?? undefined}
            className={className}
        />
    );
}
