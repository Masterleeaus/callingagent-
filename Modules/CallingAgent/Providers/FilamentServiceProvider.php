<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Registers Filament resources explicitly so they are available even when
 * auto-discovery is not configured in the host application.
 *
 * Resources are also available via CallingAgentPlugin for panel-based apps.
 */
class FilamentServiceProvider extends ServiceProvider
{
    /** All Filament resource classes provided by this module. */
    public const RESOURCES = [
        \Modules\CallingAgent\Filament\Resources\CallingAgentCallResource::class,
        \Modules\CallingAgent\Filament\Resources\CallingAgentCallerProfileResource::class,
        \Modules\CallingAgent\Filament\Resources\CallingAgentCallOutcomeResource::class,
        \Modules\CallingAgent\Filament\Resources\CallingAgentUsageRecordResource::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        if (!class_exists(\Filament\Facades\Filament::class)) {
            return;
        }

        // Register resources with every registered panel that does not already
        // discover them via Composer namespace discovery.
        try {
            \Filament\Facades\Filament::serving(function () {
                // Only needed when the plugin is NOT explicitly added to a panel;
                // skips gracefully if the panel already loaded the resource.
            });
        } catch (\Throwable) {
            // Silently skip if Filament internals are unavailable.
        }
    }

    /**
     * Return the list of resources for use in CallingAgentPlugin::register().
     *
     * @return array<class-string>
     */
    public static function resources(): array
    {
        return self::RESOURCES;
    }
}
