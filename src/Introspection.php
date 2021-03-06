<?php


namespace Ndberg\IntrospectionClient;


use Illuminate\Cache\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

/**
 * Class Introspection
 *
 * @package Ndberg\IntrospectionClient
 */
class Introspection
{
    /**
     * @var string
     */
    protected string $userModelClass = User::class;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected Collection $scopes;

    /**
     * User Model
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    protected $user;

    /**
     * @var \Illuminate\Cache\Repository
     */
    protected Repository $cache;

    /**
     * Introspection constructor.
     *
     * @param  \Illuminate\Cache\Repository  $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get to a user related data, which was delivered
     * by the introspection endpoint
     *
     * @param  string  $dataKey
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getUserRelatedData(string $dataKey) : ?Collection
    {
        try {
            return collect($this->getUser()->{$dataKey});

        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getUser() : ?User
    {
        return $this->user;
    }

    /**
     * @param  array  $userData
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function setUser($userData): User
    {
        $this->user = $this->getUserModel();
        $this->user->forceFill($userData);

        return $this->user;
    }

    /**
     * @param  Collection  $scopes
     *
     * @return \Ndberg\IntrospectionClient\Introspection
     */
    public function setScopes($scopes = null)
    {
        $this->scopes = $scopes ?? collect([]);

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    protected function getUserModel() : Authenticatable
    {
        return new $this->userModelClass;
    }

    /**
     * Get the scopes of the inspected token
     *
     * @return \Illuminate\Support\Collection
     */
    public function getScopes(): ?Collection
    {
        return $this->scopes;
    }

    /**
     * Checks if the introspection contains a specific scope
     *
     * @param  string  $scope
     *
     * @return bool
     */
    public function containsScope(string $scope): bool
    {
        return $this->scopes->contains($scope);
    }

    /**
     * To get user related data directly
     *
     * @param $name
     * @param $arguments
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function __call($name, $arguments)
    {
        return $this->getUserRelatedData($name);
    }
}
