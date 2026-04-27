<?php
namespace Modules\CallingAgent\Services\Realtime;

use Modules\CallingAgent\Enums\RealtimeTurnState;

final class RealtimeConversationSession
{
    public function __construct(
        public string $id,
        public RealtimeTurnState $state = RealtimeTurnState::LISTENING,
        public array $context = [],
        public array $turns = [],
    ) {}

    public function transition(RealtimeTurnState $state, array $meta = []): void
    {
        $this->state = $state;
        $this->turns[] = ['state' => $state->value, 'meta' => $meta, 'at' => now_if_available()];
    }
}

if (! function_exists(__NAMESPACE__ . '\now_if_available')) {
    function now_if_available(): string { return function_exists('now') ? (string) now() : date(DATE_ATOM); }
}
