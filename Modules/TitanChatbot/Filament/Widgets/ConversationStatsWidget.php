<?php

namespace Modules\TitanChatbot\Filament\Widgets;

use Modules\TitanChatbot\Models\Conversation;

if (class_exists(\Filament\Widgets\StatsOverviewWidget::class)) {
    class ConversationStatsWidget extends \Filament\Widgets\StatsOverviewWidget
    {
        protected function getStats(): array
        {
            $today = now()->toDateString();

            $conversationsToday = class_exists(Conversation::class)
                ? Conversation::whereDate('started_at', $today)->count()
                : 0;

            $activeConversations = class_exists(Conversation::class)
                ? Conversation::whereNull('ended_at')->count()
                : 0;

            $messagesTotal = class_exists(Conversation::class)
                ? Conversation::whereDate('started_at', $today)->withCount('messages')->get()->sum('messages_count')
                : 0;

            return [
                \Filament\Widgets\StatsOverviewWidget\Stat::make('Conversations Today', $conversationsToday)
                    ->description('Total conversations started today')
                    ->icon('heroicon-o-chat-bubble-left-right'),

                \Filament\Widgets\StatsOverviewWidget\Stat::make('Active Conversations', $activeConversations)
                    ->description('Currently open sessions')
                    ->icon('heroicon-o-signal')
                    ->color('success'),

                \Filament\Widgets\StatsOverviewWidget\Stat::make('Messages Today', $messagesTotal)
                    ->description('Total messages exchanged today')
                    ->icon('heroicon-o-chat-bubble-oval-left'),
            ];
        }
    }
} else {
    class ConversationStatsWidget
    {
        public function getStats(): array
        {
            return [];
        }
    }
}
