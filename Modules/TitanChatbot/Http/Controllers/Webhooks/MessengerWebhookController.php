<?php

namespace Modules\TitanChatbot\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\DTOs\MessagePayload;
use Modules\TitanChatbot\Services\ConversationRouter;

class MessengerWebhookController extends Controller
{
    /**
     * Facebook sends a GET request to verify the webhook endpoint.
     * Respond with the hub.challenge value when the verify token matches.
     */
    public function verify(Request $request, int $channelId): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = config('titan-chatbot.channels.messenger.verify_token', '');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return response((string) $challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request, int $channelId): Response
    {
        try {
            $body = $request->all();

            if (($body['object'] ?? '') !== 'page') {
                return response('', 200);
            }

            foreach ($body['entry'] ?? [] as $entry) {
                foreach ($entry['messaging'] ?? [] as $event) {
                    $this->processEvent($event, $channelId);
                }
            }
        } catch (\Throwable $e) {
            Log::error('MessengerWebhook: failed', [
                'channel_id' => $channelId,
                'error'      => $e->getMessage(),
            ]);
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processEvent(array $event, int $channelId): void
    {
        $senderId = $event['sender']['id'] ?? 'unknown';
        $text     = $event['message']['text'] ?? '';

        if ($text === '') {
            return;
        }

        $payload = MessagePayload::fromArray([
            'chatbot_id' => $this->resolveChatbotId($channelId),
            'session_id' => (string) $senderId,
            'channel'    => 'messenger',
            'message'    => $text,
            'metadata'   => $event,
        ]);

        app(ConversationRouter::class)->route($payload);
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
