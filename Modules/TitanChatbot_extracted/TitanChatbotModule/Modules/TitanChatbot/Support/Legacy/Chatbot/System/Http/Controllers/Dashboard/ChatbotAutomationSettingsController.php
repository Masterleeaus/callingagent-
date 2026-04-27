<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Dashboard;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotToolSetting;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowSetting;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowRun;
use App\Extensions\Chatbot\System\Workflow\WorkflowRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotAutomationSettingsController
{
    public function __construct(protected WorkflowRegistry $registry = new WorkflowRegistry()) {}

    public function index(Chatbot $chatbot): View
    {
        // Workflows
        $allWorkflows = $this->registry->all();
        $wfSettings = ChatbotWorkflowSetting::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->get()
            ->keyBy('workflow_key');

        $workflowRows = [];
        foreach ($allWorkflows as $key => $wf) {
            $enabled = (bool)($wf['default_enabled'] ?? true);
            if ($wfSettings->has($key)) {
                $enabled = (bool) $wfSettings->get($key)->enabled;
            }
            $workflowRows[] = [
                'key' => (string) $key,
                'name' => (string)($wf['name'] ?? $key),
                'category' => (string)($wf['category'] ?? 'general'),
                'enabled' => $enabled,
                'requires_confirmation' => (bool)($wf['requires_confirmation'] ?? true),
            ];
        }

        // Tools
        $allTools = $this->registry->toolsAll();
        $toolSettings = ChatbotToolSetting::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->get()
            ->keyBy('tool_key');

        $toolRows = [];
        foreach ($allTools as $key => $tool) {
            $enabled = true;
            if ($toolSettings->has($key)) {
                $enabled = (bool) $toolSettings->get($key)->enabled;
            }
            $toolRows[] = [
                'key' => (string) $key,
                'name' => (string)($tool['name'] ?? $key),
                'category' => (string)($tool['category'] ?? 'general'),
                'enabled' => $enabled,
                'requires_confirmation' => (bool)($tool['requires_confirmation'] ?? true),
                'description' => (string)($tool['description'] ?? ''),
            ];
        }

        $runs = ChatbotWorkflowRun::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('chatbot::dashboard/automation/index', [
            'chatbot' => $chatbot,
            'workflowRows' => $workflowRows,
            'toolRows' => $toolRows,
            'runs' => $runs,
        ]);
    }

    public function update(Request $request, Chatbot $chatbot): RedirectResponse
    {
        // Update workflow enablement
        $enabledWorkflows = $this->normalizeEnabledKeys($request->input('workflows', []));
        foreach ($this->registry->all() as $key => $_wf) {
            $enabled = in_array((string) $key, $enabledWorkflows, true);
            ChatbotWorkflowSetting::query()->updateOrCreate(
                ['chatbot_id' => $chatbot->getKey(), 'workflow_key' => (string) $key],
                ['enabled' => $enabled]
            );
        }

        // Update tool enablement
        $enabledTools = $this->normalizeEnabledKeys($request->input('tools', []));
        foreach ($this->registry->toolsAll() as $key => $_tool) {
            $enabled = in_array((string) $key, $enabledTools, true);
            ChatbotToolSetting::query()->updateOrCreate(
                ['chatbot_id' => $chatbot->getKey(), 'tool_key' => (string) $key],
                ['enabled' => $enabled]
            );
        }

        // Gateway/tool runner settings (webhook-first)
        $chatbot->external_endpoint_url = (string) $request->input('gateway.external_endpoint_url', $chatbot->external_endpoint_url ?? '');
        $chatbot->external_auth_type = (string) $request->input('gateway.external_auth_type', $chatbot->external_auth_type ?? '');
        $chatbot->external_auth_token = (string) $request->input('gateway.external_auth_token', $chatbot->external_auth_token ?? '');
        $chatbot->external_signing_secret = (string) $request->input('gateway.external_signing_secret', $chatbot->external_signing_secret ?? '');
        $chatbot->external_timeout_ms = (int) $request->input('gateway.external_timeout_ms', $chatbot->external_timeout_ms ?? 15000);
        $chatbot->limit_per_minute = (int) $request->input('gateway.limit_per_minute', $chatbot->limit_per_minute ?? 60);
        $chatbot->save();

        return back()->with('success', 'Automation settings updated.');
    }

    /** @param mixed $items @return array<int,string> */
    protected function normalizeEnabledKeys(mixed $items): array
    {
        $enabled = [];
        if (is_array($items)) {
            foreach ($items as $key => $val) {
                if ($val === '1' || $val === 1 || $val === true || $val === 'on') {
                    $enabled[] = (string) $key;
                }
            }
        }
        return $enabled;
    }
}
