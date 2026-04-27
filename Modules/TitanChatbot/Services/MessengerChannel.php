<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Contracts\ChannelDriver;

class MessengerChannel implements ChannelDriver
{
    public function handle(array $payload): string
    {
        if (class_exists(\Modules\TitanChatbot\Channels\Messenger\System\Services\MessengerConversationService::class)) {
            return $this->handleViaMessengerService($payload);
        }

        return $this->handleDirect($payload);
    }

    private function handleViaMessengerService(array $payload): string
    {
        try {
            /** @var \Modules\TitanChatbot\Channels\Messenger\System\Services\MessengerConversationService $service */
            $service = app(\Modules\TitanChatbot\Channels\Messenger\System\Services\MessengerConversationService::class);

            $service->setChatbotId((int) ($payload['chatbot_id'] ?? 0));
            $service->setPayload($payload['metadata'] ?? $payload);

            if (isset($payload['channel_id'])) {
                $service->setChannelId((int) $payload['channel_id']);
            }

            $service->storeConversation();
            $service->storeHistory();
            $service->handle();

            return 'ok';
        } catch (\Throwable $e) {
            Log::error('MessengerChannel: service delegate failed.', ['error' => $e->getMessage()]);

            return $this->handleDirect($payload);
        }
    }

    private function handleDirect(array $payload): string
    {
        $message = $payload['message'] ?? '';

        /** @var GeneratorBridge $generator */
        $generator = app(GeneratorBridge::class);

        return $generator
            ->setChatbot($payload['chatbot'] ?? null)
            ->generate($message);
    }
}
