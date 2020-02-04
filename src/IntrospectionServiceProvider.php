<?php

namespace Ndberg\IntrospectionClient;

use Illuminate\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Ndberg\IntrospectionClient\Middleware\VerifyAccessToken;

class IntrospectionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->aliasMiddleware(VerifyAccessToken::class, 'VerifyAccessToken');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('introspection-client.php'),
            ], 'config');
        }

        $this->bootIntrospection();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'introspection-client');
    }

    /**
     * Register the Middleware to a middlewareGroup
     *
     * @param  string  $middleware
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function registerMiddleware($middleware)
    {
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('api', $middleware);
    }

    /**
     * Alias the Middleware that it can be added in the main project
     *
     * @param  string  $middleware
     * @param  string  $alias
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function aliasMiddleware(string $middleware, string $alias)
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware($alias, $middleware);
    }

    /**
     * Boots the Introspection classes
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function bootIntrospection()
    {
        $cache = $this->app->make(Cache::class);
        $config = Config::get('introspection-client');

        $this->app->singleton(IntrospectionClient::class, function() use($config, $cache) {
            return new IntrospectionClient($config, $cache);
        });

        $this->app->singleton(Introspection::class, function() use($cache) {
            return new Introspection($cache);
        });
    }
}
