<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;

class AutomationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Console commands can be registered here if a commands list is available
        }
    }
}
