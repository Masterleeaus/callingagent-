<?php

namespace Modules\CallingAgent\Filament\Plugin;

use Modules\CallingAgent\Providers\FilamentServiceProvider;

/**
 * Filament plugin for CallingAgent.
 *
 * Register it in your panel provider (Filament v3):
 *
 *   $panel->plugins([
 *       \Modules\CallingAgent\Filament\Plugin\CallingAgentPlugin::make(),
 *   ])
 *
 * The register() and boot() methods use \Filament\Panel type-hints which are
 * only available in Filament v3. If you are on Filament v2, simply call
 * FilamentServiceProvider::resources() to retrieve the resource class list
 * and pass it to $panel->resources() yourself.
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
     *
     * Requires Filament v3 (\Filament\Panel must exist).
     */
    public function register(\Filament\Panel $panel): void
    {
        $panel->resources(FilamentServiceProvider::resources());
    }

    /**
     * Called by Filament v3 after the panel has booted.
     *
     * Requires Filament v3 (\Filament\Panel must exist).
     */
    public function boot(\Filament\Panel $panel): void
    {
        // Nothing additional needed at boot time.
    }
}
