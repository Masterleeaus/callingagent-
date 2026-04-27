<?php
use Illuminate\Support\Facades\Route;
use Modules\CallingAgent\Http\Controllers\Webhooks\TwilioVoiceWebhookController;
use Modules\CallingAgent\Http\Controllers\Webhooks\TwilioMessagingWebhookController;
Route::post('/calling-agent/webhooks/twilio/voice/incoming', [TwilioVoiceWebhookController::class,'incoming'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/calling-agent/webhooks/twilio/voice/gather', [TwilioVoiceWebhookController::class,'gather'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/calling-agent/webhooks/twilio/voice/status', [TwilioVoiceWebhookController::class,'status'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/calling-agent/webhooks/twilio/message/incoming', [TwilioMessagingWebhookController::class,'incoming'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
