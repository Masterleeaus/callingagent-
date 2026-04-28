<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Drivers;

use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Usage\UsageRecord;

interface DriverInterface
{
    /**
     * Send messages to the AI provider and return a normalized response array.
     */
    public function send(array $messages, array $config = []): array;

    /**
     * Stream messages, calling $onChunk for each chunk of content.
     */
    public function sendStream(array $messages, array $config, callable $onChunk): void;

    /**
     * Return the usage record from the last send() call.
     */
    public function getUsage(): UsageRecord;

    /**
     * Return the canonical driver name.
     */
    public function getName(): string;

    /**
     * Check whether this driver is usable in the current environment.
     */
    public function isAvailable(): bool;
}
