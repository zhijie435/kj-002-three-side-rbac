<?php

namespace Shearerline;

use Illuminate\Support\ServiceProvider;

class ShearerlineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/shearerline.php',
            'shearerline'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/config/shearerline.php' => config_path('shearerline.php'),
        ], 'shearerline-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'shearerline-migrations');
    }
}
