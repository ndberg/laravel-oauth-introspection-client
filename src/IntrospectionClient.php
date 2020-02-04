<?php

namespace Ndberg\IntrospectionClient;

use GuzzleHttp\Client;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\Repository as Cache;

/**
 * Class IntrospectionClient
 *
 * This class is the Client itself, used to connect
 * to the Introspection Server (Endpoint)
 *
 * @package Ndberg\IntrospectionClient
 */
class IntrospectionClient
{
    /**
     * @var string
     */
    protected string $accessTokenCacheKey = 'introspection_client_access_token';

    /**
     * @var \Illuminate\Cache\Repository
     */
    protected Cache $cache;

    /**
     * @var
     */
    protected $client;

    /**
     * @var array
     */
    protected array $config;

    /**
     * IntrospectionClient constructor.
     *
     * @param  array  $config
     * @param  \Illuminate\Cache\Repository  $cache
     */
    public function __construct(array $config, Cache $cache)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * @param  string  $token
     *
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function introspect(string $token)
    {
        $response = $this->getClient()->post($this->config['introspect_url'], [
            'form_params' => [
                'token_type_hint' => 'access_token',
                'token' => $token,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ],
        ]);

        \Log::warning('intrespect result: ', json_decode((string) $response->getBody(), true));

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient() : Client
    {
        if ($this->client === null) {
            $this->setClient(new Client());
        }

        return $this->client;
    }

    /**
     * @param  \GuzzleHttp\Client  $client
     *
     * @return $this
     */
    public function setClient(Client $client) : self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function getAccessToken() : string
    {
        $accessToken = $this->cache->get($this->accessTokenCacheKey);

        return $accessToken ?: $this->getNewAccessToken();
    }

    /**
     * @return string
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function getNewAccessToken() : string
    {
        $response = $this->getClient()->post($this->config['token_url'], [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'scope' => $this->config['scope'],
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);

        if (isset($result['access_token'])) {
            $accessToken = $result['access_token'];

            $this->cache->add($this->accessTokenCacheKey, $accessToken, (intVal($result['expires_in']) / 60) - 5);

            return $accessToken;
        }

        throw new AuthenticationException('Did not receive an access token');
    }
}
