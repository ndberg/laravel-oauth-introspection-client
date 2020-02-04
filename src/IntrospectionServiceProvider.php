<?php

namespace Ndberg\IntrospectionClient;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Ndberg\IntrospectionClient\Middleware\VerifyAccessToken;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class IntrospectionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-oauth-introspection-client');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-oauth-introspection-client');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->aliasMiddleware(VerifyAccessToken::class, 'VerifyAccessToken');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('introspection-client.php'),
            ], 'config');

            $this->loadMigrationsFrom(__DIR__ . '/../resources/Migrations/');

            // Publishing Migrations
            // TODO -> does publish multiple times! WHY?
            if (! class_exists('CreateUsersTable')){
                $this->publishes([
                    __DIR__.'/../database/migrations/create_users_table.stub.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_users_table.php'),
                ], 'migrations');
            }
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

        // Register the main class to use with the facade
        //$this->app->singleton('laravel-oauth-introspection-client', function () {
        //    return new IntrospectionServiceProvider();
        //});
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
        $request = $this->app->make(Request::class);
        $cache = $this->app->make(Cache::class);
        $config = Config::get('introspection-client');

        $client = new IntrospectionClient($config, $cache);
        $introspect = new Introspection($client, $request);

        $this->app->singleton(IntrospectionClient::class, function() use($client) {
            return $client;
        });

        $this->app->singleton(Introspection::class, function() use($introspect) {
            return $introspect;
        });

//        Auth::extend('introspect', function () use($introspect) {
//            return new Guard\IntrospectGuard($introspect);
//        });
    }
}
