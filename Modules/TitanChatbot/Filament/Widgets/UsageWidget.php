<?php

namespace Modules\TitanChatbot\Filament\Widgets;

use Modules\TitanChatbot\Billing\Meters\ConversationMeter;
use Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter;

if (class_exists(\Filament\Widgets\StatsOverviewWidget::class)) {
    class UsageWidget extends \Filament\Widgets\StatsOverviewWidget
    {
        protected function getStats(): array
        {
            $tenantId = (int) (auth()->user()?->tenant_id ?? 0);
            $today    = now()->toDateString();

            $conversationCount = app(ConversationMeter::class)->getCount($tenantId, $today);
            $voiceSeconds      = app(VoiceSecondsMeter::class)->getSeconds($tenantId, $today);

            return [
                \Filament\Widgets\StatsOverviewWidget\Stat::make('Conversation Usage (Today)', $conversationCount)
                    ->description('Billable conversations today')
                    ->icon('heroicon-o-currency-dollar'),

                \Filament\Widgets\StatsOverviewWidget\Stat::make('Voice Seconds (Today)', $voiceSeconds . 's')
                    ->description('Billable voice seconds today')
                    ->icon('heroicon-o-microphone'),
            ];
        }
    }
} else {
    class UsageWidget
    {
        public function getStats(): array
        {
            return [];
        }
    }
}
