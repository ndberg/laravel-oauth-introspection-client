<?php


namespace Ndberg\IntrospectionClient;


use Firebase\JWT\JWT;
use Illuminate\Support\Facades\File;
use Ndberg\IntrospectionClient\Exceptions\InvalidAccessTokenException;

/**
 * Class AccessTokenDecoder
 *
 * @package Ndberg\IntrospectionClient
 */
class AccessTokenDecoder
{
    /**
     * @var string
     */
    public string $accessToken;

    /**
     * Audience
     *
     * @var
     */
    public $aud;

    /**
     * JWT ID
     *
     * @var
     */
    public $jti;

    /**
     * Issued at
     *
     * @var
     */
    public $iat;

    /**
     * Not valid before
     *
     * @var
     */
    public $nbf;

    /**
     * Expiration Time
     *
     * @var
     */
    public $exp;

    /**
     * Subject
     *
     * @var
     */
    public $sub;

    /**
     * Scopes
     *
     * @var
     */
    public $scopes;

    /**
     * AccessTokenDecoder constructor.
     *
     * @param  string  $accessToken
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Ndberg\IntrospectionClient\Exceptions\InvalidAccessTokenException
     */
    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return object|null
     * @throws InvalidAccessTokenException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function decodeAccessToken() : ?object
    {
        $key = File::get(storage_path('oauth-public.key'));
        $decoded = JWT::decode($this->accessToken, $key, ['RS256']);

        if ( ! $decoded->aud) {
            throw new InvalidAccessTokenException();
        }

        $this->aud = $decoded->aud;

//        Log::debug('Access Token:', (array) $decoded);
//        Log::debug('Scopes', $decoded->scopes);
//        Log::debug('Scopes json '.json_encode($decoded->scopes));

        $this->scopes = json_encode($decoded->scopes);

        $this->exp = $decoded->exp;

        return $decoded;
    }
}
