<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Webhook Security Configuration
    |--------------------------------------------------------------------------
    |
    | Each webhook source (e.g. "dapodik", "simak") has its own HMAC-SHA256
    | signing secret.  The middleware verifies the X-Webhook-Signature header
    | against the raw request body using the matching secret.
    |
    | Secrets are loaded from the WEBHOOK_SECRETS environment variable in the
    | format: "source1:secret1,source2:secret2"
    |
    */

    'secrets' => (function () {
        $raw = env('WEBHOOK_SECRETS', '');
        $map = [];
        foreach (array_filter(explode(',', $raw)) as $pair) {
            [$source, $secret] = explode(':', $pair, 2) + [null, null];
            if ($source && $secret) {
                $map[trim($source)] = trim($secret);
            }
        }
        return $map;
    })(),

    /*
    |--------------------------------------------------------------------------
    | Replay-Protection Window
    |--------------------------------------------------------------------------
    |
    | Maximum age in seconds of an accepted webhook request.  Requests with a
    | X-Webhook-Timestamp that is older than this window are rejected.
    | Set to 0 to disable timestamp checking.
    |
    */

    'max_age' => (int) env('WEBHOOK_MAX_AGE', 300),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | The queue name used to dispatch ProcessWebhookEvent jobs.
    |
    */

    'queue' => env('WEBHOOK_QUEUE', 'webhooks'),

    /*
    |--------------------------------------------------------------------------
    | Admin Credentials (HTTP Basic)
    |--------------------------------------------------------------------------
    |
    | Simple credentials for the /admin panel.  Replace with a proper auth
    | system (e.g. Laravel Breeze or Sanctum) for production use.
    |
    */

    'admin_user'     => env('ADMIN_USER', 'admin'),
    'admin_password' => env('ADMIN_PASSWORD', 'changeme'),

];
