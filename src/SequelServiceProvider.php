<?php

declare(strict_types=1);

namespace Akrista\Sequel;

use Akrista\Sequel\Database\DatabaseTraverser;
use Akrista\Sequel\Database\SequelDB;
use Akrista\Sequel\Http\Controllers\DatabaseController;
use Akrista\Sequel\Http\Requests\SequelDatabaseRequest;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Class SequelServiceProvider
 */
final class SequelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DatabaseTraverser::class, fn(): DatabaseTraverser => new DatabaseTraverser());

        $this->app->bind('sequeldb', fn(): SequelDB => new SequelDB());

        $this->app->singleton(DatabaseController::class, function (Application $app): DatabaseController {
            if ($app->runningInConsole()) {
                return new DatabaseController($app['request']);
            }

            return new DatabaseController($app[SequelDatabaseRequest::class]);
        });

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/sequel.php',
            'sequel'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(dirname(__DIR__) . '/resources/views', 'Sequel');

        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        $this->loadTranslationsFrom(
            dirname(__DIR__) . '/resources/lang/',
            'Sequel'
        );

        $this->publishes(
            [
                dirname(__DIR__) . '/resources/lang' => resource_path(
                    'lang/vendor/sequel'
                ),
            ],
            'sequel-lang'
        );

        $this->publishes(
            [
                dirname(__DIR__) . '/config/sequel.php' => config_path(
                    'sequel.php'
                ),
            ],
            'sequel-config'
        );

        $this->publishes(
            [
                dirname(__DIR__) . '/public' => public_path('vendor/sequel'),
            ],
            'sequel-assets'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\UpdateCommand::class,
                Commands\InstallCommand::class,
            ]);
        }
    }
}
