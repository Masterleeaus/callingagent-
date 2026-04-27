<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Event;
use Modules\TitanChatbot\DTOs\MessagePayload;
use Modules\TitanChatbot\Events\ConversationStarted;
use Modules\TitanChatbot\Models\Chatbot;

class ConversationRouter
{
    public function __construct(
        private readonly ChannelRouter $channelRouter,
        private readonly ConversationSessionManager $sessionManager,
    ) {}

    public function route(MessagePayload $payload): string
    {
        $session = $this->sessionManager->findOrCreate(
            $payload->sessionId,
            $payload->chatbotId,
            [
                'customer_id' => $payload->customerId,
                'tenant_id'   => $payload->tenantId,
                'channel'     => $payload->channel,
            ]
        );

        $chatbot = Chatbot::find($payload->chatbotId);

        if ($this->isNewSession($session)) {
            Event::dispatch(new ConversationStarted($payload, $chatbot));
        }

        $this->sessionManager->touchSession($payload->sessionId, $payload->chatbotId);

        $routePayload = array_merge($payload->toArray(), [
            'session'    => $session,
            'chatbot'    => $chatbot,
        ]);

        return $this->channelRouter->route($payload->channel, $routePayload);
    }

    private function isNewSession(array $session): bool
    {
        return ($session['started_at'] ?? null) === ($session['last_seen_at'] ?? null);
    }
}
