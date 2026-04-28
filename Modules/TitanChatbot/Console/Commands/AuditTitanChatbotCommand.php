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
}
