<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Workflow;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotToolSetting;
use App\Extensions\Chatbot\System\Models\ChatbotWorkflowSetting;
use Illuminate\Support\Arr;

class WorkflowRegistry
{
    /** @return array<string, mixed> */
    public function all(): array
    {
        return config('chatbot.workflows.workflows', []);
    }

    /** @return array<string, mixed>|null */
    public function get(string $workflowKey): ?array
    {
        $all = $this->all();
        return $all[$workflowKey] ?? null;
    }

    public function exists(string $workflowKey): bool
    {
        return $this->get($workflowKey) !== null;
    }

    /**
     * Enabled workflows for a chatbot.
     * If no explicit settings exist, falls back to each workflow's default_enabled.
     *
     * @return array<int, array<string, mixed>>
     */
    public function enabledFor(Chatbot $chatbot): array
    {
        $all = $this->all();

        $settings = ChatbotWorkflowSetting::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->get()
            ->keyBy('workflow_key');

        $enabled = [];
        foreach ($all as $key => $wf) {
            $isEnabled = Arr::get($wf, 'default_enabled', true);
            if ($settings->has($key)) {
                $isEnabled = (bool) $settings->get($key)->enabled;
            }
            if ($isEnabled) {
                $wf['workflow_key'] = $key;
                $enabled[] = $wf;
            }
        }

        return $enabled;
    }

    /** @return array<int, string> */
    public function toolsAllowlist(): array
    {
        return config('chatbot.workflows.tools', []);
    }

    /** @return array<string, mixed> */
    public function toolsAll(): array
    {
        return config('chatbot.tools.tools', []);
    }

    /** @return array<string, mixed>|null */
    public function tool(string $toolKey): ?array
    {
        $all = $this->toolsAll();
        return $all[$toolKey] ?? null;
    }

    /**
     * Enabled tools for a chatbot.
     * If no explicit settings exist, tools default to enabled.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toolsEnabledFor(Chatbot $chatbot): array
    {
        $all = $this->toolsAll();

        $settings = ChatbotToolSetting::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->get()
            ->keyBy('tool_key');

        $enabled = [];
        foreach ($all as $key => $tool) {
            $isEnabled = true;
            if ($settings->has($key)) {
                $isEnabled = (bool) $settings->get($key)->enabled;
            }
            if ($isEnabled) {
                $tool['tool_key'] = $key;
                $enabled[] = $tool;
            }
        }
        return $enabled;
    }

    public function toolIsEnabledFor(Chatbot $chatbot, string $toolKey): bool
    {
        $setting = ChatbotToolSetting::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->where('tool_key', $toolKey)
            ->first();

        if ($setting) {
            return (bool) $setting->enabled;
        }

        // default enabled
        return $this->tool($toolKey) !== null;
    }
}
