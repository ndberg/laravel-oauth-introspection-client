<?php

namespace Ndberg\IntrospectionClient\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Support\Facades\Config;
use Ndberg\IntrospectionClient\Introspection;
use Ndberg\IntrospectionClient\LocalIntrospector;
use Ndberg\IntrospectionClient\RemoteIntrospector;

/**
 * Class VerifyAccessToken
 *
 * Middleware for verifying an access_token
 *
 * @package Ndberg\IntrospectionClient\Middleware
 */
class VerifyAccessToken
{
    /**
     * @var \Ndberg\IntrospectionClient\RemoteIntrospector
     */
    protected RemoteIntrospector $remoteIntrospector;

    /**
     * @var \Illuminate\Cache\Repository
     */
    private Cache $cache;

    /**
     * introspection-client config
     *
     * @var mixed
     */
    private $config;

    /**
     * @var \Ndberg\IntrospectionClient\LocalIntrospector
     */
    private LocalIntrospector $localIntrospector;
    /**
     * @var \Ndberg\IntrospectionClient\Introspection
     */
    private Introspection $introspection;

    /**
     * VerifyAccessToken constructor.
     *
     * @param  \Ndberg\IntrospectionClient\Introspection  $introspection
     * @param  \Ndberg\IntrospectionClient\RemoteIntrospector  $remoteIntrospectort
     * @param  \Ndberg\IntrospectionClient\LocalIntrospector  $localIntrospector
     * @param  \Illuminate\Cache\Repository  $cache
     */
    public function __construct(Introspection $introspection, RemoteIntrospector $remoteIntrospectort, LocalIntrospector $localIntrospector, Cache $cache)
    {
        $this->introspection = $introspection;
        $this->remoteIntrospector = $remoteIntrospectort;
        $this->localIntrospector = $localIntrospector;
        $this->cache = $cache;
        $this->config = Config::get('introspection-client');
    }

    /**
     * @param $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     *
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Ndberg\IntrospectionClient\Exceptions\MissingScopeException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];

        $token = $request->bearerToken();
        if (empty($token)) {
            throw new AuthenticationException();
        }

        // If enabled, make a local introspection first
        if ($this->config['introspection_use_local_decoding']) {
            $this->localIntrospector->introspect()->mustHaveScopes($scopes);
        }

        // remember introspection to endpoint / server to reduce network traffic
        if ( ! $this->config['introspection_disable_remote']) {
            $userData = $this->cache->remember('introspect_token:'.$token, $this->config['introspection_cache_result_in_minutes'], function () use ($scopes) {
                return $this->remoteIntrospector
                    ->verifyToken()
                    ->mustHaveScopes($scopes)
                    ->getUserData();
            });

            $user = $this->introspection->setUser($userData);

            if ($user) {
                \Auth::login($user);
            } else {
                throw new AuthenticationException();
            }
        }

        return $next($request);
    }
}
