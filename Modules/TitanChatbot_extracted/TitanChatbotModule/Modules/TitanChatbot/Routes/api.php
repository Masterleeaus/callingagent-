<?php
use Illuminate\Support\Facades\Route;
use Modules\TitanChatbot\Http\Controllers\TitanChatbotController;

Route::middleware(['api'])->prefix(config('titan-chatbot.api_prefix', 'api/titan-chatbot'))->name('api.titan-chatbot.')->group(function () {
    Route::get('/health', [TitanChatbotController::class, 'health'])->name('health');
});
