<?php

namespace Modules\TitanChatbot\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\TitanChatbot\Automation\Handlers\EscalateToAgentHandler;
use Modules\TitanChatbot\Automation\Handlers\SendSuggestedPromptsHandler;
use Modules\TitanChatbot\Automation\Handlers\SendWelcomeBannerHandler;
use Modules\TitanChatbot\Events\ConversationEscalated;
use Modules\TitanChatbot\Events\ConversationStarted;
use Modules\TitanChatbot\Events\IntentDetected;
use Modules\TitanChatbot\Events\MessageReceived;
use Modules\TitanChatbot\Events\VoiceSessionDurationRecorded;
use Modules\TitanChatbot\Events\VoiceSessionStarted;
use Modules\TitanChatbot\Listeners\RecordVoiceSessionBillingListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ConversationStarted::class => [
            SendWelcomeBannerHandler::class,
            SendSuggestedPromptsHandler::class,
        ],
        ConversationEscalated::class => [
            EscalateToAgentHandler::class,
        ],
        IntentDetected::class => [],
        VoiceSessionStarted::class => [],
        VoiceSessionDurationRecorded::class => [
            RecordVoiceSessionBillingListener::class,
        ],
        MessageReceived::class => [],
    ];
}
