<?php
namespace Modules\CallingAgent\Services\Providers;

use InvalidArgumentException;

final class VoiceProviderManager
{
    public function __construct(private array $providers = []) {}

    public function register(string $key, object $provider): self
    {
        $this->providers[$key] = $provider;
        return $this;
    }

    public function get(string $key): object
    {
        if (! isset($this->providers[$key])) {
            throw new InvalidArgumentException("Voice provider [$key] is not registered.");
        }
        return $this->providers[$key];
    }

    public function all(): array { return $this->providers; }
}
