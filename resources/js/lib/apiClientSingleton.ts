import {
    ActiveGamesResponseSchema,
    AuthResponseSchema,
    BrowseResponseSchema,
    CategoriesResponseSchema,
    CategoryResponseSchema,
    ContentItemsSchema,
    CreateMusicTrackRequestSchema,
    CreatePlaylistRequestSchema,
    CreateQuizQuestionRequestSchema,
    CreateSessionRequestSchema,
    HomeResponseSchema,
    JoinSessionRequestSchema,
    LeaderboardResponseSchema,
    LoginRequest,
    LoginRequestSchema,
    MusicTrackResponseSchema,
    MusicTracksResponseSchema,
    PlaylistResponseSchema,
    PlaylistsResponseSchema,
    QuizQuestionResponseSchema,
    QuizQuestionsResponseSchema,
    RegisterRequest,
    RegisterRequestSchema,
    SessionLobbyResponseSchema,
    SessionPlayResponseSchema,
    SessionResultsResponseSchema,
    StatisticsResponseSchema,
    TrackResponseSchema,
    TracksResponseSchema,
    UserDataSchema,
} from '@/types/effect-schemas';
import {
    FetchHttpClient,
    HttpApi,
    HttpApiClient,
    HttpApiEndpoint,
    HttpApiGroup,
    HttpClient,
    HttpClientRequest,
} from '@effect/platform';
import { Effect, Schema } from 'effect';

import { HttpApiDecodeError } from '@effect/platform/HttpApiError';
import { HttpClientError } from '@effect/platform/HttpClientError';
import { ParseError } from 'effect/ParseResult';
import { apiCache } from './apiCache';
import { authManager } from './auth';

export const ValidationErrorSchema = Schema.Struct({
    _tag: Schema.Literal('ValidationError'),
    errors: Schema.Record({
        key: Schema.String,
        value: Schema.Array(Schema.String),
    }),
});

export type ValidationError = Schema.Schema.Type<typeof ValidationErrorSchema>;

/* ============================================================================
 * API Definition
 * ============================================================================
 */

const authGroup = HttpApiGroup.make('auth')
    .add(
        HttpApiEndpoint.post('login', '/api/login')
            .setPayload(LoginRequestSchema)
            .addSuccess(AuthResponseSchema),
    )
    .add(
        HttpApiEndpoint.post('register', '/api/register')
            .setPayload(RegisterRequestSchema)
            .addSuccess(AuthResponseSchema),
    )
    .add(
        HttpApiEndpoint.post('logout', '/api/logout').addSuccess(
            Schema.Struct({ message: Schema.String }),
        ),
    )
    .add(
        HttpApiEndpoint.post(
            'disconnectGoogle',
            '/api/disconnect-google',
        ).addSuccess(
            Schema.Struct({
                message: Schema.String,
                user: UserDataSchema,
            }),
        ),
    )
    .add(
        HttpApiEndpoint.get('oauthToken', '/api/oauth-token').addSuccess(
            AuthResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.get('sessionToken', '/api/token').addSuccess(
            AuthResponseSchema,
        ),
    );

const userGroup = HttpApiGroup.make('users').add(
    HttpApiEndpoint.get('show', '/api/user').addSuccess(UserDataSchema),
);

const contentGroup = HttpApiGroup.make('content').add(
    HttpApiEndpoint.get('show', '/api/content').addSuccess(ContentItemsSchema),
);

// Home endpoint (authenticated, but returns empty data for guests)
const homeGroup = HttpApiGroup.make('home').add(
    HttpApiEndpoint.get('show', '/api/home').addSuccess(HomeResponseSchema),
);

// Browse endpoints (public)
const browseGroup = HttpApiGroup.make('browse')
    .add(
        HttpApiEndpoint.get('index', '/api/browse').addSuccess(
            BrowseResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.get('categories', '/api/browse/categories').addSuccess(
            CategoriesResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.get('category', '/api/browse/categories/:id')
            .setPath(Schema.Struct({ id: Schema.String }))
            .addSuccess(CategoryResponseSchema),
    )
    .add(
        HttpApiEndpoint.get('tracks', '/api/browse/tracks').addSuccess(
            TracksResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.get('track', '/api/browse/tracks/:id')
            .setPath(Schema.Struct({ id: Schema.String }))
            .addSuccess(TrackResponseSchema),
    )
    .add(
        HttpApiEndpoint.get('playlists', '/api/browse/playlists').addSuccess(
            PlaylistsResponseSchema,
        ),
    );

// Leaderboard (public)
const leaderboardGroup = HttpApiGroup.make('leaderboard').add(
    HttpApiEndpoint.get('show', '/api/leaderboard').addSuccess(
        LeaderboardResponseSchema,
    ),
);

// Playlists endpoints (authenticated)
const playlistsGroup = HttpApiGroup.make('playlists')
    .add(
        HttpApiEndpoint.get('list', '/api/playlists').addSuccess(
            PlaylistsResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.post('create', '/api/playlists')
            .setPayload(CreatePlaylistRequestSchema)
            .addSuccess(PlaylistResponseSchema),
    )
    .add(
        HttpApiEndpoint.get('show', '/api/playlists/:id')
            .setPath(Schema.Struct({ id: Schema.String }))
            .addSuccess(PlaylistResponseSchema),
    )
    .add(
        HttpApiEndpoint.put('update', '/api/playlists/:id')
            .setPath(Schema.Struct({ id: Schema.String }))
            .setPayload(CreatePlaylistRequestSchema)
            .addSuccess(PlaylistResponseSchema),
    );

// Music Tracks endpoints (authenticated)
const musicTracksGroup = HttpApiGroup.make('musicTracks')
    .add(
        HttpApiEndpoint.get('list', '/api/music-tracks').addSuccess(
            MusicTracksResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.post('create', '/api/music-tracks')
            .setPayload(CreateMusicTrackRequestSchema)
            .addSuccess(MusicTrackResponseSchema),
    );

// Quiz Questions endpoints (authenticated)
const quizQuestionsGroup = HttpApiGroup.make('quizQuestions')
    .add(
        HttpApiEndpoint.get('list', '/api/quiz-questions').addSuccess(
            QuizQuestionsResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.post('create', '/api/quiz-questions')
            .setPayload(CreateQuizQuestionRequestSchema)
            .addSuccess(QuizQuestionResponseSchema),
    );

// Game Sessions endpoints (authenticated)
const sessionsGroup = HttpApiGroup.make('sessions')
    .add(
        HttpApiEndpoint.get('activeGames', '/api/active-games').addSuccess(
            ActiveGamesResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.post('create', '/api/sessions')
            .setPayload(CreateSessionRequestSchema)
            .addSuccess(SessionLobbyResponseSchema),
    )
    .add(
        HttpApiEndpoint.post('join', '/api/sessions/join')
            .setPayload(JoinSessionRequestSchema)
            .addSuccess(SessionLobbyResponseSchema),
    )
    .add(
        HttpApiEndpoint.get('lobby', '/api/sessions/:roomCode')
            .setPath(Schema.Struct({ roomCode: Schema.String }))
            .addSuccess(SessionLobbyResponseSchema),
    )
    .add(
        HttpApiEndpoint.post('start', '/api/sessions/:roomCode/start')
            .setPath(Schema.Struct({ roomCode: Schema.String }))
            .addSuccess(SessionLobbyResponseSchema),
    )
    .add(
        HttpApiEndpoint.post('leave', '/api/sessions/:roomCode/leave')
            .setPath(Schema.Struct({ roomCode: Schema.String }))
            .addSuccess(Schema.Struct({ message: Schema.String })),
    )
    .add(
        HttpApiEndpoint.get('play', '/api/sessions/:roomCode/play')
            .setPath(Schema.Struct({ roomCode: Schema.String }))
            .addSuccess(SessionPlayResponseSchema),
    )
    .add(
        HttpApiEndpoint.get('results', '/api/sessions/:roomCode/results')
            .setPath(Schema.Struct({ roomCode: Schema.String }))
            .addSuccess(SessionResultsResponseSchema),
    );

// Statistics endpoints (authenticated)
const statisticsGroup = HttpApiGroup.make('statistics').add(
    HttpApiEndpoint.get('show', '/api/statistics').addSuccess(
        StatisticsResponseSchema,
    ),
);

export const Api = HttpApi.make('BackendApi')
    .add(authGroup)
    .add(userGroup)
    .add(contentGroup)
    .add(homeGroup)
    .add(browseGroup)
    .add(leaderboardGroup)
    .add(playlistsGroup)
    .add(musicTracksGroup)
    .add(quizQuestionsGroup)
    .add(sessionsGroup)
    .add(statisticsGroup)
    .addError(ValidationErrorSchema, { status: 422 });

/* ============================================================================
 * Form-Friendly Result
 * ============================================================================
 */
const baseUrl = ''; // Empty string to use relative paths

const baseClient = HttpApiClient.make(Api, {
    baseUrl,
});

const baseAuthClient = HttpApiClient.make(Api, {
    baseUrl,
    transformClient: (client) => {
        const token = authManager.getToken();
        if (token) {
            return client.pipe(
                HttpClient.mapRequest(HttpClientRequest.bearerToken(token)),
            );
        }
        return client;
    },
});

/* ============================================================================
 * Client Types (inferred from HttpApiClient.make)
 * ============================================================================
 */
type BaseClientType = Effect.Effect.Success<typeof baseClient>;
type BaseAuthClientType = Effect.Effect.Success<typeof baseAuthClient>;

/* ============================================================================
 * Singleton Client
 * ============================================================================
 */
class ApiClientSingleton {
    /* ==========================================================================
     * Memoized Client Instances
     * ========================================================================== */
    private _baseClientPromise: Promise<BaseClientType> | null = null;
    private _baseAuthClientPromise: Promise<BaseAuthClientType> | null = null;

    private getBaseClient(): Promise<BaseClientType> {
        if (!this._baseClientPromise) {
            this._baseClientPromise = Effect.runPromise(
                baseClient.pipe(Effect.provide(FetchHttpClient.layer)),
            );
        }
        return this._baseClientPromise;
    }

    private getBaseAuthClient(): Promise<BaseAuthClientType> {
        if (!this._baseAuthClientPromise) {
            this._baseAuthClientPromise = Effect.runPromise(
                baseAuthClient.pipe(Effect.provide(FetchHttpClient.layer)),
            );
        }
        return this._baseAuthClientPromise;
    }

    private runEffect<A>(
        effect: Effect.Effect<
            A,
            HttpApiDecodeError | ValidationError | HttpClientError | ParseError,
            HttpClient.HttpClient
        >,
    ) {
        return Effect.runPromise(
            effect.pipe(
                Effect.map((data) => ({
                    _tag: 'Success' as const,
                    data,
                })),
                Effect.catchTag('ValidationError', (e) => {
                    return Effect.succeed({
                        _tag: 'ValidationError' as const,
                        errors: e.errors,
                    });
                }),
                Effect.catchTag('ParseError', (e) => {
                    // Don't log - callers handle the error via tagged union result
                    return Effect.succeed({
                        _tag: 'ParseError' as const,
                        message: JSON.stringify(e),
                    });
                }),
                Effect.catchAll((e) => {
                    return Effect.succeed({
                        _tag: 'FatalError' as const,
                        message: JSON.stringify(e),
                    });
                }),
                Effect.provide(FetchHttpClient.layer),
            ),
        );
    }

    /**
     * Run an Effect with optional caching for offline support.
     * If a cacheKey is provided:
     * - Online: Fetch fresh data and cache it
     * - Offline: Return cached data if available
     */
    private async runEffectWithCache<A>(
        effect: Effect.Effect<
            A,
            HttpApiDecodeError | ValidationError | HttpClientError | ParseError,
            HttpClient.HttpClient
        >,
        cacheKey: string,
    ) {
        // If offline, try to return cached data
        if (!navigator.onLine) {
            const cached = await apiCache.get<A>(cacheKey);
            if (cached !== undefined) {
                return {
                    _tag: 'Success' as const,
                    data: cached,
                };
            }
            // No cached data available while offline
            return {
                _tag: 'FatalError' as const,
                message: 'You are offline and no cached data is available.',
            };
        }

        // Online: fetch fresh data
        const result = await this.runEffect(effect);

        // Cache successful responses
        if (result._tag === 'Success') {
            await apiCache.set(cacheKey, result.data);
        }

        return result;
    }

    /* ==========================================================================
     * Public API Methods
     * ========================================================================== */
    async login(payload: LoginRequest) {
        const client = await this.getBaseClient();
        return this.runEffect(client.auth.login({ payload }));
    }

    async register(payload: RegisterRequest) {
        const client = await this.getBaseClient();
        return this.runEffect(client.auth.register({ payload }));
    }

    async showUser() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.users.show());
    }

    async logout() {
        const client = await this.getBaseAuthClient();
        const result = await this.runEffect(client.auth.logout());
        // Clear cached data on logout to prevent data leakage
        await apiCache.clear();
        return result;
    }

    async disconnectGoogle() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.auth.disconnectGoogle());
    }

    async showContent() {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(client.content.show(), 'content_list');
    }

    async showHome() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.home.show());
    }

    /**
     * Fetch OAuth token after successful OAuth callback.
     * Called when user returns from OAuth provider with ?auth=success
     */
    async fetchOAuthToken() {
        const client = await this.getBaseClient();
        return this.runEffect(client.auth.oauthToken());
    }

    /**
     * Fetch session token from server.
     * Useful for restoring auth state from server session (e.g., after OAuth or in tests with actingAs)
     */
    async fetchSessionToken() {
        const client = await this.getBaseClient();
        return this.runEffect(client.auth.sessionToken());
    }

    /**
     * Initiate Google OAuth login/registration flow
     */
    googleLogin(forceConsent = false) {
        // Include current user ID in the OAuth redirect URL as a query parameter
        // This will be picked up by RedirectToGoogle action
        const user = authManager.getUser();
        const query: Record<string, string> = {};
        if (forceConsent) {
            query.force_consent = '1';
        }
        if (user) {
            query.user_id = user.id;
        }

        const queryString = new URLSearchParams(query).toString();
        window.location.href = `/auth/google${queryString ? `?${queryString}` : ''}`;
    }

    /* ==========================================================================
     * Browse API Methods (Public)
     * ========================================================================== */
    async showBrowse() {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(client.browse.index(), 'browse_index');
    }

    async showCategories() {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(
            client.browse.categories(),
            'categories_list',
        );
    }

    async showCategory(id: string) {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(
            client.browse.category({ path: { id } }),
            `category_${id}`,
        );
    }

    async showTracks(search?: string) {
        const client = await this.getBaseClient();
        return this.runEffect(client.browse.tracks());
    }

    async showTrack(id: string) {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(
            client.browse.track({ path: { id } }),
            `track_${id}`,
        );
    }

    async showPublicPlaylists() {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(
            client.browse.playlists(),
            'public_playlists',
        );
    }

    async showLeaderboard() {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(
            client.leaderboard.show(),
            'leaderboard',
        );
    }

    /* ==========================================================================
     * Playlists API Methods (Authenticated)
     * ========================================================================== */
    async listPlaylists() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.playlists.list());
    }

    async createPlaylist(
        payload: Schema.Schema.Type<typeof CreatePlaylistRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.playlists.create({ payload }));
    }

    async showPlaylist(id: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.playlists.show({ path: { id } }));
    }

    async updatePlaylist(
        id: string,
        payload: Schema.Schema.Type<typeof CreatePlaylistRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.playlists.update({ path: { id }, payload }),
        );
    }

    /* ==========================================================================
     * Music Tracks API Methods (Authenticated)
     * ========================================================================== */
    async listMusicTracks() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.musicTracks.list());
    }

    async createMusicTrack(
        payload: Schema.Schema.Type<typeof CreateMusicTrackRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.musicTracks.create({ payload }));
    }

    /* ==========================================================================
     * Quiz Questions API Methods (Authenticated)
     * ========================================================================== */
    async listQuizQuestions() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.quizQuestions.list());
    }

    async createQuizQuestion(
        payload: Schema.Schema.Type<typeof CreateQuizQuestionRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.quizQuestions.create({ payload }));
    }

    /* ==========================================================================
     * Game Sessions API Methods (Authenticated)
     * ========================================================================== */
    async listActiveGames() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.activeGames());
    }

    async createSession(
        payload: Schema.Schema.Type<typeof CreateSessionRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.create({ payload }));
    }

    async joinSession(
        payload: Schema.Schema.Type<typeof JoinSessionRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.join({ payload }));
    }

    async showSessionLobby(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.lobby({ path: { roomCode } }));
    }

    async startSession(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.start({ path: { roomCode } }));
    }

    async leaveSession(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.leave({ path: { roomCode } }));
    }

    async showSessionPlay(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.play({ path: { roomCode } }));
    }

    async showSessionResults(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.results({ path: { roomCode } }));
    }

    /* ==========================================================================
     * Statistics API Methods (Authenticated)
     * ========================================================================== */
    async showStatistics() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.statistics.show());
    }
}

// Export singleton instance
export const ApiClient = new ApiClientSingleton();
