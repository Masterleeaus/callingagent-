<?php

namespace Modules\CallingAgent\Filament\Plugin;

use Modules\CallingAgent\Providers\FilamentServiceProvider;

/**
 * Filament plugin for CallingAgent.
 *
 * Register it in your panel provider:
 *
 *   $panel->plugins([
 *       \Modules\CallingAgent\Filament\Plugin\CallingAgentPlugin::make(),
 *   ])
 *
 * This works with both Filament v2 and v3. In v3 the class can optionally
 * implement \Filament\Panel\Contracts\Plugin; in v2 it is just a plain object.
 */
class CallingAgentPlugin
{
    public static function make(): static
    {
        return new static();
    }

    public function getId(): string
    {
        return 'calling-agent';
    }

    /**
     * Called by Filament v3 when the plugin is registered with a panel.
     * Registers all module resources with the panel.
     */
    public function register(\Filament\Panel $panel): void
    {
        $panel->resources(FilamentServiceProvider::resources());
    }

    /**
     * Called by Filament v3 after the panel has booted.
     */
    public function boot(\Filament\Panel $panel): void
    {
        // Nothing additional needed at boot time.
    }
}
