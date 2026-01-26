import { ApiClient } from '@/lib/apiClient';
import { echo } from '@/lib/echo';
import { SessionPlayResponse } from '@/schemas/App/Data/Response';
import { useEffect, useState } from 'react';
import { useLoaderData, useNavigate, useParams } from 'react-router-dom';

export async function sessionPlayLoader({ params }: any) {
    const result = await ApiClient.showSessionPlay(params.roomCode);
    if (result._tag === 'Success') {
        return result.data;
    }
    throw new Error('Failed to load session play data');
}

export function SessionPlayPage() {
    const initialData = useLoaderData<SessionPlayResponse>();
    const { roomCode } = useParams<{ roomCode: string }>();
    const navigate = useNavigate();

    const [data, setData] = useState(initialData);
    const [answer, setAnswer] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [result, setResult] = useState<{
        is_correct: boolean;
        points: number;
    } | null>(null);
    const [timeLeft, setTimeLeft] = useState<number | null>(null);

    const isHost =
        data.participant?.user_id === (data.round as any)?.session?.host_id;

    useEffect(() => {
        if (!roomCode) return;

        const channel = echo.join(`session.${roomCode}`);

        channel.listen('RoundStarted', (event: any) => {
            ApiClient.showSessionPlay(roomCode).then((res) => {
                if (res._tag === 'Success') {
                    setData(res.data);
                    setAnswer('');
                    setResult(null);
                }
            });
        });

        channel.listen('RoundEnded', (event: any) => {
            // Update time left or show that round ended
            setTimeLeft(0);
        });

        channel.listen('GameCompleted', (event: any) => {
            navigate(`/sessions/${roomCode}/results`);
        });

        return () => {
            echo.leave(`session.${roomCode}`);
        };
    }, [roomCode, navigate]);

    useEffect(() => {
        if (!data.round?.started_at || !data.scoring_rule?.max_time_ms) {
            setTimeLeft(null);
            return;
        }

        const maxTime = data.scoring_rule.max_time_ms;
        const startedAt = new Date(data.round.started_at).getTime();

        const interval = setInterval(() => {
            const now = new Date().getTime();
            const elapsed = now - startedAt;
            const remaining = Math.max(0, maxTime - elapsed);
            setTimeLeft(remaining);

            if (remaining <= 0) {
                clearInterval(interval);
            }
        }, 100);

        return () => clearInterval(interval);
    }, [data.round]);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!roomCode || !answer || isSubmitting || result) return;

        setIsSubmitting(true);
        try {
            const res = await ApiClient.submitAnswer(roomCode, answer);
            if (res._tag === 'Success') {
                setResult({
                    is_correct: res.data.is_correct,
                    points: res.data.points_awarded,
                });
            }
        } catch (error) {
            console.error('Error submitting answer:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleNextRound = async () => {
        if (!roomCode) return;
        await ApiClient.nextRound(roomCode);
    };

    if (!data.round || !data.question) {
        return <div className="p-8 text-center">Loading round...</div>;
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-2xl">
                <div className="mb-8 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">
                        Round {data.round.round_number}
                    </h1>
                    {timeLeft !== null && (
                        <div
                            className={`font-mono text-xl ${timeLeft < 5000 ? 'animate-pulse text-red-500' : ''}`}
                        >
                            {(timeLeft / 1000).toFixed(1)}s
                        </div>
                    )}
                </div>

                <div className="bg-card mb-8 rounded-lg p-8 shadow-xl">
                    <div className="mb-6 text-center">
                        <p className="text-muted mb-2 text-sm tracking-widest uppercase">
                            Question
                        </p>
                        <h2 className="text-3xl font-semibold">
                            {data.question.prompt_text ||
                                'Identify the artist/track'}
                        </h2>
                    </div>

                    {result ? (
                        <div
                            className={`rounded-lg p-6 text-center ${result.is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}
                        >
                            <p className="mb-2 text-2xl font-bold">
                                {result.is_correct ? 'Correct!' : 'Incorrect'}
                            </p>
                            <p className="text-lg">
                                {result.is_correct
                                    ? `+${result.points} points`
                                    : `Correct answer: ${data.question.correct_answer}`}
                            </p>
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <input
                                type="text"
                                value={answer}
                                onChange={(e) => setAnswer(e.target.value)}
                                placeholder="Type your answer here..."
                                autoComplete="off"
                                autoFocus
                                className="bg-muted focus:ring-primary w-full rounded-lg border-none p-4 text-center text-xl focus:ring-2"
                                disabled={isSubmitting || timeLeft === 0}
                            />
                            <button
                                type="submit"
                                disabled={
                                    isSubmitting || !answer || timeLeft === 0
                                }
                                className="bg-primary text-primary-foreground w-full rounded-lg py-4 font-bold transition-opacity hover:opacity-90 disabled:opacity-50"
                            >
                                {isSubmitting
                                    ? 'Submitting...'
                                    : 'Submit Answer'}
                            </button>
                        </form>
                    )}
                </div>

                <div className="bg-card rounded-lg p-6 shadow-lg">
                    <div className="mb-4 flex items-center justify-between">
                        <h3 className="text-lg font-semibold">Your Score</h3>
                        <span className="font-mono text-2xl">
                            {data.participant?.current_total_score || 0}
                        </span>
                    </div>
                </div>

                {isHost && (
                    <div className="mt-8 flex justify-center">
                        <button
                            onClick={handleNextRound}
                            className="bg-secondary text-secondary-foreground rounded-lg px-8 py-3 font-semibold transition-opacity hover:opacity-90"
                        >
                            Next Round / End Game
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
