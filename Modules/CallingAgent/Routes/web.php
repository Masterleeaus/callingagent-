<?php
use Illuminate\Support\Facades\Route;
use Modules\CallingAgent\Http\Controllers\CallingAgentDashboardController;

Route::middleware(['web','auth'])->prefix(config('calling-agent.routes.prefix','calling-agent'))->name('calling-agent.')->group(function () {
    Route::get('/', [CallingAgentDashboardController::class, 'index'])->name('dashboard');
});
Route::get('/calling-agent/builder', [\Modules\CallingAgent\Http\Controllers\CallingAgentBuilderController::class, 'index'])->name('calling-agent.builder');
