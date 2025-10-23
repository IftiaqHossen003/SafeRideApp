<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Traccar Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to Traccar GPS tracking server.
    | Supports both self-hosted and cloud-hosted Traccar instances.
    |
    */

    /**
     * Base URL of the Traccar server
     * Example: https://demo.traccar.org or http://localhost:8082
     */
    'url' => env('TRACCAR_URL', 'https://demo.traccar.org'),

    /**
     * Authentication method: 'basic' or 'token'
     * - basic: Uses username/password (TRACCAR_USERNAME, TRACCAR_PASSWORD)
     * - token: Uses API token (TRACCAR_TOKEN)
     */
    'auth_method' => env('TRACCAR_AUTH_METHOD', 'basic'),

    /**
     * Basic authentication credentials (if auth_method = 'basic')
     */
    'username' => env('TRACCAR_USERNAME', 'admin'),
    'password' => env('TRACCAR_PASSWORD', 'admin'),

    /**
     * API token for authentication (if auth_method = 'token')
     * Token should be generated from Traccar server settings
     */
    'token' => env('TRACCAR_TOKEN', ''),

    /**
     * Webhook authentication token
     * Used to verify incoming webhook requests from Traccar
     * Generate a secure random string for production
     */
    'webhook_token' => env('TRACCAR_WEBHOOK_TOKEN', ''),

    /**
     * API endpoints
     */
    'endpoints' => [
        'positions' => '/api/positions',
        'devices' => '/api/devices',
        'reports_route' => '/api/reports/route',
    ],

    /**
     * Sync settings
     */
    'sync' => [
        /**
         * Default time window for fetching positions (in hours)
         * Used when no specific time range is provided
         */
        'default_window_hours' => env('TRACCAR_SYNC_WINDOW_HOURS', 24),

        /**
         * Maximum number of positions to fetch per request
         */
        'max_positions_per_request' => env('TRACCAR_MAX_POSITIONS', 1000),

        /**
         * Timeout for HTTP requests (in seconds)
         */
        'timeout' => env('TRACCAR_TIMEOUT', 30),

        /**
         * Only sync positions for trips with these statuses
         */
        'trip_statuses' => ['ongoing'],
    ],

    /**
     * Enable/disable features
     */
    'features' => [
        /**
         * Enable automatic position syncing
         */
        'auto_sync' => env('TRACCAR_AUTO_SYNC', true),

        /**
         * Enable webhook endpoint for real-time position updates
         */
        'webhook_enabled' => env('TRACCAR_WEBHOOK_ENABLED', true),

        /**
         * Log all Traccar API requests/responses for debugging
         */
        'debug_logging' => env('TRACCAR_DEBUG_LOGGING', false),
    ],

];
