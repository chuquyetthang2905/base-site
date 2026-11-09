<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Only expose API routes. 'sanctum/csrf-cookie' is not needed since we use JWT.
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // Restrict to the frontend origin. Never use '*' when supports_credentials is true.
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:8080')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // Expose Authorization header so the frontend can read it if needed.
    'exposed_headers' => ['Authorization'],

    'max_age' => 86400,

    // Must be true so the browser sends the HttpOnly refresh token cookie on cross-origin requests.
    'supports_credentials' => true,

];
