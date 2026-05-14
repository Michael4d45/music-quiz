import { PageHeader } from '@/components/layout/PageHeader';
import { PageShell } from '@/components/layout/PageShell';
import { Surface } from '@/components/layout/Surface';
import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import {
    advanceGameSessionRound,
    joinGameSession,
    leaveGameSession,
    startGameSession,
    submitSessionRoundAnswer,
} from '@/features/game-sessions/api';
import { gameSessionStatusLabel } from '@/features/game-sessions/gameSessionStatusLabel';
import {
    SessionRoundMediaPlayer,
    type SessionRoundRemotePlayback,
} from '@/features/game-sessions/SessionRoundMediaPlayer';
import { useGameSessionChannel } from '@/hooks/useGameSessionChannel';
import { useOfflineBlock } from '@/hooks/useOfflineBlock';
import { apiFailureMessage } from '@/lib/apiCore';
import { cn } from '@/lib/utils';
import type { SessionParticipantData } from '@/schemas/App/Data/Models/SessionParticipantData';
import type { GameSessionRoomViewData } from '@/schemas/App/Data/Responses/GameSessionRoomViewData';
import type { SessionRoundGameplayData } from '@/schemas/App/Data/Responses/SessionRoundGameplayData';
import { QuestionType } from '@/schemas/App/Enums/QuestionType';
import { useState } from 'react';
import toast from 'react-hot-toast';
import {
    useLoaderData,
    useNavigate,
    useParams,
    useRevalidator,
} from 'react-router-dom';

export { gameSessionRecapLoader } from '@/features/game-sessions/gameSessionRecapLoader';
export { gameSessionRoomLoader } from '@/features/game-sessions/gameSessionRoomLoader';

function findActiveRound(
    rounds: readonly SessionRoundGameplayData[],
): SessionRoundGameplayData | null {
    return (
        rounds.find((r) => r.started_at != null && r.ended_at == null) ?? null
    );
}

function formatSessionInstant(value: Date | null | undefined): string {
    if (value == null) {
        return '—';
    }
    return new Intl.DateTimeFormat(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(value instanceof Date ? value : new Date(value));
}

function multipleChoiceAnswerLabel(
    round: SessionRoundGameplayData,
    selectedOptionId: string | null,
): string | null {
    if (!selectedOptionId) {
        return null;
    }
    const opt = round.question.multiple_choice_options.find(
        (o) => o.id === selectedOptionId,
    );
    return opt?.option_text ?? null;
}

function roundAudioLabel(round: SessionRoundGameplayData): string {
    const q = round.question;
    const title = q.track_title?.trim() ?? 'Track';
    const artist = q.track_artist_name?.trim() ?? '';
    if (artist === '') {
        return `Round ${round.round_number} audio: ${title}`;
    }
    return `Round ${round.round_number} audio: ${title} — ${artist}`;
}

function participantSeatLabel(participant: SessionParticipantData): string {
    const alias = participant.guest_name?.trim();
    if (alias) {
        return alias;
    }
    return participant.user?.name?.trim() || 'Player';
}

export function GameSessionRoomPage() {
    const room = useLoaderData<GameSessionRoomViewData>();
    const core = room.session;
    const params = useParams<{
        roomCode?: string;
        sessionId?: string;
    }>();
    const navigate = useNavigate();
    const revalidator = useRevalidator();
    const { isBlocked, blockReason } = useOfflineBlock();
    const roomCode = params.roomCode ?? core.room_code;
    const isRecapRoute = Boolean(params.sessionId);
    const isCompleted = core.status === 'completed';

    const [remotePlayback, setRemotePlayback] =
        useState<SessionRoundRemotePlayback | null>(null);

    const revalidateRoom = () => {
        revalidator.revalidate();
    };

    const maySubscribeToSessionChannel =
        !isCompleted &&
        (room.viewer_is_host || room.viewer_participant_id !== null);

    const { participantCount } = useGameSessionChannel(
        maySubscribeToSessionChannel ? core.id : undefined,
        {
            onSessionUpdated: revalidateRoom,
            onRoundMediaPlayback: (data) => {
                if (data.session_id !== core.id) {
                    return;
                }
                setRemotePlayback({
                    round_id: data.round_id,
                    playing: data.playing,
                    current_time_seconds: data.current_time_seconds,
                    server_seq: data.server_seq,
                });
            },
            subscribeRoundMediaPlayback: !isCompleted && !room.viewer_is_host,
        },
    );

    const displayCount = (() => {
        if (participantCount !== null) {
            return participantCount;
        }
        return core.participants?.length ?? 0;
    })();

    const sortedParticipants = (() => {
        const list = core.participants ? [...core.participants] : [];
        list.sort((a, b) => b.current_total_score - a.current_total_score);
        return list;
    })();

    const sortedRounds = [...room.rounds].sort(
        (a, b) => a.round_number - b.round_number,
    );

    const activeRound = findActiveRound(room.rounds);

    const effectiveRemotePlayback =
        activeRound !== null &&
        remotePlayback !== null &&
        remotePlayback.round_id === activeRound.id
            ? remotePlayback
            : null;

    const [leaving, setLeaving] = useState(false);
    const [joining, setJoining] = useState(false);
    const [joinDisplayName, setJoinDisplayName] = useState('');
    const [starting, setStarting] = useState(false);
    const [advancing, setAdvancing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [selectedOptionId, setSelectedOptionId] = useState<string | null>(
        null,
    );
    const [textAnswer, setTextAnswer] = useState('');

    const shareRoomUrl =
        typeof window !== 'undefined' && roomCode.trim() !== ''
            ? `${window.location.origin}/game-sessions/room/${roomCode.trim().toUpperCase()}`
            : '';

    const showInviteLinkCard =
        !isRecapRoute && core.status === 'lobby' && shareRoomUrl !== '';

    const copyInviteLink = async () => {
        if (isBlocked) {
            toast.error(blockReason || 'Cannot copy while offline');
            return;
        }
        if (shareRoomUrl === '') {
            toast.error('No room link');
            return;
        }
        try {
            await navigator.clipboard.writeText(shareRoomUrl);
            toast.success('Invite link copied');
        } catch {
            toast.error('Could not copy link');
        }
    };

    const handleJoinRoom = async () => {
        if (isBlocked) {
            toast.error(blockReason || 'Cannot join while offline');
            return;
        }
        const normalized = roomCode.trim().toUpperCase();
        if (normalized.length !== 6) {
            toast.error('Invalid room code');
            return;
        }
        setJoining(true);
        const result = await joinGameSession(normalized, {
            display_name: joinDisplayName,
        });
        setJoining(false);
        if (result._tag === 'Success') {
            toast.success('You have a seat in this room');
            revalidateRoom();
        } else {
            toast.error(apiFailureMessage(result, 'Could not join this room'));
        }
    };

    const handleLeave = async () => {
        if (core.status === 'completed') {
            return;
        }
        setLeaving(true);
        const result = await leaveGameSession(core.id);
        setLeaving(false);
        if (result._tag === 'Success') {
            toast.success('Left the room');
            navigate('/game-sessions/lobby');
        } else {
            toast.error('Could not leave');
            revalidateRoom();
        }
    };

    const handleStart = async () => {
        setStarting(true);
        const result = await startGameSession(core.id);
        setStarting(false);
        if (result._tag === 'Success') {
            toast.success('Game started');
            revalidateRoom();
        } else {
            toast.error(apiFailureMessage(result, 'Could not start game'));
        }
    };

    const handleAdvance = async () => {
        setAdvancing(true);
        const result = await advanceGameSessionRound(core.id);
        setAdvancing(false);
        if (result._tag === 'Success') {
            toast.success('Round updated');
            setSelectedOptionId(null);
            setTextAnswer('');
            revalidateRoom();
        } else {
            toast.error(apiFailureMessage(result, 'Could not advance round'));
        }
    };

    const myAnswerForActiveRound = (() => {
        if (!activeRound || !room.viewer_participant_id) {
            return null;
        }
        return (
            activeRound.answers.find(
                (a) => a.participant_id === room.viewer_participant_id,
            ) ?? null
        );
    })();

    const handleSubmitAnswer = async () => {
        if (!activeRound) {
            return;
        }
        setSubmitting(true);
        const q = activeRound.question;
        const payload =
            q.question_type === QuestionType.MultipleChoice
                ? { selected_option_id: selectedOptionId ?? undefined }
                : { submitted_text: textAnswer };

        const result = await submitSessionRoundAnswer(
            core.id,
            activeRound.id,
            payload,
        );
        setSubmitting(false);
        if (result._tag === 'Success') {
            toast.success('Answer submitted');
            revalidateRoom();
        } else {
            toast.error(apiFailureMessage(result, 'Could not submit answer'));
        }
    };

    const canSubmitMc =
        activeRound?.question.question_type === QuestionType.MultipleChoice &&
        selectedOptionId !== null;

    const canSubmitText =
        activeRound?.question.question_type !== QuestionType.MultipleChoice &&
        textAnswer.trim().length > 0;

    return (
        <PageShell>
            <PageHeader
                title={isRecapRoute ? 'Session recap' : `Room ${roomCode}`}
                description={
                    <>
                        {isRecapRoute ? (
                            <span className="font-mono tracking-wide">
                                {core.room_code}
                            </span>
                        ) : null}
                        {isRecapRoute ? ' · ' : null}
                        Status: {gameSessionStatusLabel(core.status)} · Players:{' '}
                        {displayCount} / {core.max_players}
                        {room.viewer_is_host ? (
                            <span> · You are the host</span>
                        ) : null}
                        {room.viewer_participant_id && isCompleted ? (
                            <span> · You played in this session</span>
                        ) : null}
                    </>
                }
            />

            {showInviteLinkCard ? (
                <Surface
                    variant="tint"
                    className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div className="min-w-0 flex-1">
                        <p className="text-muted mb-1 text-xs font-medium tracking-wide uppercase">
                            Invite link
                        </p>
                        <input
                            type="text"
                            readOnly
                            value={shareRoomUrl}
                            aria-label="Room invite link"
                            className="border-input bg-background text-foreground w-full rounded-md border px-3 py-2 font-mono text-sm"
                            onFocus={(e) => e.currentTarget.select()}
                            onClick={(e) => e.currentTarget.select()}
                        />
                    </div>
                    <Button
                        type="button"
                        variant="secondary"
                        className="shrink-0"
                        onClick={() => void copyInviteLink()}
                    >
                        Copy link
                    </Button>
                </Surface>
            ) : null}

            {!isCompleted ? (
                <PageIntroExpandable
                    summary="Live session room: host can start the quiz and advance rounds; players submit answers when a round is open."
                    moreLabel="How play works"
                >
                    <p className="text-muted text-sm">
                        Join while the session is in the lobby — use the join
                        card below, or enter the code on the game lobby page.
                        When the host starts, the host also gets a player seat
                        so you can rehearse solo. Questions follow the playlist
                        order (up to ten rounds). After you submit, other
                        players will not see your answer until the round closes.
                        The host advances when you are ready for the next
                        question.
                    </p>
                </PageIntroExpandable>
            ) : null}

            {core.status === 'lobby' &&
            !room.viewer_is_host &&
            !room.viewer_participant_id ? (
                <Surface variant="tint" className="mb-8">
                    <h2 className="mb-2 font-semibold">Join this room</h2>
                    <p className="text-muted mb-3 text-sm">
                        Opening the invite link only shows the room. Take a seat
                        to appear in the player list and play when the host
                        starts. Optionally choose how your name appears in this
                        room only (your account name stays private if you fill
                        this in).
                    </p>
                    <div className="mb-3">
                        <label
                            htmlFor="room-join-display-name"
                            className="text-muted mb-1 block text-sm"
                        >
                            Name in this room (optional)
                        </label>
                        <input
                            id="room-join-display-name"
                            type="text"
                            maxLength={64}
                            autoComplete="nickname"
                            className="border-input bg-background w-full max-w-md rounded-md border px-3 py-2 text-sm"
                            value={joinDisplayName}
                            onChange={(e) => setJoinDisplayName(e.target.value)}
                            placeholder="e.g. Team Blue"
                        />
                    </div>
                    <Button
                        type="button"
                        disabled={joining}
                        onClick={() => void handleJoinRoom()}
                    >
                        {joining ? 'Joining…' : 'Join this room'}
                    </Button>
                </Surface>
            ) : null}

            {core.status === 'lobby' && room.viewer_is_host ? (
                <Surface variant="tint" className="mb-8">
                    <h2 className="mb-2 font-semibold">Host controls</h2>
                    <p className="text-muted mb-3 text-sm">
                        {core.playlist_id
                            ? 'Start the game to create rounds from your playlist.'
                            : 'Choose a playlist for this session (My sessions) before starting.'}
                    </p>
                    <Button
                        type="button"
                        disabled={starting || !core.playlist_id}
                        onClick={() => void handleStart()}
                    >
                        {starting ? 'Starting…' : 'Start game'}
                    </Button>
                </Surface>
            ) : null}

            {core.status === 'in_progress' && activeRound ? (
                <Surface variant="elevated" className="mb-8">
                    <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <h2 className="font-semibold">
                            Round {activeRound.round_number}
                        </h2>
                        {room.viewer_is_host ? (
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={advancing}
                                onClick={() => void handleAdvance()}
                            >
                                {advancing ? 'Working…' : 'End round / next'}
                            </Button>
                        ) : null}
                    </div>
                    <p className="text-muted mb-1 text-xs uppercase">
                        {activeRound.question.question_type.replaceAll(
                            '_',
                            ' ',
                        )}
                    </p>
                    <p className="mb-4 text-base">
                        {activeRound.question.prompt_text?.trim() || 'Question'}
                    </p>

                    {activeRound.question.audio_upload_available ? (
                        <SessionRoundMediaPlayer
                            key={activeRound.id}
                            sessionId={core.id}
                            roundId={activeRound.id}
                            variant={room.viewer_is_host ? 'host' : 'follower'}
                            mediaStartSeconds={
                                activeRound.question.media_start_seconds
                            }
                            mediaEndSeconds={
                                activeRound.question.media_end_seconds
                            }
                            ariaLabel={roundAudioLabel(activeRound)}
                            hasAudio
                            remotePlayback={effectiveRemotePlayback}
                        />
                    ) : null}

                    {room.viewer_participant_id && !myAnswerForActiveRound ? (
                        activeRound.question.question_type ===
                        QuestionType.MultipleChoice ? (
                            <div className="flex flex-col gap-2">
                                {activeRound.question.multiple_choice_options.map(
                                    (opt) => (
                                        <label
                                            key={opt.id}
                                            className={cn(
                                                'flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm dark:border-white/15',
                                                selectedOptionId === opt.id
                                                    ? 'border-primary bg-primary/10'
                                                    : 'border-transparent bg-black/5 dark:bg-white/5',
                                            )}
                                        >
                                            <input
                                                type="radio"
                                                name="mc-option"
                                                className="accent-primary"
                                                aria-label={opt.option_text}
                                                checked={
                                                    selectedOptionId === opt.id
                                                }
                                                onChange={() =>
                                                    setSelectedOptionId(opt.id)
                                                }
                                            />
                                            <span>{opt.option_text}</span>
                                        </label>
                                    ),
                                )}
                                <Button
                                    type="button"
                                    className="mt-2 self-start"
                                    disabled={submitting || !canSubmitMc}
                                    onClick={() => void handleSubmitAnswer()}
                                >
                                    {submitting
                                        ? 'Submitting…'
                                        : 'Submit answer'}
                                </Button>
                            </div>
                        ) : (
                            <div className="flex flex-col gap-2">
                                <input
                                    type="text"
                                    value={textAnswer}
                                    onChange={(e) =>
                                        setTextAnswer(e.target.value)
                                    }
                                    className="bg-background border-border focus:ring-primary w-full rounded-md border px-3 py-2 text-sm outline-none focus:ring-2"
                                    placeholder="Your answer"
                                    autoComplete="off"
                                />
                                <Button
                                    type="button"
                                    className="self-start"
                                    disabled={submitting || !canSubmitText}
                                    onClick={() => void handleSubmitAnswer()}
                                >
                                    {submitting
                                        ? 'Submitting…'
                                        : 'Submit answer'}
                                </Button>
                            </div>
                        )
                    ) : null}

                    {room.viewer_participant_id && myAnswerForActiveRound ? (
                        <p className="text-muted text-sm">
                            You have submitted for this round.
                            {myAnswerForActiveRound.is_correct === true
                                ? ' Nice — that was correct.'
                                : null}
                            {myAnswerForActiveRound.is_correct === false
                                ? ' That was not correct.'
                                : null}
                            {myAnswerForActiveRound.is_correct === null
                                ? ' Results will show when the round closes.'
                                : null}
                        </p>
                    ) : null}

                    {!room.viewer_participant_id && !room.viewer_is_host ? (
                        <p className="text-muted text-sm">
                            You are watching without a seat (the game has
                            already started, so joining from here is closed).
                            Ask the host to open a new lobby game if you want to
                            play.
                        </p>
                    ) : null}

                    {activeRound.answers.length > 0 ? (
                        <div className="mt-4 border-t border-black/10 pt-4 dark:border-white/10">
                            <h3 className="mb-2 text-sm font-medium">
                                Answers this round
                            </h3>
                            <ul className="flex flex-col gap-2">
                                {activeRound.answers.map((a) => (
                                    <li
                                        key={a.id}
                                        className="text-muted text-sm"
                                    >
                                        <span className="text-foreground font-medium">
                                            {a.participant_display_name}
                                        </span>
                                        {a.selected_option_id ? (
                                            <span className="font-mono">
                                                {' '}
                                                · option{' '}
                                                {a.selected_option_id.slice(
                                                    0,
                                                    8,
                                                )}
                                                …
                                            </span>
                                        ) : null}
                                        {a.submitted_text ? (
                                            <span> · “{a.submitted_text}”</span>
                                        ) : null}
                                        {a.is_correct === true ? (
                                            <span className="text-success ml-1">
                                                · correct
                                            </span>
                                        ) : null}
                                        {a.is_correct === false ? (
                                            <span className="text-error ml-1">
                                                · incorrect
                                            </span>
                                        ) : null}
                                        {a.points_awarded != null &&
                                        a.points_awarded > 0 ? (
                                            <span className="ml-1">
                                                · +{a.points_awarded} pts
                                            </span>
                                        ) : null}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ) : null}
                </Surface>
            ) : null}

            {core.status === 'in_progress' && !activeRound ? (
                <Surface variant="tint" className="mb-8">
                    <p className="text-muted text-sm">
                        No active round (loading or transitioning). Try
                        refreshing.
                    </p>
                </Surface>
            ) : null}

            {isCompleted ? (
                <Surface variant="elevated" className="mb-8 p-6 sm:p-8">
                    <p className="text-primary mb-1 text-sm font-semibold tracking-wide uppercase">
                        Game finished
                    </p>
                    <h2 className="mb-3 text-xl font-bold">Results</h2>
                    <p className="text-muted mb-6 text-sm">
                        Started {formatSessionInstant(core.started_at)} · Ended{' '}
                        {formatSessionInstant(core.ended_at)}
                    </p>

                    <h3 className="mb-2 font-semibold">Final scores</h3>
                    {sortedParticipants.length > 0 ? (
                        <ol className="mb-8 flex flex-col gap-2">
                            {sortedParticipants.map((p, index) => {
                                const isViewerRow =
                                    room.viewer_participant_id !== null &&
                                    p.id === room.viewer_participant_id;
                                return (
                                    <li
                                        key={p.id}
                                        className={cn(
                                            'flex items-center justify-between rounded-md border border-transparent bg-black/5 px-3 py-2 text-sm dark:bg-white/5',
                                            isViewerRow &&
                                                'border-primary/50 bg-primary/10 dark:bg-primary/15',
                                        )}
                                    >
                                        <span>
                                            <span className="text-muted mr-2 font-mono">
                                                #{index + 1}
                                            </span>
                                            {participantSeatLabel(p)}
                                            {p.user_id === core.host_id ? (
                                                <span className="text-muted">
                                                    {' '}
                                                    (host)
                                                </span>
                                            ) : null}
                                            {isViewerRow ? (
                                                <span className="text-primary ml-1.5 text-xs font-semibold tracking-wide">
                                                    (you)
                                                </span>
                                            ) : null}
                                        </span>
                                        <span className="font-mono font-semibold">
                                            {p.current_total_score} pts
                                        </span>
                                    </li>
                                );
                            })}
                        </ol>
                    ) : (
                        <p className="text-muted mb-8 text-sm">
                            No scoreboard yet—when players join, they show up
                            here.
                        </p>
                    )}

                    <h3 className="mb-3 font-semibold">Rounds</h3>
                    <ul className="flex flex-col gap-4">
                        {sortedRounds.map((round) => (
                            <li key={round.id}>
                                <Surface variant="tint" className="p-4">
                                    <p className="text-muted mb-1 text-xs font-medium uppercase">
                                        Round {round.round_number}
                                    </p>
                                    <p className="mb-2 text-sm font-medium">
                                        {round.question.prompt_text?.trim() ??
                                            'Question'}
                                    </p>
                                    {round.question.correct_answer ? (
                                        <p className="text-muted mb-3 text-sm">
                                            Correct answer:{' '}
                                            <span className="text-foreground font-medium">
                                                {round.question.correct_answer}
                                            </span>
                                        </p>
                                    ) : null}
                                    {round.question.audio_upload_available ? (
                                        <SessionRoundMediaPlayer
                                            sessionId={core.id}
                                            roundId={round.id}
                                            variant="recap"
                                            mediaStartSeconds={
                                                round.question
                                                    .media_start_seconds
                                            }
                                            mediaEndSeconds={
                                                round.question.media_end_seconds
                                            }
                                            ariaLabel={roundAudioLabel(round)}
                                            hasAudio
                                            remotePlayback={null}
                                        />
                                    ) : null}
                                    {round.answers.length > 0 ? (
                                        <ul className="flex flex-col gap-1.5">
                                            {round.answers.map((a) => {
                                                const isViewerAnswer =
                                                    room.viewer_participant_id !==
                                                        null &&
                                                    a.participant_id ===
                                                        room.viewer_participant_id;
                                                return (
                                                    <li
                                                        key={a.id}
                                                        className={cn(
                                                            'text-muted rounded-md px-3 py-1.5 text-sm',
                                                            isViewerAnswer &&
                                                                'border-primary/55 bg-primary/10 text-foreground dark:bg-primary/15 border-l-4 font-medium',
                                                        )}
                                                    >
                                                        <span className="text-foreground font-medium">
                                                            {
                                                                a.participant_display_name
                                                            }
                                                            {isViewerAnswer ? (
                                                                <span className="text-primary ml-1.5 text-xs font-semibold tracking-wide">
                                                                    (you)
                                                                </span>
                                                            ) : null}
                                                        </span>
                                                        {multipleChoiceAnswerLabel(
                                                            round,
                                                            a.selected_option_id,
                                                        ) ? (
                                                            <span>
                                                                {' '}
                                                                ·{' '}
                                                                {multipleChoiceAnswerLabel(
                                                                    round,
                                                                    a.selected_option_id,
                                                                )}
                                                            </span>
                                                        ) : null}
                                                        {a.submitted_text ? (
                                                            <span>
                                                                {' '}
                                                                · “
                                                                {
                                                                    a.submitted_text
                                                                }
                                                                ”
                                                            </span>
                                                        ) : null}
                                                        {a.is_correct ===
                                                        true ? (
                                                            <span className="text-success">
                                                                {' '}
                                                                · correct
                                                            </span>
                                                        ) : null}
                                                        {a.is_correct ===
                                                        false ? (
                                                            <span className="text-error">
                                                                {' '}
                                                                · incorrect
                                                            </span>
                                                        ) : null}
                                                        {a.points_awarded !=
                                                            null &&
                                                        a.points_awarded > 0 ? (
                                                            <span>
                                                                {' '}
                                                                · +
                                                                {
                                                                    a.points_awarded
                                                                }{' '}
                                                                pts
                                                            </span>
                                                        ) : null}
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    ) : (
                                        <p className="text-muted text-sm">
                                            No answers recorded.
                                        </p>
                                    )}
                                </Surface>
                            </li>
                        ))}
                    </ul>
                </Surface>
            ) : null}

            {!isCompleted ? (
                <Surface variant="tint" className="mb-8">
                    <h2 className="mb-2 font-semibold">Scores</h2>
                    {sortedParticipants.length > 0 ? (
                        <ul className="flex flex-col gap-2">
                            {sortedParticipants.map((p) => (
                                <li
                                    key={p.id}
                                    className="flex items-center justify-between text-sm"
                                >
                                    <span>
                                        {participantSeatLabel(p)}
                                        {p.user_id === core.host_id ? (
                                            <span className="text-muted">
                                                {' '}
                                                (host)
                                            </span>
                                        ) : null}
                                    </span>
                                    <span className="font-mono font-medium">
                                        {p.current_total_score}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-muted text-sm">
                            No players yet—share the invite when you are ready.
                        </p>
                    )}
                </Surface>
            ) : null}

            {!isCompleted && room.viewer_participant_id !== null ? (
                <Button
                    type="button"
                    variant="secondary"
                    disabled={leaving}
                    onClick={() => void handleLeave()}
                >
                    {leaving ? 'Leaving…' : 'Leave session'}
                </Button>
            ) : null}
        </PageShell>
    );
}
