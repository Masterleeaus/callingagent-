<?php

namespace Modules\TitanChatbot\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanChatbot\DTOs\MessagePayload;
use Modules\TitanChatbot\Services\ConversationRouter;

class ConversationController extends Controller
{
    public function __construct(private readonly ConversationRouter $router) {}

    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'message'    => 'required|string',
            'channel'    => 'sometimes|string|max:100',
            'metadata'   => 'sometimes|array',
        ]);

        $payload = MessagePayload::fromArray(array_merge($validated, [
            'chatbot_id' => $id,
            'channel'    => $validated['channel'] ?? 'webchat',
            'tenant_id'  => $request->user()?->tenant_id,
        ]));

        $reply = $this->router->route($payload);

        return response()->json(['reply' => $reply]);
    }

    public function sendVoice(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'session_id'  => 'required|string',
            'audio_url'   => 'required|url',
            'metadata'    => 'sometimes|array',
        ]);

        $payload = MessagePayload::fromArray([
            'chatbot_id' => $id,
            'session_id' => $validated['session_id'],
            'channel'    => 'voice',
            'message'    => $validated['audio_url'],
            'tenant_id'  => $request->user()?->tenant_id,
            'metadata'   => $validated['metadata'] ?? [],
        ]);

        $reply = $this->router->route($payload);

        return response()->json(['reply' => $reply]);
    }

    public function history(Request $request, int $id): JsonResponse
    {
        $sessionId = $request->query('session_id');
        $limit     = (int) $request->query('limit', 50);

        $query = \Modules\TitanChatbot\Models\Conversation::where('chatbot_id', $id)
            ->with('messages')
            ->orderBy('started_at', 'desc')
            ->limit($limit);

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function train(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'content'     => 'required|string',
            'title'       => 'sometimes|string|max:255',
            'source_type' => 'sometimes|string|in:text,qa,pdf,url',
            'metadata'    => 'sometimes|array',
        ]);

        $pipeline = app(\Modules\TitanChatbot\Services\TrainingPipeline::class);
        $chunks = $pipeline->ingest(
            chatbotId:  $id,
            sourceType: $request->input('source_type', 'text'),
            content:    $request->input('content'),
            metadata:   $request->input('metadata', []),
        );

        return response()->json([
            'message'        => 'Training complete.',
            'chatbot_id'     => $id,
            'chunks_created' => $chunks,
        ]);
    }
}
