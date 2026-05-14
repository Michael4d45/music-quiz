import { myMusicTrackUploadAudioUrl } from '@/features/music-tracks/audioUrl';
import { cn } from '@/lib/utils';

interface TrackUploadAudioPlayerProps {
    readonly trackId: string;
    readonly trackTitle: string;
    readonly hasUpload: boolean;
    readonly className?: string;
}

export function TrackUploadAudioPlayer({
    trackId,
    trackTitle,
    hasUpload,
    className,
}: TrackUploadAudioPlayerProps) {
    if (!hasUpload) {
        return (
            <p className={cn('text-muted text-xs leading-snug', className)}>
                Playback is only available when you upload an audio file. Rows
                tied to a streaming catalog are for quiz metadata; listen in
                your usual music app.
            </p>
        );
    }

    const src = myMusicTrackUploadAudioUrl(trackId);

    return (
        <div className={cn('flex w-full flex-col gap-1', className)}>
            <span className="text-muted text-xs">Listen</span>
            <audio
                controls
                preload="none"
                src={src}
                className="h-9 w-full max-w-md min-w-[12rem]"
                aria-label={`Play uploaded audio for ${trackTitle}`}
            >
                <a className="text-primary text-xs underline" href={src}>
                    Open audio
                </a>
            </audio>
        </div>
    );
}
