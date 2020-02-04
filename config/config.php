<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel OAuth Introspection Client Options
    |--------------------------------------------------------------------------
    |
    */

    /*
     * base url of the introspection server (laravel passport server with introspection package)
     * Example: 'https://my-auth-server.com'
     */
    'authorization_url' => env('AUTHORIZATION_SERVER_AUTHORIZATION_URL'),

    /*
    * Redirect URL, where the auth server should return
    */
    'redirect_url' => env('AUTHORIZATION_SERVER_REDIRECT_URL', null),

    /*
     * Auth Server token endpoint to get an oauth token for the introspection client itself
     * in .env you can use: ${AUTHORIZATION_SERVER_URL}/oauth/token
     */
    'token_url' => env('AUTHORIZATION_SERVER_TOKEN_URL', env('AUTHORIZATION_SERVER_URL') . '/oauth/token'),

    /*
     * Introspection Server Endpoint URL
     * in .env you can use: ${AUTHORIZATION_SERVER_URL}/oauth/introspect
     */
    'introspect_url' => env('AUTHORIZATION_SERVER_INTROSPECT_URL', env('AUTHORIZATION_SERVER_URL') . '/oauth/introspect'),

    /*
     * Client id of this introspection client, defined in laravel passport
     */
    'client_id' => env('AUTHORIZATION_SERVER_CLIENT_ID'),

    /*
     * Client secret of this introspection client, defined in laravel passport
     */
    'client_secret' => env('AUTHORIZATION_SERVER_CLIENT_SECRET'),

    /*
     * Scope which this introspection client must have for using the introspection endpoint
     */
    'scope' => env('AUTHORIZATION_SERVER_SCOPE', 'introspect'),

    /*
     * If this introspection client should cache the results of the introspection server.
     * Cached for each access token
     */
    'introspection_cache_result_in_minutes' => env('INTROSPECTION_CACHE_RESULT_IN_MINUTES', null),

    /*
     * If this Introspection Client should first decode the obtained access token from
     * the calling client to check if it's expired or does not have the required scope,
     * before checking it on the introspection server (endpoint)
     *
     * Needs firebase/php-jwt -> https://github.com/firebase/php-jwt
     *
     * Needs the key from the laravel/passport server in /storage/oauth-public.key
     * This has to be placed in this project in /storage/oauth-public.key
     * -> Don't put the real key in git, as no keys should be placed in repositories
     */
    'introspection_use_local_decoding' => env('INTROSPECTION_USE_LOCAL_DECODING', FALSE),

    /*
     * Completly disable remote introspection against an introspection server / endpoint.
     * Only possible if introspection_use_local_decoding is enabled.
     *
     * Attention, there are some cons:
     * - Does not recognize revoked tokens
     * - Then there are no information about the current user, it can't be logged in either.
     */
    'introspection_disable_remote' => env('INTROSPECTION_DISABLE_REMOTE', FALSE),
];
