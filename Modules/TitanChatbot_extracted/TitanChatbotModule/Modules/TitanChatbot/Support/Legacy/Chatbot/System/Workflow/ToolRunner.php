<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Workflow;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotConversation;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowRun;
use App\Extensions\Chatbot\System\Workflow\Tools\AuditLogTool;
use App\Extensions\Chatbot\System\Workflow\Tools\CalendarCreateTool;
use App\Extensions\Chatbot\System\Workflow\Tools\FileRequestTool;
use App\Extensions\Chatbot\System\Workflow\Tools\NotifyChannelTool;
use App\Extensions\Chatbot\System\Workflow\Tools\NotifyEmailTool;
use App\Extensions\Chatbot\System\Workflow\Tools\NotifySmsTool;
use App\Extensions\Chatbot\System\Workflow\Tools\RecordCreateTool;
use App\Extensions\Chatbot\System\Workflow\Tools\RecordFindTool;
use App\Extensions\Chatbot\System\Workflow\Tools\RecordUpdateTool;
use App\Extensions\Chatbot\System\Workflow\Tools\WebhookCallTool;

class ToolRunner
{
    public function __construct(
        protected WebhookCallTool $webhookCallTool = new WebhookCallTool(),
        protected AuditLogTool $auditLogTool = new AuditLogTool(),
        protected RecordCreateTool $recordCreateTool = new RecordCreateTool(),
        protected RecordUpdateTool $recordUpdateTool = new RecordUpdateTool(),
        protected RecordFindTool $recordFindTool = new RecordFindTool(),
        protected NotifyEmailTool $notifyEmailTool = new NotifyEmailTool(),
        protected NotifySmsTool $notifySmsTool = new NotifySmsTool(),
        protected NotifyChannelTool $notifyChannelTool = new NotifyChannelTool(),
        protected CalendarCreateTool $calendarCreateTool = new CalendarCreateTool(),
        protected FileRequestTool $fileRequestTool = new FileRequestTool(),
        protected WorkflowRegistry $registry = new WorkflowRegistry(),
    ) {}

    /**
     * @param array<string,mixed> $step
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function runStep(Chatbot $chatbot, ?ChatbotConversation $conversation, ChatbotWorkflowRun $run, array $step, array $context = []): array
    {
        $tool = (string)($step['tool'] ?? '');

        // Tool enable/disable gate (per chatbot)
        if ($tool !== '' && $tool !== 'webhook.call' && $tool !== 'audit.log') {
            if (!$this->registry->toolIsEnabledFor($chatbot, $tool)) {
                return [
                    'ok' => false,
                    'error' => 'tool_disabled',
                    'tool' => $tool,
                ];
            }
        }

        if ($tool === 'webhook.call') {
            return $this->webhookCallTool->handle($chatbot, $conversation, $run, $step, $context);
        }
        if ($tool === 'audit.log') {
            return $this->auditLogTool->handle($chatbot, $conversation, $run, $step, $context);
        }

        // First-class tools (provide a stable payload contract + validation)
        return match ($tool) {
            'record.create' => $this->recordCreateTool->handle($chatbot, $conversation, $run, $step, $context),
            'record.update' => $this->recordUpdateTool->handle($chatbot, $conversation, $run, $step, $context),
            'record.find' => $this->recordFindTool->handle($chatbot, $conversation, $run, $step, $context),
            'notify.email' => $this->notifyEmailTool->handle($chatbot, $conversation, $run, $step, $context),
            'notify.sms' => $this->notifySmsTool->handle($chatbot, $conversation, $run, $step, $context),
            'notify.channel' => $this->notifyChannelTool->handle($chatbot, $conversation, $run, $step, $context),
            'calendar.create' => $this->calendarCreateTool->handle($chatbot, $conversation, $run, $step, $context),
            'file.request' => $this->fileRequestTool->handle($chatbot, $conversation, $run, $step, $context),
            default => null,
        } ?? $this->fallback($chatbot, $conversation, $run, $step, $context);
    }

    /** @return array<string,mixed> */
    protected function fallback(Chatbot $chatbot, ?ChatbotConversation $conversation, ChatbotWorkflowRun $run, array $step, array $context = []): array
    {
        $tool = (string)($step['tool'] ?? '');

        // Webhook-first: for any allowlisted tool, call the external tool gateway
        // using the tool name as the action. This keeps the internal implementation minimal
        // while preserving a stable workflow/tool contract.
        $allow = $this->registry->toolsAllowlist();
        if (in_array($tool, $allow, true)) {
            $step['action'] = $step['action'] ?? $tool;
            // Pass the tool name through for external dispatch.
            $step['tool'] = 'webhook.call';
            return $this->webhookCallTool->handle($chatbot, $conversation, $run, $step, $context);
        }

        return [
            'ok' => false,
            'error' => 'tool_not_implemented',
            'tool' => $tool,
        ];
    }
}
