<?php

namespace Modules\TitanChatbot\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\DTOs\MessagePayload;
use Modules\TitanChatbot\Services\ConversationRouter;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, int $channelId): Response
    {
        try {
            $update  = $request->all();
            $message = $update['message'] ?? $update['edited_message'] ?? null;

            if (! $message) {
                return response('', 200);
            }

            $sessionId = (string) ($message['from']['id'] ?? 'unknown');
            $text      = $message['text'] ?? '';

            $payload = MessagePayload::fromArray([
                'chatbot_id' => $this->resolveChatbotId($channelId),
                'session_id' => $sessionId,
                'channel'    => 'telegram',
                'message'    => $text,
                'metadata'   => $update,
            ]);

            app(ConversationRouter::class)->route($payload);
        } catch (\Throwable $e) {
            Log::error('TelegramWebhook: failed', [
                'channel_id' => $channelId,
                'error'      => $e->getMessage(),
            ]);
        }

        return response('', 200);
    }

    /**
     * Respond to Telegram's webhook verification (not required by Telegram, kept for tooling).
     */
    public function verify(Request $request, int $channelId): JsonResponse
    {
        return response()->json(['ok' => true, 'channel_id' => $channelId]);
    }

    private function resolveChatbotId(int $channelId): int
    {
        try {
            if (class_exists(\Modules\TitanChatbot\Models\ChatbotChannel::class)) {
                $channel = \Modules\TitanChatbot\Models\ChatbotChannel::find($channelId);
                if ($channel) {
                    return (int) $channel->chatbot_id;
                }
            }
        } catch (\Throwable) {
            // Fall through to default
        }

        return $channelId;
    }
}
