<?php

use Illuminate\Support\Facades\Route;
use Modules\CallingAgent\Http\Controllers\CallingAgentBuilderController;
use Modules\CallingAgent\Http\Controllers\CallingAgentDashboardController;

Route::middleware(['web', 'auth'])
    ->prefix(config('calling-agent.routes.prefix', 'calling-agent'))
    ->name('calling-agent.')
    ->group(function () {
        Route::get('/', [CallingAgentDashboardController::class, 'index'])->name('dashboard');

        // Builder UI
        Route::get('/builder', [CallingAgentBuilderController::class, 'index'])->name('builder');
        Route::get('/builder/{agent}/load', [CallingAgentBuilderController::class, 'load'])->name('builder.load');
        Route::post('/builder/{agent}/save', [CallingAgentBuilderController::class, 'save'])->name('builder.save');
        Route::post('/builder/{agent}/assign-number', [CallingAgentBuilderController::class, 'assignNumber'])->name('builder.assign-number');
        Route::post('/builder/preview', [CallingAgentBuilderController::class, 'preview'])->name('builder.preview');
    });
