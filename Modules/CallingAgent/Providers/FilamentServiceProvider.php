<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CallingAgent\Filament\Plugin\CallingAgentPlugin;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (class_exists(\Filament\FilamentManager::class) || class_exists(\Filament\Panel::class)) {
            // Plugin is registered via Filament panel discovery or explicit panel configuration
        }
    }
}
