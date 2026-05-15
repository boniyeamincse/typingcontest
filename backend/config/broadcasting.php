<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option defines the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other parts of your application as well as other
    | applications listening to these channels. Feel free to add more.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'host' => env('PUSHER_HOST', 'api-'.env('PUSHER_REGION', 'mt').'.pusher.com'),
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'host' => env('PUSHER_HOST', 'api-'.env('PUSHER_REGION', 'mt').'.pusher.com'),
                'port' => env('PUSHER_PORT', 443),
                'timeout' => 30,
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'soketi' => [
            'driver' => 'pusher',
            'key' => env('SOKETI_APP_KEY', 'app-key'),
            'secret' => env('SOKETI_APP_SECRET', 'app-secret'),
            'app_id' => env('SOKETI_APP_ID', '1'),
            'options' => [
                'host' => env('SOKETI_HOST', 'localhost'),
                'port' => env('SOKETI_PORT', 6001),
                'scheme' => env('SOKETI_SCHEME', 'http'),
                'encrypted' => false,
            ],
            'client_options' => [
                'scheme' => env('SOKETI_SCHEME', 'http'),
                'host' => env('SOKETI_HOST', 'localhost'),
                'port' => env('SOKETI_PORT', 6001),
                'timeout' => 30,
            ],
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
