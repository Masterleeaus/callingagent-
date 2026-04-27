<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Workflow\Tools;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotHistory;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowRun;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookCallTool
{
    /**
     * Webhook-first execution.
     *
     * @param array<string,mixed> $step
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function handle(Chatbot $chatbot, ?ChatbotConversation $conversation, ChatbotWorkflowRun $run, array $step, array $context = []): array
    {
        // Allow per-step override, otherwise fall back to the chatbot's configured external tool gateway.
        $url = (string)($step['url'] ?? ($chatbot->getAttribute('external_endpoint_url') ?? ''));
        if ($url === '') {
            return ['ok' => false, 'error' => 'missing_external_endpoint_url'];
        }

        $action = (string)($step['action'] ?? $run->workflow_key);
        $nonce = (string) Str::uuid();
        $ts = (int) now()->timestamp;

        $stepInput = [];
        if (is_array($run->input)) {
            $stepInput = $run->input;
        }
        if (isset($step['input']) && is_array($step['input'])) {
            $stepInput = array_merge($stepInput, $step['input']);
        }

        $payload = [
            'type' => 'chatbot_workflow',
            'action' => $action,
            'workflow_key' => $run->workflow_key,
            'run_id' => $run->getKey(),
            'step' => [
                'tool' => $step['tool'] ?? 'webhook.call',
                'action' => $step['action'] ?? $action,
                'name' => $step['name'] ?? null,
            ],
            'chatbot' => [
                'id' => $chatbot->getKey(),
                'uuid' => $chatbot->uuid ?? null,
            ],
            'conversation_id' => $conversation?->getKey(),
            'channel' => $conversation?->chatbot_channel ?? null,
            'input' => $stepInput,
            'context' => $context,
            'ts' => $ts,
            'nonce' => $nonce,
        ];

        $secret = (string)($chatbot->getAttribute('external_signing_secret') ?? '');
        $signature = $secret !== '' ? hash_hmac('sha256', json_encode($payload), $secret) : null;

        $req = Http::timeout((int)($chatbot->getAttribute('external_timeout_ms') ?? 15000) / 1000);

        $authType = (string)($chatbot->getAttribute('external_auth_type') ?? '');
        $authToken = (string)($chatbot->getAttribute('external_auth_token') ?? '');
        $headers = [
            'X-Chatbot-Workflow' => '1',
            'X-Workflow-Run-Id' => (string) $run->getKey(),
            'X-Workflow-Nonce' => $nonce,
            'X-Workflow-Ts' => (string) $ts,
        ];
        if ($signature) {
            $headers['X-Workflow-Signature'] = $signature;
        }
        if ($authType === 'bearer' && $authToken !== '') {
            $headers['Authorization'] = 'Bearer '.$authToken;
        } elseif ($authType === 'header' && $authToken !== '') {
            $headers['X-Auth-Token'] = $authToken;
        }

        try {
            $resp = $req->withHeaders($headers)->post($url, $payload);
            $result = [
                'ok' => $resp->successful(),
                'status' => $resp->status(),
                'body' => $resp->json() ?? $resp->body(),
            ];

            // Audit to conversation history (so the chat timeline reflects tool executions)
            if ($conversation) {
                ChatbotHistory::create([
                    'chatbot_id' => $chatbot->getKey(),
                    'conversation_id' => $conversation->getKey(),
                    'model' => 'workflow',
                    'role' => 'assistant',
                    'message' => json_encode([
                        'tool' => $step['action'] ?? $action,
                        'workflow_key' => $run->workflow_key,
                        'run_id' => $run->getKey(),
                        'request' => $payload,
                        'response' => $result,
                    ]),
                    'type' => (string)($conversation->chatbot_channel ?? 'frame'),
                    'message_type' => 'tool',
                    'content_type' => 'json',
                    'created_at' => now(),
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            $result = [
                'ok' => false,
                'error' => 'webhook_exception',
                'message' => $e->getMessage(),
            ];

            if ($conversation) {
                ChatbotHistory::create([
                    'chatbot_id' => $chatbot->getKey(),
                    'conversation_id' => $conversation->getKey(),
                    'model' => 'workflow',
                    'role' => 'assistant',
                    'message' => json_encode([
                        'tool' => $step['action'] ?? $action,
                        'workflow_key' => $run->workflow_key,
                        'run_id' => $run->getKey(),
                        'request' => $payload,
                        'response' => $result,
                    ]),
                    'type' => (string)($conversation->chatbot_channel ?? 'frame'),
                    'message_type' => 'tool',
                    'content_type' => 'json',
                    'created_at' => now(),
                ]);
            }

            return $result;
        }
    }
}
