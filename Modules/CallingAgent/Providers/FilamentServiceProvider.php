<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (!class_exists(\Filament\Panel::class)) {
            return;
        }

        // Resources are registered here for host apps that use auto-discovery.
        // For manual panel registration, add CallingAgentPlugin::make() to your panel.
    }
}
