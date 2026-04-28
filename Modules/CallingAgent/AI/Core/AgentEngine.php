<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Core;

use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;

interface AgentEngine
{
    /**
     * Generate an AssistantMessage from a MessageCollection using the given config.
     * Named `generate` (not `send`) to avoid signature collision with DriverInterface::send.
     */
    public function generate(MessageCollection $messages, AgentConfig $config): AssistantMessage;

    public function supports(string $driver): bool;
}
