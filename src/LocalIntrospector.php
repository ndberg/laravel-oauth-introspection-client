<?php


namespace Ndberg\IntrospectionClient;

use Firebase\JWT\ExpiredException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Ndberg\IntrospectionClient\Exceptions\MissingScopeException;

class LocalIntrospector
{
    /**
     * @var \Ndberg\IntrospectionClient\AccessTokenDecoder
     */
    private AccessTokenDecoder $acccessTokenDecoder;

    /**
     * Scopes from the given AccessToken
     *
     * @var array
     */
    private array $scopes;

    /**
     * LocalIntrospector constructor.
     *
     * @param  \Ndberg\IntrospectionClient\AccessTokenDecoder  $acccessTokenDecoder
     */
    public function __construct(AccessTokenDecoder $acccessTokenDecoder)
    {
        $this->acccessTokenDecoder = $acccessTokenDecoder;
    }

    /**
     * Introspects the token with the local decoder
     *
     * @return $this
     */
    public function introspect()
    {
        // Decodes and checkes signature, exp, etc.
        try {
            $accessTokenDecoder = $this->acccessTokenDecoder->decodeAccessToken();
        } catch (ExpiredException $exception) {
            abort('401', 'Expired Token');
        } catch (\Exception $exception) {
            abort('401', 'Invalid token');
        }

        return $this;
    }

    /**
     * Checks required scopes
     *
     * @param  array  $requiredScopes
     *
     * @return \Ndberg\IntrospectionClient\LocalIntrospector
     * @throws \Ndberg\IntrospectionClient\Exceptions\MissingScopeException
     */
    public function mustHaveScopes(array $requiredScopes = [])
    {
        if ($this->scopes = ['*']) {
            return $this;
        }

        $missingScopes = array_diff($requiredScopes, $this->scopes);

        if (count($missingScopes) > 0) {
            throw new MissingScopeException($missingScopes);
        }

        return $this;
    }
}
