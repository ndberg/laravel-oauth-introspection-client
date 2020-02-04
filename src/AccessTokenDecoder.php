<?php


namespace Ndberg\IntrospectionClient;


use Firebase\JWT\JWT;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Class AccessTokenDecoder
 *
 * @package Ndberg\IntrospectionClient
 */
class AccessTokenDecoder
{
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
     * @var \Illuminate\Http\Request
     */
    private Request $request;

    /**
     * AccessTokenDecoder constructor.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Ndberg\IntrospectionClient\AccessTokenDecoder
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function decodeAccessToken() : self
    {
        $key = File::get(storage_path('oauth-public.key'));
        $decoded = JWT::decode($this->request->bearerToken(), $key, ['RS256']);

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
