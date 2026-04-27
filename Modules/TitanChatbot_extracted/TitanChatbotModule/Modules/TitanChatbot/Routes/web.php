<?php
use Illuminate\Support\Facades\Route;
use Modules\TitanChatbot\Http\Controllers\TitanChatbotController;

Route::middleware(['web', 'auth'])->prefix(config('titan-chatbot.route_prefix', 'titan-chatbot'))->name('titan-chatbot.')->group(function () {
    Route::get('/', [TitanChatbotController::class, 'index'])->name('index');
    Route::get('/channels', [TitanChatbotController::class, 'channels'])->name('channels');
    Route::get('/voice', [TitanChatbotController::class, 'voice'])->name('voice');
    Route::get('/agent', [TitanChatbotController::class, 'agent'])->name('agent');
});
