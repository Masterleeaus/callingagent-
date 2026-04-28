<?php

namespace Modules\CallingAgent\Filament\Pages;

class CallingAgentBuilderPage
{
    public static string $view = 'calling-agent::builder.index';

    public static function navigationLabel(): string { return 'Agent Builder'; }
    public static function route(): string { return '/calling-agent/builder'; }

    public function schema(): array
    {
        return [
            'persona' => ['front_desk', 'clinic', 'legal', 'hotel', 'saas'],
            'channels' => ['voice', 'sms', 'whatsapp', 'messenger', 'telegram', 'web_widget'],
            'routing' => ['front_desk', 'booking_team', 'sales_team', 'support_team', 'urgent_handoff'],
            'calendar' => ['google', 'outlook', 'caldav'],
            'recovery' => ['missed_call_sms', 'callback_scheduler', 'voicemail_summary'],
        ];
    }
}
