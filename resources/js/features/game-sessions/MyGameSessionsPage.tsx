import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import {
    createGameSession,
    fetchMyGameSessions,
    fetchQuizModes,
    fetchScoringRules,
} from '@/features/game-sessions/api';
import { fetchMyPlaylists } from '@/features/playlists/api';
import type { GameSessionData } from '@/schemas/App/Data/Models/GameSessionData';
import type { QuizModeData } from '@/schemas/App/Data/Models/QuizModeData';
import type { ScoringRuleData } from '@/schemas/App/Data/Models/ScoringRuleData';
import type { PlaylistData } from '@/schemas/App/Data/Models/PlaylistData';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { Link, useLoaderData, useRevalidator } from 'react-router-dom';

export interface MyGameSessionsLoaderData {
    sessions: readonly GameSessionData[];
    quiz_modes: readonly QuizModeData[];
    scoring_rules: readonly ScoringRuleData[];
    playlists: readonly PlaylistData[];
}

export async function myGameSessionsLoader(): Promise<MyGameSessionsLoaderData> {
    const [sessionsRes, modesRes, rulesRes, playlistsRes] = await Promise.all([
        fetchMyGameSessions(),
        fetchQuizModes(),
        fetchScoringRules(),
        fetchMyPlaylists(),
    ]);

    return {
        sessions:
            sessionsRes._tag === 'Success' ? sessionsRes.data.sessions : [],
        quiz_modes: modesRes._tag === 'Success' ? modesRes.data.quiz_modes : [],
        scoring_rules:
            rulesRes._tag === 'Success' ? rulesRes.data.scoring_rules : [],
        playlists:
            playlistsRes._tag === 'Success' ? playlistsRes.data.playlists : [],
    };
}

export function MyGameSessionsPage() {
    const { sessions, quiz_modes, scoring_rules, playlists } =
        useLoaderData<MyGameSessionsLoaderData>();
    const revalidator = useRevalidator();
    const [quizModeId, setQuizModeId] = useState('');
    const [scoringRuleId, setScoringRuleId] = useState('');
    const [playlistId, setPlaylistId] = useState('');
    const [maxPlayers, setMaxPlayers] = useState(8);
    const [isPublic, setIsPublic] = useState(true);

    const handleCreate = async () => {
        if (!quizModeId || !scoringRuleId) {
            toast.error('Select a quiz mode and scoring rule');
            return;
        }
        const result = await createGameSession({
            quiz_mode_id: quizModeId,
            scoring_rule_id: scoringRuleId,
            playlist_id: playlistId.trim() === '' ? null : playlistId.trim(),
            max_players: maxPlayers,
            is_public: isPublic,
        });
        if (result._tag === 'Success') {
            toast.success(`Room ${result.data.room_code} created`);
            revalidator.revalidate();
        } else {
            toast.error('Could not create session');
        }
    };

    return (
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <h1 className="text-2xl font-bold">My game sessions</h1>
                <ButtonLink to="/game-sessions/lobby" variant="secondary">
                    Public lobby
                </ButtonLink>
            </div>

            <PageIntroExpandable
                summary="Create a room, share the code, and optionally bind a playlist so rounds draw from your library."
                moreLabel="More about hosting a session"
            >
                <p>
                    Host a room for your friends: pick how the game is scored, set
                    how many players can join, and optionally tie the session to
                    one of your playlists so each round uses your question
                    library.
                </p>
            </PageIntroExpandable>

            <div className="bg-card mb-10 flex flex-col gap-4 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <h2 className="text-lg font-semibold">Host a new session</h2>
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label
                            htmlFor="host-quiz-mode"
                            className="text-muted mb-1 block text-sm"
                        >
                            Quiz mode
                        </label>
                        <select
                            id="host-quiz-mode"
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={quizModeId}
                            onChange={(e) => setQuizModeId(e.target.value)}
                        >
                            <option value="">Select…</option>
                            {quiz_modes.map((m) => (
                                <option key={m.id} value={m.id}>
                                    {m.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label
                            htmlFor="host-scoring-rule"
                            className="text-muted mb-1 block text-sm"
                        >
                            Scoring rule
                        </label>
                        <select
                            id="host-scoring-rule"
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={scoringRuleId}
                            onChange={(e) => setScoringRuleId(e.target.value)}
                        >
                            <option value="">Select…</option>
                            {scoring_rules.map((r) => (
                                <option key={r.id} value={r.id}>
                                    {r.name ?? r.id}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label
                            htmlFor="host-playlist"
                            className="text-muted mb-1 block text-sm"
                        >
                            Playlist (optional)
                        </label>
                        <select
                            id="host-playlist"
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={playlistId}
                            onChange={(e) => setPlaylistId(e.target.value)}
                        >
                            <option value="">None</option>
                            {playlists.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label
                            htmlFor="host-max-players"
                            className="text-muted mb-1 block text-sm"
                        >
                            Max players
                        </label>
                        <input
                            id="host-max-players"
                            type="number"
                            min={2}
                            max={50}
                            className="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                            value={maxPlayers}
                            onChange={(e) =>
                                setMaxPlayers(Number.parseInt(e.target.value, 10))
                            }
                        />
                    </div>
                </div>
                <div className="flex flex-col gap-1">
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            id="host-is-public"
                            type="checkbox"
                            aria-describedby="host-is-public-hint"
                            checked={isPublic}
                            onChange={(e) => setIsPublic(e.target.checked)}
                        />
                        List in public lobby
                    </label>
                    <p
                        id="host-is-public-hint"
                        className="text-muted max-w-xl pl-6 text-xs"
                    >
                        When checked, joiners can discover this room from the
                        game lobby before it starts. Turn off for a private code
                        only you share.
                    </p>
                </div>
                <Button type="button" onClick={() => void handleCreate()}>
                    Create session
                </Button>
            </div>

            {sessions.length === 0 ? (
                <p className="text-muted">No sessions yet.</p>
            ) : (
                <ul className="flex flex-col gap-3" role="list">
                    {sessions.map((s) => (
                        <li key={s.id}>
                            <Link
                                to={`/game-sessions/room/${s.room_code}`}
                                className="bg-card hover:border-primary/40 flex flex-col gap-1 rounded-lg border border-transparent p-4 shadow-md transition-colors dark:border-white/10"
                            >
                                <span className="font-mono text-lg font-semibold tracking-wide">
                                    {s.room_code}
                                </span>
                                <span className="text-muted text-sm">
                                    {s.status}
                                    {s.is_public ? ' · public' : ' · private'}
                                </span>
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
