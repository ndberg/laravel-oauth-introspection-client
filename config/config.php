<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'authorization_url' => env('AUTHORIZATION_SERVER_AUTHORIZATION_URL'),
    'redirect_url' => env('AUTHORIZATION_SERVER_REDIRECT_URL'),
    'token_url' => env('AUTHORIZATION_SERVER_TOKEN_URL'),
    'introspect_url' => env('AUTHORIZATION_SERVER_INTROSPECT_URL'),
    'client_id' => env('AUTHORIZATION_SERVER_CLIENT_ID'),
    'client_secret' => env('AUTHORIZATION_SERVER_CLIENT_SECRET'),
    'scope' => env('AUTHORIZATION_SERVER_SCOPE'),
    'cache_introspection_result_in_minutes' => env('CACHE_INTROSPECTION_RESULT_IN_MINUTES'),
    'use_additional_local_decoding' => env('USE_ADDITIONAL_LOCAL_DECODING'),
];
