<?php

use Illuminate\Support\Facades\Route;
use Modules\CallingAgent\Http\Controllers\CallingAgentApiController;

Route::middleware(['api'])
    ->prefix(config('calling-agent.routes.api_prefix', 'api/calling-agent'))
    ->name('api.calling-agent.')
    ->group(function () {
        // Call log
        Route::get('/calls/{call}', [CallingAgentApiController::class, 'showCall'])->name('calls.show');

        // Outbound call
        Route::post('/calls/outbound', [CallingAgentApiController::class, 'placeCall'])->name('calls.outbound');

        // Transfer live call
        Route::post('/calls/{callSid}/transfer', [CallingAgentApiController::class, 'transfer'])->name('calls.transfer');

        // Hang up live call
        Route::post('/calls/{callSid}/hangup', [CallingAgentApiController::class, 'hangup'])->name('calls.hangup');

        // Messaging
        Route::post('/sms', [CallingAgentApiController::class, 'sendSms'])->name('sms.send');
        Route::post('/whatsapp', [CallingAgentApiController::class, 'sendWhatsapp'])->name('whatsapp.send');
    });

// Realtime / media streams (no auth prefix — authenticated separately)
Route::post('calling-agent/realtime/twilio', [\Modules\CallingAgent\Http\Controllers\RealtimeStreamController::class, 'twilio'])
    ->name('calling-agent.realtime.twilio');

Route::post('calling-agent/realtime/token', [\Modules\CallingAgent\Http\Controllers\RealtimeStreamController::class, 'token'])
    ->name('calling-agent.realtime.token');

Route::post('calling-agent/realtime/validate-token', [\Modules\CallingAgent\Http\Controllers\RealtimeStreamController::class, 'validateToken'])
    ->name('calling-agent.realtime.validate-token');

Route::post('calling-agent/receptionist/slots', [\Modules\CallingAgent\Http\Controllers\ReceptionistBookingController::class, 'slots'])
    ->name('calling-agent.receptionist.slots');
