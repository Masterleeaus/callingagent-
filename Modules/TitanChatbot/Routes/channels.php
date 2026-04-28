<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanChatbot\Http\Controllers\Webhooks\WhatsappWebhookController;
use Modules\TitanChatbot\Http\Controllers\Webhooks\TelegramWebhookController;
use Modules\TitanChatbot\Http\Controllers\Webhooks\MessengerWebhookController;

/*
|--------------------------------------------------------------------------
| TitanChatbot Channel Webhook Routes
|--------------------------------------------------------------------------
| These routes are publicly accessible — no auth middleware — because
| external services (Twilio, Telegram, Meta) POST/GET here directly.
|
*/

Route::prefix('webhooks/titan-chatbot')->name('titan-chatbot.webhooks.')->group(function (): void {

    // ── WhatsApp / Twilio ──────────────────────────────────────────────────
    Route::post('/whatsapp/{channelId}', [WhatsappWebhookController::class, 'handle'])
        ->name('whatsapp');

    // ── Telegram ───────────────────────────────────────────────────────────
    Route::post('/telegram/{channelId}', [TelegramWebhookController::class, 'handle'])
        ->name('telegram');

    Route::get('/telegram/{channelId}/verify', [TelegramWebhookController::class, 'verify'])
        ->name('telegram.verify');

    // ── Facebook Messenger ─────────────────────────────────────────────────
    // Facebook sends GET for hub verification, POST for message events
    Route::get('/messenger/{channelId}', [MessengerWebhookController::class, 'verify'])
        ->name('messenger.verify');

    Route::post('/messenger/{channelId}', [MessengerWebhookController::class, 'handle'])
        ->name('messenger');
});

