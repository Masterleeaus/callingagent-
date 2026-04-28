<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleBootServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Config' => config_path('calling-agent'),
            ], 'calling-agent-config');
            $this->publishes([
                __DIR__.'/../Resources/assets' => public_path('vendor/calling-agent'),
            ], 'calling-agent-assets');
            $this->publishes([
                __DIR__.'/../Database/migrations' => database_path('migrations'),
            ], 'calling-agent-migrations');
        }
    }
}
