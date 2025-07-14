<?php

return [

    'default' => env('BROADCAST_DRIVER', 'pusher'),

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('REVERB_APP_KEY', 'radijator-key'),
            'secret' => env('REVERB_APP_SECRET', 'dummy-secret'),
            'app_id' => env('REVERB_APP_ID', 'radijator-app'),
            'options' => [
                'host' => env('REVERB_HOST', 'radijatorapp.duckdns.org'),
                'port' => env('REVERB_PORT', 443),
                'scheme' => env('REVERB_SCHEME', 'https'),
                'useTLS' => true,
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
