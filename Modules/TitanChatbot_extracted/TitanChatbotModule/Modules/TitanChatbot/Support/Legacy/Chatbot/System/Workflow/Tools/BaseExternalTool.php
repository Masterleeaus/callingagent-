<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Workflow\Tools;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowRun;
use App\Extensions\Chatbot\System\Workflow\WorkflowRegistry;

abstract class BaseExternalTool
{
    public function __construct(
        protected WebhookCallTool $webhook = new WebhookCallTool(),
        protected WorkflowRegistry $registry = new WorkflowRegistry(),
    ) {}

    abstract public function key(): string;

    /** @return array<string,mixed> */
    public function handle(Chatbot $chatbot, ?ChatbotConversation $conversation, ChatbotWorkflowRun $run, array $step, array $context = []): array
    {
        $def = $this->registry->tool($this->key());
        if ($def && isset($def['required_fields']) && is_array($def['required_fields'])) {
            $missing = [];
            $input = $this->mergedInput($run, $step);
            foreach ($def['required_fields'] as $field) {
                $field = (string) $field;
                if ($field === '') {
                    continue;
                }
                if (!array_key_exists($field, $input) || $input[$field] === null || $input[$field] === '') {
                    $missing[] = $field;
                }
            }
            if ($missing) {
                return [
                    'ok' => false,
                    'error' => 'missing_required_fields',
                    'tool' => $this->key(),
                    'missing' => $missing,
                ];
            }
        }

        $step['action'] = $step['action'] ?? $this->key();
        $step['tool'] = 'webhook.call';
        return $this->webhook->handle($chatbot, $conversation, $run, $step, $context);
    }

    /** @return array<string,mixed> */
    protected function mergedInput(ChatbotWorkflowRun $run, array $step): array
    {
        $input = [];
        if (is_array($run->input)) {
            $input = $run->input;
        }
        if (isset($step['input']) && is_array($step['input'])) {
            $input = array_merge($input, $step['input']);
        }
        return $input;
    }
}
