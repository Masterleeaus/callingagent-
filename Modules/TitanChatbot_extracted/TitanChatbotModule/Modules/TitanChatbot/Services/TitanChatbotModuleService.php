<?php
namespace Modules\TitanChatbot\Services;

class TitanChatbotModuleService
{
    public function summary(): array
    {
        return [
            'module' => 'TitanChatbot',
            'capabilities' => [
                'external_builder', 'rag_training', 'multi_llm', 'website_widget',
                'whatsapp', 'telegram', 'messenger', 'voice', 'elevenlabs',
                'live_agent', 'analytics', 'pwa_ready'
            ],
            'migration_strategy' => 'reuse existing source archives, then promote components into module-native namespaces',
        ];
    }

    public function channels(): array
    {
        return ['website', 'whatsapp', 'telegram', 'messenger'];
    }

    public function voice(): array
    {
        return ['enabled' => true, 'providers' => ['core_voice', 'elevenlabs']];
    }

    public function agent(): array
    {
        return ['enabled' => true, 'handoff' => 'human-agent-escalation'];
    }
}
