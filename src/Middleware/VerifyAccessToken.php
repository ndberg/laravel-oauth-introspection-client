<?php

namespace Ndberg\IntrospectionClient\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Support\Facades\Config;
use Ndberg\IntrospectionClient\AccessTokenDecoder;
use Ndberg\IntrospectionClient\Exceptions\MissingScopeException;
use Ndberg\IntrospectionClient\Introspection;

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
     * @var \Ndberg\IntrospectionClient\Introspection
     */
    protected Introspection $introspection;
    /**
     * @var \Illuminate\Cache\Repository
     */
    private Cache $cache;

    private $config;

    /**
     * VerifyAccessToken constructor.
     *
     * @param  \Ndberg\IntrospectionClient\Introspection  $introspection
     * @param  \Illuminate\Cache\Repository  $cache
     */
    public function __construct(Introspection $introspection, Cache $cache)
    {
        $this->introspection = $introspection;
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
     * @throws \Ndberg\IntrospectionClient\Exceptions\InvalidAccessTokenException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $scopes = is_array($scopes) ? $scopes : [$scopes];

        $token = $request->bearerToken();
        if (empty($token)) {
            throw new AuthenticationException();
        }

        if ($this->config['introspection_use_local_decoding']) {
            // Decodes and checkes signature, exp, etc.
            try {
                $accessTokenDecoder = (new AccessTokenDecoder($token))->decodeAccessToken();
            } catch (ExpiredException $exception) {
                abort('401', 'Expired Token');
            } catch (\Exception $exception) {
                abort('401', 'Invalid token');
            }

            // Check required scopes
            $this->mustHaveScopes($accessTokenDecoder->scopes, $scopes);
        }

        // remember introspection to endpoint / server for 5 Minutes to reduce network traffic
        $user = $this->cache->remember('introspect_token:'.$token, $this->config['introspection_cache_result_in_minutes'], function () use ($scopes) {
            return $this->introspection
                ->verifyToken()
                ->mustHaveScopes($scopes)
                ->getUser();
        });

        if ($user) {
            \Auth::login($user);
            if ($user->user_companies) {
                $this->cache->remember('user:'.$user->id.':user_companies', 60 * 5, function () use ($user) {
                    return collect($user->user_companies);
                });
            }
        } else {
            throw new AuthenticationException();
        }

        return $next($request);
    }

    /**
     * @param  array  $givenScopes
     * @param  array  $requiredScopes
     *
     * @return bool
     * @throws \Ndberg\IntrospectionClient\Exceptions\MissingScopeException
     */
    public function mustHaveScopes(array $givenScopes = [], array $requiredScopes = [])
    {
        $missingScopes = array_diff($requiredScopes, $givenScopes);

        if (count($missingScopes) > 0) {
            throw new MissingScopeException($missingScopes);
        }

        return true;
    }
}
