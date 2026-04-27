<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Workflow\Tools;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowRun;
use App\Extensions\Chatbot\System\Workflow\WorkflowRegistry;

class FileRequestTool
{
    public function __construct(protected WorkflowRegistry $registry = new WorkflowRegistry()) {}

    /**
     * Request a file from the user.
     *
     * This does not upload anything by itself; it emits a tool-event into the chat history
     * so the frontend (or channel adapters) can present an upload prompt.
     *
     * @param array<string,mixed> $step
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function handle(Chatbot $chatbot, ?ChatbotConversation $conversation, ChatbotWorkflowRun $run, array $step, array $context = []): array
    {
        $input = [];
        if (is_array($run->input)) {
            $input = $run->input;
        }
        if (isset($step['input']) && is_array($step['input'])) {
            $input = array_merge($input, $step['input']);
        }

        $def = $this->registry->tool('file.request');
        $required = $def['required_fields'] ?? ['prompt'];
        $missing = [];
        foreach ((array) $required as $field) {
            $field = (string) $field;
            if (!array_key_exists($field, $input) || $input[$field] === null || $input[$field] === '') {
                $missing[] = $field;
            }
        }
        if ($missing) {
            return ['ok' => false, 'error' => 'missing_required_fields', 'tool' => 'file.request', 'missing' => $missing];
        }

        if (!$conversation) {
            return ['ok' => true, 'skipped' => true];
        }

        $payload = [
            'tool' => 'file.request',
            'workflow_key' => $run->workflow_key,
            'run_id' => $run->getKey(),
            'prompt' => (string)($input['prompt'] ?? 'Please upload a file.'),
            'accept' => $input['accept'] ?? null, // e.g. ['image/*','application/pdf']
            'max_files' => $input['max_files'] ?? 3,
            'context' => $context,
        ];

        ChatbotHistory::create([
            'chatbot_id' => $chatbot->getKey(),
            'conversation_id' => $conversation->getKey(),
            'model' => 'workflow',
            'role' => 'assistant',
            'message' => json_encode($payload),
            'type' => (string)($conversation->chatbot_channel ?? 'frame'),
            'message_type' => 'tool',
            'content_type' => 'json',
            'created_at' => now(),
        ]);

        return ['ok' => true, 'tool' => 'file.request'];
    }
}
