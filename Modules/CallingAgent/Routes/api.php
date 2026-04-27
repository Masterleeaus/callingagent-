<?php
use Illuminate\Support\Facades\Route;
use Modules\CallingAgent\Http\Controllers\CallingAgentApiController;

Route::middleware(['api'])->prefix(config('calling-agent.routes.api_prefix','api/calling-agent'))->name('api.calling-agent.')->group(function () {
    Route::get('/calls/{call}', [CallingAgentApiController::class, 'showCall'])->name('calls.show');
    Route::post('/sms', [CallingAgentApiController::class, 'sendSms'])->name('sms.send');
    Route::post('/whatsapp', [CallingAgentApiController::class, 'sendWhatsapp'])->name('whatsapp.send');
});


// CallingAgent next-extraction realtime/reception endpoints
Route::post('calling-agent/realtime/twilio', [\Modules\CallingAgent\Http\Controllers\RealtimeStreamController::class, 'twilio']);
Route::post('calling-agent/receptionist/slots', [\Modules\CallingAgent\Http\Controllers\ReceptionistBookingController::class, 'slots']);
Route::post('/calling-agent/builder/preview', [\Modules\CallingAgent\Http\Controllers\CallingAgentBuilderController::class, 'preview'])->name('calling-agent.builder.preview');
