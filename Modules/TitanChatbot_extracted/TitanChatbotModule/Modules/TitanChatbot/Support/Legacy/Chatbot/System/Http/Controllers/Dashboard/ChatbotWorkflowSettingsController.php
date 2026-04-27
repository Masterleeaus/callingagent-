<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Dashboard;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowSetting;
use App\Extensions\Chatbot\System\Workflow\WorkflowRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotWorkflowSettingsController
{
    public function __construct(protected WorkflowRegistry $registry = new WorkflowRegistry()) {}

    public function index(Chatbot $chatbot): View
    {
        $all = $this->registry->all();
        $settings = ChatbotWorkflowSetting::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->get()
            ->keyBy('workflow_key');

        $rows = [];
        foreach ($all as $key => $wf) {
            $enabled = (bool)($wf['default_enabled'] ?? true);
            if ($settings->has($key)) {
                $enabled = (bool)$settings->get($key)->enabled;
            }
            $rows[] = [
                'key' => $key,
                'name' => (string)($wf['name'] ?? $key),
                'category' => (string)($wf['category'] ?? 'general'),
                'enabled' => $enabled,
                'requires_confirmation' => (bool)($wf['requires_confirmation'] ?? true),
            ];
        }

        return view('chatbot::dashboard.workflows.index', [
            'chatbot' => $chatbot,
            'rows' => $rows,
        ]);
    }

    public function update(Request $request, Chatbot $chatbot): RedirectResponse
    {
        $items = $request->input('enabled', []);
        $all = $this->registry->all();

        // Normalize to a set of enabled keys
        $enabledKeys = [];
        if (is_array($items)) {
            foreach ($items as $key => $val) {
                if ($val === '1' || $val === 1 || $val === true || $val === 'on') {
                    $enabledKeys[] = (string)$key;
                }
            }
        }

        foreach ($all as $key => $wf) {
            $enabled = in_array($key, $enabledKeys, true);
            ChatbotWorkflowSetting::query()->updateOrCreate(
                ['chatbot_id' => $chatbot->getKey(), 'workflow_key' => $key],
                ['enabled' => $enabled]
            );
        }

        return back()->with('success', 'Workflow settings updated.');
    }
}
