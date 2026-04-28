<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanChatbot\Http\Controllers\Api\ConversationController;
use Modules\TitanChatbot\Http\Controllers\TitanChatbotController;

Route::middleware(['api'])->prefix(config('titan-chatbot.api_prefix', 'api/titan-chatbot'))->name('api.titan-chatbot.')->group(function () {
    Route::get('/health', [TitanChatbotController::class, 'health'])->name('health');
});

Route::middleware(['api'])->prefix('api/chatbots')->name('api.chatbots.')->group(function () {
    Route::post('/{id}/message', [ConversationController::class, 'sendMessage'])->name('message');
    Route::post('/{id}/voice',   [ConversationController::class, 'sendVoice'])->name('voice');
    Route::get('/{id}/history',  [ConversationController::class, 'history'])->name('history');
    Route::post('/{id}/train',   [ConversationController::class, 'train'])->name('train');
});
