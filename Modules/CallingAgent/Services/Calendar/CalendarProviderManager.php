<?php
namespace Modules\CallingAgent\Services\Calendar;

use Modules\CallingAgent\Contracts\CalendarProvider;

final class CalendarProviderManager
{
    /** @var array<string,CalendarProvider> */
    private array $providers;

    public function __construct(?array $providers = null)
    {
        $this->providers = $providers ?? [
            'google' => new GoogleCalendarProvider(),
            'outlook' => new OutlookCalendarProvider(),
            'caldav' => new CalDavCalendarProvider(),
        ];
    }

    public function provider(string $name): CalendarProvider
    {
        return $this->providers[$name] ?? $this->providers['google'];
    }

    public function availability(array $query): array
    {
        return $this->provider($query['provider'] ?? 'google')->availability($query);
    }

    public function book(array $payload): array
    {
        return $this->provider($payload['provider'] ?? 'google')->book($payload);
    }
}
