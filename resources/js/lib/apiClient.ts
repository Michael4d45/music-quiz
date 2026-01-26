import { UserDataSchema } from '@/schemas/App/Data/Models';
import {
    CreateMusicTrackRequest,
    CreatePlaylistRequest,
    CreateQuizQuestionRequest,
    CreateSessionRequest,
    JoinSessionRequest,
    LoginRequest,
    RegisterRequest,
    ResetPasswordRequest,
    StartGameRequest,
    SubmitAnswerRequest,
    UpdatePlaylistRequest,
} from '@/schemas/App/Data/Requests';
import {
    ActiveGamesResponseSchema,
    AuthenticateBroadcastingResponseSchema,
    AuthResponseSchema,
    BrowseResponseSchema,
    CategoriesResponseSchema,
    CategoryResponseSchema,
    DisconnectGoogleResponseSchema,
    HomeResponseSchema,
    LeaderboardResponseSchema,
    MessageResponseSchema,
    MusicSourcesResponseSchema,
    MusicTrackResponseSchema,
    MusicTracksResponseSchema,
    PlaylistResponseSchema,
    PlaylistsResponseSchema,
    QuizModesResponseSchema,
    QuizQuestionResponseSchema,
    QuizQuestionsResponseSchema,
    ScoringRulesResponseSchema,
    SessionLobbyResponseSchema,
    SessionPlayResponseSchema,
    SessionResultsResponseSchema,
    StatisticsResponseSchema,
    SubCategoriesResponseSchema,
    SubmitAnswerResponseSchema,
    TrackResponseSchema,
    TracksResponseSchema,
    UserMusicTracksResponseSchema,
    UserPlaylistsResponseSchema,
} from '@/schemas/App/Data/Response';
import { Effect, pipe } from 'effect';
import { apiCache } from './apiCache';
import { ensureCsrfToken, httpRequest, runEffect, withRetry } from './apiCore';

/* ============================================================================
 * API Client Singleton
 * ============================================================================ */

class ApiClientSingleton {
    private csrfInitialized = false;

    private async ensureCsrf(): Promise<void> {
        if (!this.csrfInitialized) {
            await Effect.runPromise(ensureCsrfToken);
            this.csrfInitialized = true;
        }
    }

    /* ==========================================================================
     * Auth Methods
     * ========================================================================== */

    async login(payload: LoginRequest) {
        await this.ensureCsrf();

        const effect = pipe(
            httpRequest(
                '/login',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'login'),
        );

        return runEffect(effect, 'login');
    }

    async register(payload: RegisterRequest) {
        await this.ensureCsrf();

        const effect = pipe(
            httpRequest(
                '/register',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'register'),
        );

        return runEffect(effect, 'register');
    }

    async logout() {
        const effect = pipe(
            httpRequest(
                '/api/logout',
                {
                    method: 'POST',
                },
                MessageResponseSchema,
            ),
            Effect.tap(() => Effect.promise(() => apiCache.clear())),
        );

        return runEffect(effect, 'logout');
    }

    async sendPasswordResetLink(email: string) {
        await this.ensureCsrf();

        const effect = pipe(
            httpRequest(
                '/api/send-password-reset-link',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email }),
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'sendPasswordResetLink'),
        );

        return runEffect(effect, 'sendPasswordResetLink');
    }

    async resetPassword(payload: ResetPasswordRequest) {
        await this.ensureCsrf();

        const effect = pipe(
            httpRequest(
                '/api/reset-password',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'resetPassword'),
        );

        return runEffect(effect, 'resetPassword');
    }

    async resendVerificationEmail() {
        const effect = pipe(
            httpRequest(
                '/api/send-email-verification-notification',
                {
                    method: 'POST',
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'resendVerificationEmail'),
        );

        return runEffect(effect, 'resendVerificationEmail');
    }

    async disconnectGoogle() {
        const effect = pipe(
            httpRequest(
                '/api/disconnect-google',
                {
                    method: 'POST',
                },
                DisconnectGoogleResponseSchema,
            ),
            (eff) => withRetry(eff, 'disconnectGoogle'),
        );

        return runEffect(effect, 'disconnectGoogle');
    }

    /* ==========================================================================
     * User Methods
     * ========================================================================== */

    async showUser() {
        const effect = pipe(
            httpRequest(
                '/api/user',
                {
                    method: 'GET',
                },
                UserDataSchema,
            ),
            (eff) => withRetry(eff, 'showUser'),
        );

        return runEffect(effect, 'showUser');
    }

    /* ==========================================================================
     * Home and Browse Methods
     * ========================================================================== */

    async showHome() {
        const effect = pipe(
            httpRequest(
                '/api/home',
                {
                    method: 'GET',
                },
                HomeResponseSchema,
            ),
            (eff) => withRetry(eff, 'showHome'),
        );

        return runEffect(effect, 'showHome');
    }

    async showBrowse() {
        const effect = pipe(
            httpRequest(
                '/api/browse',
                {
                    method: 'GET',
                },
                BrowseResponseSchema,
            ),
            (eff) => withRetry(eff, 'showBrowse'),
        );

        return runEffect(effect, 'showBrowse');
    }

    async showCategories() {
        const effect = pipe(
            httpRequest(
                '/api/browse/categories',
                {
                    method: 'GET',
                },
                CategoriesResponseSchema,
            ),
            (eff) => withRetry(eff, 'showCategories'),
        );

        return runEffect(effect, 'showCategories');
    }

    async showCategory(category: string) {
        const effect = pipe(
            httpRequest(
                `/api/browse/categories/${category}`,
                {
                    method: 'GET',
                },
                CategoryResponseSchema,
            ),
            (eff) => withRetry(eff, 'showCategory'),
        );

        return runEffect(effect, 'showCategory');
    }

    async showTracks() {
        const effect = pipe(
            httpRequest(
                '/api/browse/tracks',
                {
                    method: 'GET',
                },
                TracksResponseSchema,
            ),
            (eff) => withRetry(eff, 'showTracks'),
        );

        return runEffect(effect, 'showTracks');
    }

    async showTrack(track: string) {
        const effect = pipe(
            httpRequest(
                `/api/browse/tracks/${track}`,
                {
                    method: 'GET',
                },
                TrackResponseSchema,
            ),
            (eff) => withRetry(eff, 'showTrack'),
        );

        return runEffect(effect, 'showTrack');
    }

    async showPublicPlaylists() {
        const effect = pipe(
            httpRequest(
                '/api/browse/playlists',
                {
                    method: 'GET',
                },
                PlaylistsResponseSchema,
            ),
            (eff) => withRetry(eff, 'showPublicPlaylists'),
        );

        return runEffect(effect, 'showPublicPlaylists');
    }

    /* ==========================================================================
     * Statistics Methods
     * ========================================================================== */

    async showLeaderboard() {
        const effect = pipe(
            httpRequest(
                '/api/leaderboard',
                {
                    method: 'GET',
                },
                LeaderboardResponseSchema,
            ),
            (eff) => withRetry(eff, 'showLeaderboard'),
        );

        return runEffect(effect, 'showLeaderboard');
    }

    async showStatistics() {
        const effect = pipe(
            httpRequest(
                '/api/statistics',
                {
                    method: 'GET',
                },
                StatisticsResponseSchema,
            ),
            (eff) => withRetry(eff, 'showStatistics'),
        );

        return runEffect(effect, 'showStatistics');
    }

    /* ==========================================================================
     * Music and Quiz Methods
     * ========================================================================== */

    async showSubCategories() {
        const effect = pipe(
            httpRequest(
                '/api/sub-categories',
                {
                    method: 'GET',
                },
                SubCategoriesResponseSchema,
            ),
            (eff) => withRetry(eff, 'showSubCategories'),
        );

        return runEffect(effect, 'showSubCategories');
    }

    async showMusicSources() {
        const effect = pipe(
            httpRequest(
                '/api/music-sources',
                {
                    method: 'GET',
                },
                MusicSourcesResponseSchema,
            ),
            (eff) => withRetry(eff, 'showMusicSources'),
        );

        return runEffect(effect, 'showMusicSources');
    }

    async showQuizModes() {
        const effect = pipe(
            httpRequest(
                '/api/quiz-modes',
                {
                    method: 'GET',
                },
                QuizModesResponseSchema,
            ),
            (eff) => withRetry(eff, 'showQuizModes'),
        );

        return runEffect(effect, 'showQuizModes');
    }

    async showScoringRules() {
        const effect = pipe(
            httpRequest(
                '/api/scoring-rules',
                {
                    method: 'GET',
                },
                ScoringRulesResponseSchema,
            ),
            (eff) => withRetry(eff, 'showScoringRules'),
        );

        return runEffect(effect, 'showScoringRules');
    }

    /* ==========================================================================
     * Playlist Methods
     * ========================================================================== */

    async listPlaylists() {
        const effect = pipe(
            httpRequest(
                '/api/playlists',
                {
                    method: 'GET',
                },
                PlaylistsResponseSchema,
            ),
            (eff) => withRetry(eff, 'listPlaylists'),
        );

        return runEffect(effect, 'listPlaylists');
    }

    async createPlaylist(payload: CreatePlaylistRequest) {
        const effect = pipe(
            httpRequest(
                '/api/playlists',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                PlaylistResponseSchema,
            ),
            (eff) => withRetry(eff, 'createPlaylist'),
        );

        return runEffect(effect, 'createPlaylist');
    }

    async showPlaylist(playlist: string) {
        const effect = pipe(
            httpRequest(
                `/api/playlists/${playlist}`,
                {
                    method: 'GET',
                },
                PlaylistResponseSchema,
            ),
            (eff) => withRetry(eff, 'showPlaylist'),
        );

        return runEffect(effect, 'showPlaylist');
    }

    async updatePlaylist(playlist: string, payload: UpdatePlaylistRequest) {
        const effect = pipe(
            httpRequest(
                `/api/playlists/${playlist}`,
                {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                PlaylistResponseSchema,
            ),
            (eff) => withRetry(eff, 'updatePlaylist'),
        );

        return runEffect(effect, 'updatePlaylist');
    }

    async showUserPlaylists() {
        const effect = pipe(
            httpRequest(
                '/api/playlists/user/list',
                {
                    method: 'GET',
                },
                UserPlaylistsResponseSchema,
            ),
            (eff) => withRetry(eff, 'showUserPlaylists'),
        );

        return runEffect(effect, 'showUserPlaylists');
    }

    /* ==========================================================================
     * Music Track Methods
     * ========================================================================== */

    async listMusicTracks() {
        const effect = pipe(
            httpRequest(
                '/api/music-tracks',
                {
                    method: 'GET',
                },
                MusicTracksResponseSchema,
            ),
            (eff) => withRetry(eff, 'listMusicTracks'),
        );

        return runEffect(effect, 'listMusicTracks');
    }

    async createMusicTrack(payload: CreateMusicTrackRequest) {
        const effect = pipe(
            httpRequest(
                '/api/music-tracks',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                MusicTrackResponseSchema,
            ),
            (eff) => withRetry(eff, 'createMusicTrack'),
        );

        return runEffect(effect, 'createMusicTrack');
    }

    async showUserMusicTracks() {
        const effect = pipe(
            httpRequest(
                '/api/music-tracks/user',
                {
                    method: 'GET',
                },
                UserMusicTracksResponseSchema,
            ),
            (eff) => withRetry(eff, 'showUserMusicTracks'),
        );

        return runEffect(effect, 'showUserMusicTracks');
    }

    /* ==========================================================================
     * Quiz Question Methods
     * ========================================================================== */

    async listQuizQuestions() {
        const effect = pipe(
            httpRequest(
                '/api/quiz-questions',
                {
                    method: 'GET',
                },
                QuizQuestionsResponseSchema,
            ),
            (eff) => withRetry(eff, 'listQuizQuestions'),
        );

        return runEffect(effect, 'listQuizQuestions');
    }

    async createQuizQuestion(payload: CreateQuizQuestionRequest) {
        const effect = pipe(
            httpRequest(
                '/api/quiz-questions',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                QuizQuestionResponseSchema,
            ),
            (eff) => withRetry(eff, 'createQuizQuestion'),
        );

        return runEffect(effect, 'createQuizQuestion');
    }

    /* ==========================================================================
     * Session Methods
     * ========================================================================== */

    async listActiveGames() {
        const effect = pipe(
            httpRequest(
                '/api/sessions/active-games',
                {
                    method: 'GET',
                },
                ActiveGamesResponseSchema,
            ),
            (eff) => withRetry(eff, 'listActiveGames'),
        );

        return runEffect(effect, 'listActiveGames');
    }

    async createSession(payload: CreateSessionRequest) {
        const effect = pipe(
            httpRequest(
                '/api/sessions',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                SessionLobbyResponseSchema,
            ),
            (eff) => withRetry(eff, 'createSession'),
        );

        return runEffect(effect, 'createSession');
    }

    async joinSession(payload: JoinSessionRequest) {
        const effect = pipe(
            httpRequest(
                '/api/sessions/join',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                SessionLobbyResponseSchema,
            ),
            (eff) => withRetry(eff, 'joinSession'),
        );

        return runEffect(effect, 'joinSession');
    }

    async showSessionLobby(roomCode: string) {
        const effect = pipe(
            httpRequest(
                `/api/sessions/${roomCode}`,
                {
                    method: 'GET',
                },
                SessionLobbyResponseSchema,
            ),
            (eff) => withRetry(eff, 'showSessionLobby'),
        );

        return runEffect(effect, 'showSessionLobby');
    }

    async startSession(roomCode: string) {
        const payload: StartGameRequest = { room_code: roomCode };

        const effect = pipe(
            httpRequest(
                `/api/sessions/${roomCode}/start`,
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'startSession'),
        );

        return runEffect(effect, 'startSession');
    }

    async nextRound(roomCode: string) {
        const effect = pipe(
            httpRequest(
                `/api/sessions/${roomCode}/next-round`,
                {
                    method: 'POST',
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'nextRound'),
        );

        return runEffect(effect, 'nextRound');
    }

    async leaveSession(roomCode: string) {
        const effect = pipe(
            httpRequest(
                `/api/sessions/${roomCode}/leave`,
                {
                    method: 'POST',
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'leaveSession'),
        );

        return runEffect(effect, 'leaveSession');
    }

    async showSessionPlay(roomCode: string) {
        const effect = pipe(
            httpRequest(
                `/api/sessions/${roomCode}/play`,
                {
                    method: 'GET',
                },
                SessionPlayResponseSchema,
            ),
            (eff) => withRetry(eff, 'showSessionPlay'),
        );

        return runEffect(effect, 'showSessionPlay');
    }

    async submitAnswer(roomCode: string, answer: string, selectedOptionId?: string | null) {
        const payload: SubmitAnswerRequest = { answer, selected_option_id: selectedOptionId ?? null };

        const effect = pipe(
            httpRequest(
                `/api/sessions/${roomCode}/answer`,
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                },
                SubmitAnswerResponseSchema,
            ),
            (eff) => withRetry(eff, 'submitAnswer'),
        );

        return runEffect(effect, 'submitAnswer');
    }

    async showSessionResults(roomCode: string) {
        const effect = pipe(
            httpRequest(
                `/api/sessions/${roomCode}/results`,
                {
                    method: 'GET',
                },
                SessionResultsResponseSchema,
            ),
            (eff) => withRetry(eff, 'showSessionResults'),
        );

        return runEffect(effect, 'showSessionResults');
    }

    async fetchSessionToken() {
        // Note: This endpoint may not exist; placeholder for type checking
        const effect = pipe(
            httpRequest(
                '/api/session-token',
                {
                    method: 'GET',
                },
                AuthResponseSchema,
            ),
            (eff) => withRetry(eff, 'fetchSessionToken'),
        );

        return runEffect(effect, 'fetchSessionToken');
    }

    /* ==========================================================================
     * Content Methods (Placeholder)
     * ========================================================================== */

    async showContent() {
        // Placeholder for ContentPage; may need to be updated
        const effect = pipe(
            httpRequest(
                '/api/content',
                {
                    method: 'GET',
                },
                MessageResponseSchema,
            ),
            (eff) => withRetry(eff, 'showContent'),
        );

        return runEffect(effect, 'showContent');
    }

    /* ==========================================================================
     * Broadcasting Methods
     * ========================================================================== */

    async authenticateBroadcasting(socketId: string, channelName: string) {
        const effect = pipe(
            httpRequest(
                '/api/broadcasting/auth',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channelName,
                    }),
                },
                AuthenticateBroadcastingResponseSchema,
            ),
            (eff) => withRetry(eff, 'authenticateBroadcasting'),
        );

        return runEffect(effect, 'authenticateBroadcasting');
    }
}

export const ApiClient = new ApiClientSingleton();
