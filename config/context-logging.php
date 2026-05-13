<?php

declare(strict_types=1);

return [
    /*
     |--------------------------------------------------------------------------
     | Context Logging Configuration
     |--------------------------------------------------------------------------
     |
     | Configuration options for the contextual logging package.
     |
     */

    'http' => [
        // Disable this to turn off outbound HTTP auto-capture and sub-context APIs.
        'enabled' => env('CONTEXT_LOG_HTTP_ENABLED', false),

        // Capture options for global outbound HTTP instrumentation.
        'capture_headers' => env('CONTEXT_LOG_HTTP_CAPTURE_HEADERS', false),
        'capture_body' => env('CONTEXT_LOG_HTTP_CAPTURE_BODY', false),

        // Header names to redact in custom hooks and request/response logging.
        'redact_headers' => [
            'authorization',
            'cookie',
            'set-cookie',
            'x-api-key',
            'x-xsrf-token',
        ],

        // Redaction token used for masked headers/body fields.
        'redact_value' => '[redacted]',

        // JSON body keys to redact recursively when capture_body or request/response logging is enabled.
        'redact_body_fields' => [
            'password',
            'token',
            'secret',
            'access_token',
            'refresh_token',
            'api_key',
            'client_secret',
            'password_confirmation',
            'current_password',
            'api_token',
            'authorization',
            'bearer_token',
            'key',
            'private_key',
            'credit_card',
            'card_number',
            'cvv',
            'ssn',
            'social_security',
            'email_token',
            'verification_code',
            'otp',
            'pin',
            'html',
            'auth',
            'code',
            'code_verifier',
        ],

        // Query string keys to redact recursively.
        'redact_query_params' => [
            'token',
            'access_token',
            'refresh_token',
            'api_key',
            'signature',
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Incoming Request / Response Logging
     |--------------------------------------------------------------------------
     |
     | When enabled, request and response details are added as events to the
     | context log (only for web requests; ignored in console). Response and
     | user logging run in terminating middleware.
     |
     */

    'log' => [
        'console' => env('CONTEXT_LOG_CONSOLE', false),
        'request' => env('CONTEXT_LOG_REQUEST', false),
        'response' => env('CONTEXT_LOG_RESPONSE', false),
        'user' => env('CONTEXT_LOG_USER', false),

        // User model attributes to include in the User log event when log.user is true. Use 'id' for the model key (getKey()).
        'user_attributes' => ['id', 'name', 'email', 'role'],

        'db' => env('CONTEXT_LOG_DB', false),
        'cache' => env('CONTEXT_LOG_CACHE', false),
        'queue' => env('CONTEXT_LOG_QUEUE', false),
        'mail' => env('CONTEXT_LOG_MAIL', false),
        'reverb' => env('CONTEXT_LOG_REVERB', false),
        'schedule' => env('CONTEXT_LOG_SCHEDULE', false),
        'notifications' => env('CONTEXT_LOG_NOTIFICATIONS', false),
        'broadcasting' => env('CONTEXT_LOG_BROADCASTING', false),

        // Route names or path patterns to exclude from request/response logging (e.g. 'health', 'horizon.*', 'livewire/*').
        // Additional patterns: CONTEXT_LOG_IGNORE_EXTRA_ROUTES (comma-separated) in .env.
        'ignore_routes' => array_merge([
            '*.js',
            '*.css',
            '*.html',
            '*.png',
            '*.svg',
            '*.jpg',
            '*.jpeg',
            '*.gif',
            '*.woff',
            '*.woff2',
            '*.ttf',
            '*.eot',
            '*.js.map',
            '_boost/browser-logs',
            'livewire/update',
            'livewire-*/update',
        ], array_values(array_filter(array_map('trim', explode(',', (string) env('CONTEXT_LOG_IGNORE_EXTRA_ROUTES', '')))))),
    ],

    /*
     |--------------------------------------------------------------------------
     | Console Command Context
     |--------------------------------------------------------------------------
     |
     | Commands listed here do not get wrapped in a single "Console run completed"
     | context log. Use for long-running workers (e.g. queue:work, horizon) so
     | each job/unit of work is logged separately instead of one giant entry.
     | Supports Laravel wildcards (e.g. 'queue:*', 'horizon').
     |
     */

    'console' => [
        'skip_commands' => [
            'queue:work',
            'queue:listen',
            'reverb:start',
            'migrate:fresh',
            'migrate:fresh --seed',
        ],
    ],
];
