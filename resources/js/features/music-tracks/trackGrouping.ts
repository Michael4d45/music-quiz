import type { MusicTrackData } from '@/schemas/App/Data/Models/MusicTrackData';
import type { QuizQuestionData } from '@/schemas/App/Data/Models/QuizQuestionData';
import {
    MusicTrackOriginKind,
    type MusicTrackOriginKind as MusicTrackOriginKindValue,
} from '@/schemas/App/Enums/MusicTrackOriginKind';

export const SINGLES_GROUP = 'Singles & one-offs';

export const STANDALONE_QUESTIONS_GROUP = 'Standalone questions';

export function usesAlbumForGrouping(
    kind: null | MusicTrackOriginKindValue,
): boolean {
    return kind === null || kind === MusicTrackOriginKind.Album;
}

/**
 * Library collection heading for a track — matches how tracks are grouped on My music tracks.
 */
export function groupHeading(track: MusicTrackData): string {
    const album = track.album_name?.trim();
    if (usesAlbumForGrouping(track.origin_kind)) {
        if (album) {
            return album;
        }
        const legacyAlbumishTitle = track.origin_title?.trim();
        if (legacyAlbumishTitle) {
            return legacyAlbumishTitle;
        }
        return SINGLES_GROUP;
    }

    const work = track.origin_title?.trim();
    if (work) {
        return work;
    }
    if (album) {
        return album;
    }
    return SINGLES_GROUP;
}

export function sortGroupEntries(
    entries: [string, MusicTrackData[]][],
): [string, MusicTrackData[]][] {
    return [...entries].sort(([a], [b]) => {
        if (a === SINGLES_GROUP) {
            return 1;
        }
        if (b === SINGLES_GROUP) {
            return -1;
        }
        return a.localeCompare(b);
    });
}

export function sortQuestionGroupEntries(
    entries: [string, QuizQuestionData[]][],
): [string, QuizQuestionData[]][] {
    return [...entries].sort(([a], [b]) => {
        const rank = (key: string): number => {
            if (key === STANDALONE_QUESTIONS_GROUP) {
                return 2;
            }
            if (key === SINGLES_GROUP) {
                return 1;
            }
            return 0;
        };
        const ra = rank(a);
        const rb = rank(b);
        if (ra !== rb) {
            return ra - rb;
        }
        return a.localeCompare(b);
    });
}

export function questionGroupHeading(q: QuizQuestionData): string {
    if (q.track) {
        return groupHeading(q.track);
    }
    return STANDALONE_QUESTIONS_GROUP;
}
