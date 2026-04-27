<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Workflow\Tools;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowRun;

class AuditLogTool
{
    /**
     * @param array<string,mixed> $step
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function handle(Chatbot $chatbot, ?ChatbotConversation $conversation, ChatbotWorkflowRun $run, array $step, array $context = []): array
    {
        if (!$conversation) {
            return ['ok' => true, 'skipped' => true];
        }

        $summary = [
            'workflow_key' => $run->workflow_key,
            'run_id' => $run->getKey(),
            'status' => $run->status,
            'step' => $step,
        ];

        ChatbotHistory::create([
            'chatbot_id' => $chatbot->getKey(),
            'conversation_id' => $conversation->getKey(),
            'model' => 'workflow',
            'role' => 'system',
            'message' => json_encode($summary),
            'type' => $conversation->chatbot_channel ?? 'frame',
            'message_type' => 'tool',
            'content_type' => 'json',
            'created_at' => now(),
        ]);

        return ['ok' => true];
    }
}
