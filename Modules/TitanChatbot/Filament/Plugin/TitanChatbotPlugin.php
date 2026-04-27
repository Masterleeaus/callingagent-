<?php

namespace Modules\TitanChatbot\Filament\Plugin;

use Modules\TitanChatbot\Filament\Resources\ChatbotResource;
use Modules\TitanChatbot\Filament\Resources\ChannelResource;
use Modules\TitanChatbot\Filament\Resources\ConversationResource;
use Modules\TitanChatbot\Filament\Resources\KnowledgeResource;
use Modules\TitanChatbot\Filament\Widgets\ConversationStatsWidget;
use Modules\TitanChatbot\Filament\Widgets\UsageWidget;

if (interface_exists(\Filament\Contracts\Plugin::class)) {
    class TitanChatbotPlugin implements \Filament\Contracts\Plugin
    {
        public static function make(): static
        {
            return app(static::class);
        }

        public function getId(): string
        {
            return 'titan-chatbot';
        }

        public function register(\Filament\Panel $panel): void
        {
            $panel
                ->resources([
                    ChatbotResource::class,
                    ConversationResource::class,
                    KnowledgeResource::class,
                    ChannelResource::class,
                ])
                ->widgets([
                    ConversationStatsWidget::class,
                    UsageWidget::class,
                ]);
        }

        public function boot(\Filament\Panel $panel): void {}
    }
} else {
    class TitanChatbotPlugin
    {
        public static function make(): static
        {
            return new static();
        }

        public function getId(): string
        {
            return 'titan-chatbot';
        }
    }
}
