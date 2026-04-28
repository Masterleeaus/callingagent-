<?php

use Illuminate\Support\Facades\Route;
use Modules\CallingAgent\Http\Controllers\Webhooks\TwilioVoiceWebhookController;
use Modules\CallingAgent\Http\Controllers\Webhooks\TwilioMessagingWebhookController;

// Twilio Webhooks - CSRF exempt, Twilio signature validated
Route::middleware([\Modules\CallingAgent\Http\Middleware\ValidateTwilioSignature::class])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::post('/calling-agent/webhooks/twilio/voice/incoming',
            [TwilioVoiceWebhookController::class, 'incoming'])
            ->name('calling-agent.webhooks.voice.incoming');

        Route::post('/calling-agent/webhooks/twilio/voice/gather',
            [TwilioVoiceWebhookController::class, 'gather'])
            ->name('calling-agent.webhooks.voice.gather');

        Route::post('/calling-agent/webhooks/twilio/voice/status',
            [TwilioVoiceWebhookController::class, 'status'])
            ->name('calling-agent.webhooks.voice.status');

        Route::post('/calling-agent/webhooks/twilio/voice/recording',
            [TwilioVoiceWebhookController::class, 'recording'])
            ->name('calling-agent.webhooks.voice.recording');

        Route::post('/calling-agent/webhooks/twilio/voice/voicemail',
            [TwilioVoiceWebhookController::class, 'voicemail'])
            ->name('calling-agent.webhooks.voice.voicemail');

        Route::post('/calling-agent/webhooks/twilio/message/incoming',
            [TwilioMessagingWebhookController::class, 'incoming'])
            ->name('calling-agent.webhooks.message.incoming');
    });

