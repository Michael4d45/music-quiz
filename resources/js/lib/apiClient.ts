import {
    ActiveGamesResponseSchema,
    AuthenticateBroadcastingRequestSchema,
    AuthenticateBroadcastingResponseSchema,
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
    message: Schema.String,
    errors: Schema.Record({
        key: Schema.String,
        value: Schema.Array(Schema.String),
    }),
});

export type ValidationError = Schema.Schema.Type<typeof ValidationErrorSchema>;

export const CsrfTokenExpiredErrorSchema = Schema.Struct({
    _tag: Schema.Literal('CsrfTokenExpiredError'),
    message: Schema.String,
});

export type CsrfTokenExpiredError = Schema.Schema.Type<
    typeof CsrfTokenExpiredErrorSchema
>;

export const AuthenticationErrorSchema = Schema.Struct({
    _tag: Schema.Literal('AuthenticationError'),
    message: Schema.String,
});

export type AuthenticationError = Schema.Schema.Type<
    typeof AuthenticationErrorSchema
>;

export const NotFoundErrorSchema = Schema.Struct({
    _tag: Schema.Literal('NotFoundError'),
    message: Schema.String,
});

export type NotFoundError = Schema.Schema.Type<typeof NotFoundErrorSchema>;

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

// Subcategories and music sources (public)
const subCategoriesGroup = HttpApiGroup.make('subCategories').add(
    HttpApiEndpoint.get('index', '/api/sub-categories').addSuccess(
        Schema.Struct({
            sub_categories: Schema.Array(Schema.Any),
        }),
    ),
);

const musicSourcesGroup = HttpApiGroup.make('musicSources').add(
    HttpApiEndpoint.get('index', '/api/music-sources').addSuccess(
        Schema.Struct({
            music_sources: Schema.Array(Schema.Any),
        }),
    ),
);

// Quiz modes and scoring rules (public)
const quizModesGroup = HttpApiGroup.make('quizModes').add(
    HttpApiEndpoint.get('index', '/api/quiz-modes').addSuccess(
        Schema.Struct({
            quiz_modes: Schema.Array(Schema.Any),
        }),
    ),
);

const scoringRulesGroup = HttpApiGroup.make('scoringRules').add(
    HttpApiEndpoint.get('index', '/api/scoring-rules').addSuccess(
        Schema.Struct({
            scoring_rules: Schema.Array(Schema.Any),
        }),
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
    )
    .add(
        HttpApiEndpoint.get('userPlaylists', '/api/playlists/user/list').addSuccess(
            Schema.Struct({
                playlists: Schema.Array(Schema.Any),
            }),
        ),
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
            .addSuccess(MusicTrackResponseSchema, { status: 201 }),
    )
    .add(
        HttpApiEndpoint.get('userTracks', '/api/music-tracks/user').addSuccess(
            Schema.Struct({
                tracks: Schema.Array(Schema.Any),
            }),
        ),
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
            .addSuccess(QuizQuestionResponseSchema, { status: 201 }),
    );

// Game Sessions endpoints (authenticated)
const sessionsGroup = HttpApiGroup.make('sessions')
    .add(
        HttpApiEndpoint.get('activeGames', '/api/sessions/active-games').addSuccess(
            ActiveGamesResponseSchema,
        ),
    )
    .add(
        HttpApiEndpoint.post('create', '/api/sessions')
            .setPayload(CreateSessionRequestSchema)
            .addSuccess(SessionLobbyResponseSchema, { status: 201 }),
    )
    .add(
        HttpApiEndpoint.post('join', '/api/sessions/join')
            .setPayload(JoinSessionRequestSchema)
            .addSuccess(SessionLobbyResponseSchema, { status: 201 }),
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

// Broadcasting endpoints (authenticated)
const broadcastingGroup = HttpApiGroup.make('broadcasting').add(
    HttpApiEndpoint.post('auth', '/api/broadcasting/auth')
        .setPayload(AuthenticateBroadcastingRequestSchema)
        .addSuccess(AuthenticateBroadcastingResponseSchema),
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
    .add(subCategoriesGroup)
    .add(musicSourcesGroup)
    .add(quizModesGroup)
    .add(scoringRulesGroup)
    .add(leaderboardGroup)
    .add(playlistsGroup)
    .add(musicTracksGroup)
    .add(quizQuestionsGroup)
    .add(sessionsGroup)
    .add(broadcastingGroup)
    .add(statisticsGroup)
    .addError(ValidationErrorSchema, { status: 422 })
    .addError(CsrfTokenExpiredErrorSchema, { status: 419 })
    .addError(AuthenticationErrorSchema, { status: 401 })
    .addError(NotFoundErrorSchema, { status: 404 });

/* ============================================================================
 * Form-Friendly Result
 * ============================================================================
 */
const baseUrl = ''; // Empty string to use relative paths

// Base transform that always adds Accept header
const withJsonAccept = HttpClient.mapRequest(
    HttpClientRequest.setHeader('Accept', 'application/json'),
);

// Base client with common configuration
const baseClient = HttpApiClient.make(Api, {
    baseUrl,
    transformClient: (client) => client.pipe(withJsonAccept),
});

// Auth client - adds bearer token on top of base
const baseAuthClient = HttpApiClient.make(Api, {
    baseUrl,
    transformClient: (client) => {
        const token = authManager.getToken();
        if (token) {
            return client.pipe(
                withJsonAccept,
                HttpClient.mapRequest(HttpClientRequest.bearerToken(token)),
            );
        }
        return client.pipe(withJsonAccept);
    },
});

// CSRF client - adds CSRF token on top of base
const baseCsrfClient = HttpApiClient.make(Api, {
    baseUrl,
    transformClient: (client) => {
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || null;
        if (csrfToken) {
            return client.pipe(
                withJsonAccept,
                HttpClient.mapRequest(
                    HttpClientRequest.setHeader('X-CSRF-TOKEN', csrfToken),
                ),
            );
        }
        return client.pipe(withJsonAccept);
    },
});

// Auth + CSRF client - combines both auth and CSRF on top of base
const baseAuthCsrfClient = HttpApiClient.make(Api, {
    baseUrl,
    transformClient: (client) => {
        let transformed = client.pipe(withJsonAccept);

        const token = authManager.getToken();
        if (token) {
            transformed = transformed.pipe(
                HttpClient.mapRequest(HttpClientRequest.bearerToken(token)),
            );
        }
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || null;
        if (csrfToken) {
            transformed = transformed.pipe(
                HttpClient.mapRequest(
                    HttpClientRequest.setHeader('X-CSRF-TOKEN', csrfToken),
                ),
            );
        }

        return transformed;
    },
});

/* ============================================================================
 * Client Types (inferred from HttpApiClient.make)
 * ============================================================================
 */
type BaseClientType = Effect.Effect.Success<typeof baseClient>;
type BaseAuthClientType = Effect.Effect.Success<typeof baseAuthClient>;
type BaseCsrfClientType = Effect.Effect.Success<typeof baseCsrfClient>;
type BaseAuthCsrfClientType = Effect.Effect.Success<typeof baseAuthCsrfClient>;

type ErrorsType =
    | HttpApiDecodeError
    | ValidationError
    | CsrfTokenExpiredError
    | HttpClientError
    | ParseError
    | AuthenticationError
    | NotFoundError;

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
    private _baseCsrfClientPromise: Promise<BaseCsrfClientType> | null = null;
    private _baseAuthCsrfClientPromise: Promise<BaseAuthCsrfClientType> | null =
        null;

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

    private getBaseCsrfClient(): Promise<BaseCsrfClientType> {
        if (!this._baseCsrfClientPromise) {
            this._baseCsrfClientPromise = Effect.runPromise(
                baseCsrfClient.pipe(Effect.provide(FetchHttpClient.layer)),
            );
        }
        return this._baseCsrfClientPromise;
    }

    private getBaseAuthCsrfClient(): Promise<BaseAuthCsrfClientType> {
        if (!this._baseAuthCsrfClientPromise) {
            this._baseAuthCsrfClientPromise = Effect.runPromise(
                baseAuthCsrfClient.pipe(Effect.provide(FetchHttpClient.layer)),
            );
        }
        return this._baseAuthCsrfClientPromise;
    }

    private runEffect<A>(
        effect: Effect.Effect<A, ErrorsType>,
        context: string,
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
                        message: e.message,
                        errors: e.errors,
                    });
                }),
                Effect.catchTag('CsrfTokenExpiredError', (e) => {
                    return Effect.succeed({
                        _tag: 'CsrfTokenExpiredError' as const,
                        message: e.message,
                    });
                }),
                Effect.catchTag('AuthenticationError', (e) => {
                    return Effect.succeed({
                        _tag: 'AuthenticationError' as const,
                        message: e.message,
                    });
                }),
                Effect.catchTag('NotFoundError', (e) => {
                    return Effect.succeed({
                        _tag: 'NotFoundError' as const,
                        message: e.message,
                    });
                }),
                Effect.catchTag('ParseError', (e) => {
                    console.error(context, e);
                    return Effect.succeed({
                        _tag: 'ParseError' as const,
                        message: e.toString(),
                    });
                }),
                Effect.catchAll((e) => {
                    console.error(e);
                    return Effect.succeed({
                        _tag: 'FatalError' as const,
                        message: e.toString(),
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
        effect: Effect.Effect<A, ErrorsType>,
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
        const result = await this.runEffect(effect, cacheKey);

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
        // Login needs web middleware (session + CSRF) to create sessions for Filament
        const client = await this.getBaseCsrfClient();
        return this.runEffect(client.auth.login({ payload }), 'login');
    }

    async register(payload: RegisterRequest) {
        // Register needs web middleware (session + CSRF) to create sessions for Filament
        const client = await this.getBaseCsrfClient();
        return this.runEffect(client.auth.register({ payload }), 'register');
    }

    async showUser() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.users.show(), 'showUser');
    }

    async logout() {
        // Logout needs web middleware (session + CSRF) to clear Redis sessions for Filament
        const client = await this.getBaseAuthCsrfClient();
        const result = await this.runEffect(client.auth.logout(), 'logout');
        // Clear cached data on logout to prevent data leakage
        await apiCache.clear();
        return result;
    }

    async disconnectGoogle() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.auth.disconnectGoogle(),
            'disconnectGoogle',
        );
    }

    async showContent() {
        const client = await this.getBaseClient();
        return this.runEffectWithCache(
            client.content.show(),
            'content_list',
        );
    }

    async showHome() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.home.show(), 'showHome');
    }

    /**
     * Fetch OAuth token after successful OAuth callback.
     * Called when user returns from OAuth provider with ?auth=success
     */
    async fetchOAuthToken() {
        const client = await this.getBaseClient();
        return this.runEffect(client.auth.oauthToken(), 'fetchOAuthToken');
    }

    /**
     * Fetch session token from server.
     * Useful for restoring auth state from server session (e.g., after OAuth or in tests with actingAs)
     */
    async fetchSessionToken() {
        const client = await this.getBaseClient();
        return this.runEffect(client.auth.sessionToken(), 'fetchSessionToken');
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
        return this.runEffect(client.browse.tracks(), 'showTracks');
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
     * Public Data API Methods
     * ========================================================================== */
    async showSubCategories() {
        const client = await this.getBaseClient();
        return this.runEffect(client.subCategories.index(), 'showSubCategories');
    }

    async showMusicSources() {
        const client = await this.getBaseClient();
        return this.runEffect(client.musicSources.index(), 'showMusicSources');
    }

    async showQuizModes() {
        const client = await this.getBaseClient();
        return this.runEffect(client.quizModes.index(), 'showQuizModes');
    }

    async showScoringRules() {
        const client = await this.getBaseClient();
        return this.runEffect(client.scoringRules.index(), 'showScoringRules');
    }

    /* ==========================================================================
     * Playlists API Methods (Authenticated)
     * ========================================================================== */
    async listPlaylists() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.playlists.list(), 'listPlaylists');
    }

    async createPlaylist(
        payload: Schema.Schema.Type<typeof CreatePlaylistRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.playlists.create({ payload }),
            'createPlaylist',
        );
    }

    async showPlaylist(id: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.playlists.show({ path: { id } }),
            'showPlaylist',
        );
    }

    async updatePlaylist(
        id: string,
        payload: Schema.Schema.Type<typeof CreatePlaylistRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.playlists.update({ path: { id }, payload }),
            'updatePlaylist',
        );
    }

    /* ==========================================================================
     * Music Tracks API Methods (Authenticated)
     * ========================================================================== */
    async listMusicTracks() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.musicTracks.list(), 'listMusicTracks');
    }

    async createMusicTrack(
        payload: Schema.Schema.Type<typeof CreateMusicTrackRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.musicTracks.create({ payload }),
            'createMusicTrack',
        );
    }

    /* ==========================================================================
     * Quiz Questions API Methods (Authenticated)
     * ========================================================================== */
    async listQuizQuestions() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.quizQuestions.list(), 'listQuizQuestions');
    }

    async createQuizQuestion(
        payload: Schema.Schema.Type<typeof CreateQuizQuestionRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.quizQuestions.create({ payload }),
            'createQuizQuestion',
        );
    }

    /* ==========================================================================
     * User Data API Methods (Authenticated)
     * ========================================================================== */
    async showUserMusicTracks() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.musicTracks.userTracks(), 'showUserMusicTracks');
    }

    async showUserPlaylists() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.playlists.userPlaylists(), 'showUserPlaylists');
    }

    /* ==========================================================================
     * Game Sessions API Methods (Authenticated)
     * ========================================================================== */
    async listActiveGames() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.activeGames(), 'listActiveGames');
    }

    async createSession(
        payload: Schema.Schema.Type<typeof CreateSessionRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.sessions.create({ payload }),
            'createSession',
        );
    }

    async joinSession(
        payload: Schema.Schema.Type<typeof JoinSessionRequestSchema>,
    ) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.sessions.join({ payload }), 'joinSession');
    }

    async showSessionLobby(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.sessions.lobby({ path: { roomCode } }),
            'showSessionLobby',
        );
    }

    async startSession(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.sessions.start({ path: { roomCode } }),
            'startSession',
        );
    }

    async leaveSession(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.sessions.leave({ path: { roomCode } }),
            'leaveSession',
        );
    }

    async showSessionPlay(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.sessions.play({ path: { roomCode } }),
            'showSessionPlay',
        );
    }

    async showSessionResults(roomCode: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.sessions.results({ path: { roomCode } }),
            'showSessionResults',
        );
    }

    /* ==========================================================================
     * Statistics API Methods (Authenticated)
     * ========================================================================== */
    async showStatistics() {
        const client = await this.getBaseAuthClient();
        return this.runEffect(client.statistics.show(), 'showStatistics');
    }

    /* ==========================================================================
     * Broadcasting API Methods (Authenticated)
     * ========================================================================== */
    async authenticateBroadcasting(socketId: string, channelName: string) {
        const client = await this.getBaseAuthClient();
        return this.runEffect(
            client.broadcasting.auth({
                payload: { socket_id: socketId, channel_name: channelName },
            }),
            'authenticateBroadcasting',
        );
    }
}

// Export singleton instance
export const ApiClient = new ApiClientSingleton();
