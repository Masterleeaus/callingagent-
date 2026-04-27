<?php
namespace Modules\CallingAgent\Http\Controllers;

use Illuminate\Http\Request;
use Modules\CallingAgent\Services\Booking\AvailabilityResolver;

final class ReceptionistBookingController
{
    public function slots(Request $request): array
    {
        return ['slots' => (new AvailabilityResolver())->slots($request->input('calendar', []), $request->input('date', date('Y-m-d')), (int)$request->input('duration', 30))];
    }
}
