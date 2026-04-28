<?php

namespace Modules\TitanChatbot\Console\Commands;

use Illuminate\Console\Command;

class AuditTitanChatbotCommand extends Command
{
    protected $signature   = 'titan-chatbot:audit';
    protected $description = 'Audit TitanChatbot module: check providers, routes, models, and config';

    public function handle(): int
    {
        $this->info('TitanChatbot Module Audit');
        $this->line(str_repeat('-', 40));

        $checks = [
            'AI Provider Config' => fn() => $this->checkAiConfig(),
            'GeneratorBridge'    => fn() => class_exists(\Modules\TitanChatbot\Services\GeneratorBridge::class),
            'ConversationRouter' => fn() => class_exists(\Modules\TitanChatbot\Services\ConversationRouter::class),
            'ChannelRouter'      => fn() => class_exists(\Modules\TitanChatbot\Services\ChannelRouter::class),
            'TitanAgent base'    => fn() => class_exists(\Modules\TitanChatbot\AI\Core\TitanAgent::class),
            'ToolRegistry'       => fn() => class_exists(\Modules\TitanChatbot\AI\Tools\ToolRegistry::class),
            'SchemaGenerator'    => fn() => class_exists(\Modules\TitanChatbot\AI\Tools\SchemaGenerator::class),
            'StorageManager'     => fn() => class_exists(\Modules\TitanChatbot\AI\Memory\StorageManager::class),
            'VoiceSecondsMeter'  => fn() => class_exists(\Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter::class),
            'ConversationMeter'  => fn() => class_exists(\Modules\TitanChatbot\Billing\Meters\ConversationMeter::class),
            'UsageTracker'       => fn() => class_exists(\Modules\TitanChatbot\Billing\Usage\UsageTracker::class),
            'WebchatChannel'     => fn() => class_exists(\Modules\TitanChatbot\Services\WebchatChannel::class),
            'TrainingPipeline'   => fn() => class_exists(\Modules\TitanChatbot\Services\TrainingPipeline::class),
            'Knowledge Pack'     => fn() => is_dir(__DIR__ . '/../../Knowledge'),
            'Knowledge README'   => fn() => file_exists(__DIR__ . '/../../Knowledge/README.md'),
            'BookingAgent Manifest' => fn() => $this->checkValidJson(__DIR__ . '/../../Agents/BookingAgent/agent.manifest.json'),
            'BookingAgent Prompts'  => fn() => $this->checkBookingAgentPrompts(),
            'Tool Schemas Valid'    => fn() => $this->checkToolSchemas(),
            'Retrieval Policy'      => fn() => $this->checkValidJson(__DIR__ . '/../../AI/Retrieval/retrieval.policy.json'),
            'Indexing Manifest'     => fn() => $this->checkValidJson(__DIR__ . '/../../AI/Indexing/indexing.manifest.json'),
            'Guardrails Config'     => fn() => $this->checkValidJson(__DIR__ . '/../../AI/Guardrails/guardrails.json'),
            'Telemetry Manifest'    => fn() => $this->checkValidJson(__DIR__ . '/../../AI/Telemetry/telemetry.manifest.json'),
            'Action Map'            => fn() => $this->checkValidJson(__DIR__ . '/../../AI/Actions/action-map.json'),
            'Dataset Manifest'      => fn() => $this->checkValidJson(__DIR__ . '/../../Agents/BookingAgent/training/dataset-manifest.json'),
            'Eval Suite'            => fn() => $this->checkValidJson(__DIR__ . '/../../Agents/BookingAgent/evaluations/eval-suite.json'),
        ];

        $pass = 0;
        $fail = 0;

        foreach ($checks as $name => $check) {
            try {
                $result = $check();
                if ($result) {
                    $this->line("  <fg=green>✓</> {$name}");
                    $pass++;
                } else {
                    $this->line("  <fg=red>✗</> {$name}");
                    $fail++;
                }
            } catch (\Throwable $e) {
                $this->line("  <fg=yellow>!</> {$name}: " . $e->getMessage());
                $fail++;
            }
        }

        $this->line('');
        $this->line("Passed: {$pass} / Failed: {$fail}");

        return $fail === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkAiConfig(): bool
    {
        return !empty(config('titan-chatbot.ai.provider', config('titan-chatbot.ai.provider', 'openai')));
    }

    private function checkValidJson(string $path): bool
    {
        if (!file_exists($path)) {
            return false;
        }
        $content = file_get_contents($path);
        json_decode($content, true);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function checkBookingAgentPrompts(): bool
    {
        $base    = __DIR__ . '/../../Agents/BookingAgent/prompts/';
        $prompts = ['system.md', 'answering.md', 'tool-use.md'];
        foreach ($prompts as $prompt) {
            if (!file_exists($base . $prompt) || filesize($base . $prompt) < 10) {
                return false;
            }
        }
        return true;
    }

    private function checkToolSchemas(): bool
    {
        $toolDir = __DIR__ . '/../../Agents/BookingAgent/tools/';
        if (!is_dir($toolDir)) {
            return false;
        }
        $files = glob($toolDir . '*.tool.json');
        if (empty($files)) {
            return false;
        }
        foreach ($files as $file) {
            $content = file_get_contents($file);
            json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        }
        return true;
    }
}
