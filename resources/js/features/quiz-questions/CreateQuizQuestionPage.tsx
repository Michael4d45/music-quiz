import SearchSelect from '@/components/Select/SearchSelect';
import { ApiClient } from '@/lib/apiClient';
import { authManager } from '@/lib/auth';
import { useEffect, useState } from 'react';
import { redirect, useNavigate } from 'react-router-dom';

export async function createQuizQuestionLoader() {
    let user = authManager.getUser();
    let token = authManager.getToken();

    // Try to restore from session if no local token
    if (!user || !token) {
        const result = await ApiClient.fetchSessionToken();
        if (result._tag === 'Success') {
            authManager.setAuthData(result.data.token, result.data.user);
            user = result.data.user;
            token = result.data.token;
        }
    }

    if (!user || !token) {
        return redirect('/login');
    }

    return {};
}

export function CreateQuizQuestionPage() {
    const navigate = useNavigate();
    const [isCreating, setIsCreating] = useState(false);
    const [tracks, setTracks] = useState<{ value: string; label: string }[]>(
        [],
    );
    const [loading, setLoading] = useState(true);
    const [errors, setErrors] = useState<Record<string, string[]>>({});

    useEffect(() => {
        const loadData = async () => {
            try {
                const result = await ApiClient.showUserMusicTracks();

                if (result._tag === 'Success') {
                    const options = result.data.tracks.map((track) => ({
                        value: track.id.toString(),
                        label: `${track.title} - ${track.artist_name}`,
                    }));
                    setTracks(options);
                }
            } catch (error) {
                console.error('Error loading tracks:', error);
            } finally {
                setLoading(false);
            }
        };

        loadData();
    }, []);

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        setIsCreating(true);
        setErrors({});

        try {
            const formData = new FormData(e.currentTarget);

            const payload = {
                question_type: formData.get('question_type') as any,
                correct_answer: formData.get('correct_answer') as string,
                track_id: (formData.get('track_id') as string) || null,
                prompt_text: (formData.get('prompt_text') as string) || null,
                base_points:
                    parseInt(formData.get('base_points') as string) || 1000,
                media_start_seconds: formData.get('media_start_seconds')
                    ? parseInt(formData.get('media_start_seconds') as string)
                    : null,
                media_end_seconds: formData.get('media_end_seconds')
                    ? parseInt(formData.get('media_end_seconds') as string)
                    : null,
                difficulty_level:
                    parseInt(formData.get('difficulty_level') as string) || 1,
                visibility: formData.get('visibility') as any,
            };

            const result = await ApiClient.createQuizQuestion(payload);

            if (result._tag === 'Success') {
                navigate('/quiz-questions');
            } else if (result._tag === 'ValidationError') {
                setErrors(
                    Object.fromEntries(
                        Object.entries(result.errors).map(([key, value]) => [
                            key,
                            [...value],
                        ]),
                    ),
                );
            } else {
                console.error('Failed to create quiz question:', result);
            }
        } catch (error) {
            console.error('Error creating quiz question:', error);
        } finally {
            setIsCreating(false);
        }
    };

    if (loading) {
        return (
            <div className="container mx-auto px-4 py-8">
                <div className="text-center">Loading...</div>
            </div>
        );
    }

    return (
        <div className="container mx-auto px-4 py-8">
            <div className="mx-auto max-w-2xl">
                <h1 className="mb-8 text-center text-3xl font-bold">
                    Create Quiz Question
                </h1>

                <form
                    onSubmit={handleSubmit}
                    className="bg-card space-y-4 rounded-lg p-6 shadow-lg"
                >
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label
                                htmlFor="question_type"
                                className="mb-2 block text-sm font-medium"
                            >
                                Question Type *
                            </label>
                            <select
                                id="question_type"
                                name="question_type"
                                defaultValue="artist"
                                className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                                required
                            >
                                <option value="artist">Artist Name</option>
                                <option value="title">Song Title</option>
                                <option value="year">Release Year</option>
                                <option value="multiple_choice">
                                    Multiple Choice
                                </option>
                                <option value="lyric">Lyric</option>
                                <option value="audio_clip">Audio Clip</option>
                            </select>
                            {errors.question_type && (
                                <p className="mt-1 text-sm text-red-500">
                                    {errors.question_type[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="difficulty_level"
                                className="mb-2 block text-sm font-medium"
                            >
                                Difficulty Level *
                            </label>
                            <select
                                id="difficulty_level"
                                name="difficulty_level"
                                defaultValue={1}
                                className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            >
                                <option value={1}>Easy</option>
                                <option value={2}>Medium</option>
                                <option value={3}>Hard</option>
                                <option value={4}>Expert</option>
                                <option value={5}>Master</option>
                            </select>
                            {errors.difficulty_level && (
                                <p className="mt-1 text-sm text-red-500">
                                    {errors.difficulty_level[0]}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <label
                            htmlFor="correct_answer"
                            className="mb-2 block text-sm font-medium"
                        >
                            Correct Answer *
                        </label>
                        <input
                            type="text"
                            id="correct_answer"
                            name="correct_answer"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            placeholder="The correct answer to the question"
                            required
                        />
                        {errors.correct_answer && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.correct_answer[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium">
                            Associated Track (Optional)
                        </label>
                        <SearchSelect
                            name="track_id"
                            placeholder="Select a track"
                            options={tracks}
                        />
                        {errors.track_id && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.track_id[0]}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="prompt_text"
                            className="mb-2 block text-sm font-medium"
                        >
                            Prompt Text
                        </label>
                        <textarea
                            id="prompt_text"
                            name="prompt_text"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            placeholder="Additional context or question text..."
                            rows={3}
                        />
                        {errors.prompt_text && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.prompt_text[0]}
                            </p>
                        )}
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label
                                htmlFor="base_points"
                                className="mb-2 block text-sm font-medium"
                            >
                                Base Points *
                            </label>
                            <input
                                type="number"
                                id="base_points"
                                name="base_points"
                                min="1"
                                max="10000"
                                defaultValue={1000}
                                className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            />
                            {errors.base_points && (
                                <p className="mt-1 text-sm text-red-500">
                                    {errors.base_points[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="media_start_seconds"
                                className="mb-2 block text-sm font-medium"
                            >
                                Start Time (sec)
                            </label>
                            <input
                                type="number"
                                id="media_start_seconds"
                                name="media_start_seconds"
                                min="0"
                                className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            />
                            {errors.media_start_seconds && (
                                <p className="mt-1 text-sm text-red-500">
                                    {errors.media_start_seconds[0]}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="media_end_seconds"
                                className="mb-2 block text-sm font-medium"
                            >
                                End Time (sec)
                            </label>
                            <input
                                type="number"
                                id="media_end_seconds"
                                name="media_end_seconds"
                                min="0"
                                className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                            />
                            {errors.media_end_seconds && (
                                <p className="mt-1 text-sm text-red-500">
                                    {errors.media_end_seconds[0]}
                                </p>
                            )}
                        </div>
                    </div>

                    <div>
                        <label
                            htmlFor="visibility"
                            className="mb-2 block text-sm font-medium"
                        >
                            Visibility *
                        </label>
                        <select
                            id="visibility"
                            name="visibility"
                            defaultValue="public"
                            className="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-(--primary) focus:outline-none"
                        >
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                        {errors.visibility && (
                            <p className="mt-1 text-sm text-red-500">
                                {errors.visibility[0]}
                            </p>
                        )}
                    </div>

                    <div className="flex gap-4 pt-4">
                        <button
                            type="button"
                            onClick={() => navigate('/quiz-questions')}
                            className="btn-secondary flex-1 rounded-lg py-3 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={isCreating}
                            className="btn-success flex-1 rounded-lg py-3 transition-colors disabled:opacity-50"
                        >
                            {isCreating ? 'Creating...' : 'Create Question'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
