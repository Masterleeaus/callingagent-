<?php

namespace Modules\TitanGo\Providers;

use Illuminate\Support\ServiceProvider;

class TitanGoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../Config/titango.php', 'titango');
        $this->mergeConfigFrom(__DIR__ . '/../Config/voice_actions.php', 'titango.voice_actions');
        $this->mergeConfigFrom(__DIR__ . '/../Config/permissions.php', 'titango.permissions');
    }

    public function boot(): void
    {
        // Worksuite helper convention (if present)
        if (function_exists('module_enabled') && !module_enabled('titango')) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'titango');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
