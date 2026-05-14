import { PageIntroExpandable } from '@/components/PageIntroExpandable';
import { Button } from '@/components/ui/Button';
import { ButtonLink } from '@/components/ui/ButtonLink';
import { useAuth } from '@/features/auth/AuthContext';
import { isRegisteredUser } from '@/features/auth/authSession';
import {
    advanceGameSessionRound,
    leaveGameSession,
    startGameSession,
    submitSessionRoundAnswer,
} from '@/features/game-sessions/api';
import { useGameSessionChannel } from '@/hooks/useGameSessionChannel';
import { apiFailureMessage } from '@/lib/apiCore';
import { cn } from '@/lib/utils';
import { QuestionType } from '@/schemas/App/Enums/QuestionType';
import type { GameSessionRoomViewData } from '@/schemas/App/Data/Responses/GameSessionRoomViewData';
import type { SessionRoundGameplayData } from '@/schemas/App/Data/Responses/SessionRoundGameplayData';
import { useCallback, useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import {
    useLoaderData,
    useNavigate,
    useParams,
    useRevalidator,
} from 'react-router-dom';

export { gameSessionRoomLoader } from '@/features/game-sessions/gameSessionRoomLoader';

function findActiveRound(
    rounds: readonly SessionRoundGameplayData[],
): SessionRoundGameplayData | null {
    return (
        rounds.find((r) => r.started_at != null && r.ended_at == null) ?? null
    );
}

export function GameSessionRoomPage() {
    const { user } = useAuth();
    const room = useLoaderData<GameSessionRoomViewData>();
    const core = room.session;
    const params = useParams();
    const navigate = useNavigate();
    const revalidator = useRevalidator();
    const roomCode = params.roomCode ?? core.room_code;

    const revalidateRoom = () => {
        revalidator.revalidate();
    };

    const { participantCount } = useGameSessionChannel(core.id, {
        onSessionUpdated: revalidateRoom,
    });

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

    const activeRound = findActiveRound(room.rounds);

    const [leaving, setLeaving] = useState(false);
    const [starting, setStarting] = useState(false);
    const [advancing, setAdvancing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [selectedOptionId, setSelectedOptionId] = useState<string | null>(
        null,
    );
    const [textAnswer, setTextAnswer] = useState('');

    const handleLeave = async () => {
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
        <div className="mx-auto max-w-4xl px-4 py-6">
            <div className="mb-6 flex flex-wrap items-center gap-3">
                <ButtonLink to="/game-sessions/lobby" variant="secondary">
                    Lobby
                </ButtonLink>
                {isRegisteredUser(user) ? (
                    <ButtonLink to="/my/game-sessions" variant="secondary">
                        My sessions
                    </ButtonLink>
                ) : null}
            </div>

            <h1 className="mb-2 text-2xl font-bold">Room {roomCode}</h1>
            <p className="text-muted mb-6 text-sm">
                Status: {core.status} · Players: {displayCount} /{' '}
                {core.max_players}
                {room.viewer_is_host ? (
                    <span className="text-muted"> · You are the host</span>
                ) : null}
                {room.viewer_participant_id ? (
                    <span className="text-muted"> · You are playing</span>
                ) : null}
            </p>

            <PageIntroExpandable
                summary="Live session room: host can start the quiz and advance rounds; players submit answers when a round is open."
                moreLabel="How play works"
            >
                <p className="text-muted text-sm">
                    Join while the session is in the lobby. When the host starts, the
                    host also gets a player seat so you can rehearse solo. Have guests
                    join from the lobby before start if they need a seat. Questions follow
                    the playlist order (up to ten rounds). After you submit, other players
                    will not see your answer until the round closes. The host advances when
                    you are ready for the next question.
                </p>
            </PageIntroExpandable>

            {core.status === 'lobby' && room.viewer_is_host ? (
                <div className="bg-card mb-6 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
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
                </div>
            ) : null}

            {core.status === 'in_progress' && activeRound ? (
                <div className="bg-card mb-6 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
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
                        {activeRound.question.question_type.replaceAll('_', ' ')}
                    </p>
                    <p className="mb-4 text-base">
                        {activeRound.question.prompt_text?.trim() ||
                            'Question'}
                    </p>

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
                                    {submitting ? 'Submitting…' : 'Submit answer'}
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
                                    {submitting ? 'Submitting…' : 'Submit answer'}
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
                            You are viewing as a guest without a seat in this game.
                            Join from the lobby while the session is open.
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
                                                · option {a.selected_option_id.slice(0, 8)}…
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
                </div>
            ) : null}

            {core.status === 'in_progress' && !activeRound ? (
                <div className="bg-card mb-6 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                    <p className="text-muted text-sm">
                        No active round (loading or transitioning). Try refreshing.
                    </p>
                </div>
            ) : null}

            {core.status === 'completed' ? (
                <div className="bg-card mb-6 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                    <h2 className="mb-2 font-semibold">Game complete</h2>
                    <p className="text-muted text-sm">
                        Final scores are below. Thanks for playing.
                    </p>
                </div>
            ) : null}

            <div className="bg-card mb-6 rounded-lg border border-transparent p-4 shadow-md dark:border-white/10">
                <h2 className="mb-2 font-semibold">Scores</h2>
                {sortedParticipants.length > 0 ? (
                    <ul className="flex flex-col gap-2">
                        {sortedParticipants.map((p) => (
                            <li
                                key={p.id}
                                className="flex items-center justify-between text-sm"
                            >
                                <span>
                                    {p.user?.name ?? 'Player'}
                                    {p.user_id === core.host_id ? (
                                        <span className="text-muted"> (host)</span>
                                    ) : null}
                                </span>
                                <span className="font-mono font-medium">
                                    {p.current_total_score}
                                </span>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <p className="text-muted text-sm">No participants yet.</p>
                )}
            </div>

            <Button
                type="button"
                variant="secondary"
                disabled={leaving}
                onClick={() => void handleLeave()}
            >
                {leaving ? 'Leaving…' : 'Leave session'}
            </Button>
        </div>
    );
}
