<?php
namespace Modules\CallingAgent\AI\Memory;

use Modules\CallingAgent\DTOs\CallerProfileData;
use Modules\CallingAgent\ValueObjects\StructuredCallOutcome;

final class CallerProfileMemory
{
    /** @var array<string,array> */
    private array $profiles = [];

    public function remember(CallerProfileData|array $profile): CallerProfileData
    {
        $profile = is_array($profile) ? CallerProfileData::fromArray($profile) : $profile;
        $key = $this->key($profile->phone, $profile->email);
        $this->profiles[$key] = array_replace_recursive($this->profiles[$key] ?? [], $profile->toArray(), [
            'last_seen_at' => date(DATE_ATOM),
        ]);
        return CallerProfileData::fromArray($this->profiles[$key]);
    }

    public function recall(?string $phone = null, ?string $email = null): ?CallerProfileData
    {
        $data = $this->profiles[$this->key($phone, $email)] ?? null;
        return $data ? CallerProfileData::fromArray($data) : null;
    }

    public function recordOutcome(?string $phone, ?string $email, StructuredCallOutcome $outcome): CallerProfileData
    {
        $key = $this->key($phone, $email);
        $current = $this->profiles[$key] ?? ['phone' => $phone, 'email' => $email];
        $current['last_outcome'] = $outcome->toArray();
        $current['last_seen_at'] = date(DATE_ATOM);
        $current['tags'] = array_values(array_unique(array_filter(array_merge($current['tags'] ?? [], [$outcome->intent, $outcome->leadQuality]))));
        $this->profiles[$key] = $current;
        return CallerProfileData::fromArray($current);
    }

    private function key(?string $phone, ?string $email): string
    {
        return strtolower(trim($phone ?: $email ?: 'anonymous'));
    }
}
