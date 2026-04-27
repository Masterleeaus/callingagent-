<?php

namespace Extensions\Connectors\System;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ConnectorsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config (if host app supports extension config loading)
        $this->mergeConfigFrom(__DIR__ . '/../config/connectors.php', 'connectors');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'connectors');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // If the host app supports publishing configs, this won't hurt.
        $this->publishes([
            __DIR__ . '/../config/connectors.php' => config_path('connectors.php'),
        ], 'connectors-config');

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        // Webhooks (no auth). Protect with X-CONNECTORS-SECRET header if you set one.
        Route::middleware(['web'])
            ->prefix('connectors/webhooks')
            ->group(function () {
                Route::post('/client-pack', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'webhookClientPack'])
                    ->name('connectors.webhooks.clientpack');
            });

        Route::middleware(['web', 'auth'])
            ->prefix('dashboard/admin')
            ->group(function () {
                Route::get('/connectors', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'index'])
                    ->name('connectors.index');

                Route::post('/connectors/provider/save', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'saveProvider'])
                    ->name('connectors.provider.save');

                Route::post('/connectors/provider/test', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'testProvider'])
                    ->name('connectors.provider.test');

                Route::post('/connectors/client-pack/run', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'runClientPack'])
                    ->name('connectors.clientpack.run');

                Route::post('/connectors/templates/save', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'saveTemplate'])
                    ->name('connectors.templates.save');

                Route::post('/connectors/templates/delete', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'deleteTemplate'])
                    ->name('connectors.templates.delete');

                Route::post('/connectors/templates/set-default', [\Extensions\Connectors\System\Http\Controllers\ConnectorsController::class, 'setDefaultTemplate'])
                    ->name('connectors.templates.default');
            });
    }
}
