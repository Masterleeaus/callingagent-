<?php

namespace Modules\TitanChatbot\Services;

use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Contracts\ChannelDriver;

class TelegramChannel implements ChannelDriver
{
    public function handle(array $payload): string
    {
        if (class_exists(\Modules\TitanChatbot\Channels\Telegram\System\Services\Telegram\TelegramConversationService::class)) {
            return $this->handleViaTelegramService($payload);
        }

        return $this->handleDirect($payload);
    }

    private function handleViaTelegramService(array $payload): string
    {
        try {
            /** @var \Modules\TitanChatbot\Channels\Telegram\System\Services\Telegram\TelegramConversationService $service */
            $service = app(\Modules\TitanChatbot\Channels\Telegram\System\Services\Telegram\TelegramConversationService::class);

            $service->setChatbotId((int) ($payload['chatbot_id'] ?? 0));
            $service->setPayload($payload['metadata'] ?? $payload);

            if (isset($payload['channel_id'])) {
                $service->setChannelId((int) $payload['channel_id']);
            }

            $conversation = $service->storeConversation();

            if (! $conversation) {
                return "Sorry, I can't answer right now.";
            }

            $service->handleTelegram();

            return 'ok';
        } catch (\Throwable $e) {
            Log::error('TelegramChannel: service delegate failed.', ['error' => $e->getMessage()]);

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
