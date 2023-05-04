<?php

    return [
        'api_keys' => [
            'secret_key' => env('STRIPE_SECRET_KEY', null),
            'public_key' => env('STRIPE_PUBLIC_KEY', null)
        ]
    ];