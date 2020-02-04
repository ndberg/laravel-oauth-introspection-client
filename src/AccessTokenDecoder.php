<?php


namespace Ndberg\IntrospectionClient;


use Firebase\JWT\JWT;
use Illuminate\Support\Facades\File;

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
    public array $scopes;

    /**
     * AccessTokenDecoder constructor.
     *
     * @param  string  $accessToken
     */
    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return \Ndberg\IntrospectionClient\AccessTokenDecoder
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function decodeAccessToken() : self
    {
        $key = File::get(storage_path('oauth-public.key'));
        $decoded = JWT::decode($this->accessToken, $key, ['RS256']);

        $this->aud = $decoded->aud;
        $this->jti = $decoded->jti;
        $this->iat = $decoded->iat;
        $this->nbf = $decoded->nbf;
        $this->exp = $decoded->exp;
        $this->sub = $decoded->sub;
        $this->scopes = $decoded->scopes;

        return $this;
    }
}
