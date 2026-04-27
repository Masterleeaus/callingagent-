<?php
namespace Modules\CallingAgent\Http\Controllers;

use Illuminate\Http\Request;
use Modules\CallingAgent\Services\Realtime\TwilioMediaStreamRelay;

final class RealtimeStreamController
{
    public function twilio(Request $request): array
    {
        return (new TwilioMediaStreamRelay())->normalize($request->all());
    }
}
