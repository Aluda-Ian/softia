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

];
