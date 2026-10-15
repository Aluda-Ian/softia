<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Deploy Token
    |--------------------------------------------------------------------------
    |
    | Shared hosting without a terminal runs maintenance tasks through
    | /_deploy/{token}. The runner stays disabled (404) unless this token
    | is set to a random string of at least 32 characters.
    |
    */

    'token' => env('DEPLOY_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Seeded Super Admin
    |--------------------------------------------------------------------------
    |
    | Used by SuperAdminSeeder. Keep these values in .env only.
    |
    */

    'seed_admin' => [
        'name' => env('SEED_ADMIN_NAME'),
        'email' => env('SEED_ADMIN_EMAIL'),
        'password' => env('SEED_ADMIN_PASSWORD'),
    ],

];
