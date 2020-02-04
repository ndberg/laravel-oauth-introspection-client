<?php

namespace Ndberg\IntrospectionClient;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Ndberg\IntrospectionClient\Exceptions\MissingScopeException;

/**
 * Class Introspection
 *
 * @package Ndberg\IntrospectionClient
 */
class RemoteIntrospector
{
    protected IntrospectionClient $client;
    protected $result;
    protected string $userDataKey = 'user';
    protected string $userModelClass = User::class;

    /**
     * Introspection constructor.
     *
     * @param  \Ndberg\IntrospectionClient\IntrospectionClient  $client
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(IntrospectionClient $client, Request $request)
    {
        $this->client = $client;
        $this->request = $request;
    }

    /**
     * @param  array  $requiredScopes
     *
     * @return \Ndberg\IntrospectionClient\RemoteIntrospector
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Ndberg\IntrospectionClient\Exceptions\MissingScopeException
     */
    public function mustHaveScopes(array $requiredScopes = [])
    {
        $result = $this->getIntrospectionResult();
        $givenScopes = explode(' ', $result['scope']);
        $missingScopes = array_diff($requiredScopes, $givenScopes);

        if ($givenScopes == ['*']) {
            return $this;
        }

        if (count($missingScopes) > 0) {
            throw new MissingScopeException($missingScopes);
        }

        return $this;
    }

    /**
     * Get to a user related data, which was delivered
     * by the introspection endpoint
     *
     * @param  string  $dataKey
     *
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function getUserRelatedData(string $dataKey)
    {
        return collect($this->getUser()->{$dataKey});
    }

    /**
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function getIntrospectionResult()
    {
        if ($this->result !== null) {
            return $this->result;
        }

        $token = $this->request->bearerToken();

        if (empty($token)) {
            throw new AuthenticationException();
        }

        try {
            $this->result = $this->client->introspect($token);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $result = json_decode((string) $e->getResponse()->getBody(), true);

                if (isset($result['error'])) {
                    throw new AuthenticationException($result['error']['title'] ?? '');
                }
            }

            throw new AuthenticationException($e->getMessage());
        }

        return $this->result;
    }

    /**
     * @param  string  $key
     *
     * @return $this
     */
    public function setUserDataKey(string $key) : self
    {
        $this->userDataKey = $key;

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function getUser()
    {
        $result = $this->getIntrospectionResult();

        if (isset($result[$this->userDataKey]) && ! empty($result[$this->userDataKey])) {
            $user = $this->getUserModel();
            $user->forceFill($result[$this->userDataKey]);

            return $user;
        }

        return null;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function getUserData()
    {
        $result = $this->getIntrospectionResult();

        if (isset($result[$this->userDataKey]) && ! empty($result[$this->userDataKey])) {
            return $result[$this->userDataKey];
        }

        return null;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getUserModel() : Authenticatable
    {
        $class = $this->getUserModelClass();

        return new $class();
    }

    /**
     * @return string
     */
    public function getUserModelClass() : string
    {
        return $this->userModelClass;
    }

    /**
     * @param  string  $class
     *
     * @return $this
     */
    public function setUserModelClass(string $class) : self
    {
        $this->userModelClass = $class;

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function verifyToken()
    {
        if ($this->tokenIsNotActive()) {
            throw new AuthenticationException('Invalid token');
        }

        return $this;
    }

    /**
     * @return bool
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function tokenIsNotActive() : bool
    {
        return ! $this->tokenIsActive();
    }

    /**
     * @return bool
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function tokenIsActive() : bool
    {
        $result = $this->getIntrospectionResult();

        return $result['active'] === true;
    }
}
