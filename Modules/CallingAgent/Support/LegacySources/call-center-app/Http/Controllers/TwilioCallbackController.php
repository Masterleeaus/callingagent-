<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CallLog;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;

class TwilioCallbackController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function handleStatus(Request $request)
    {
        $callSid = $request->input('CallSid');
        $status = $request->input('CallStatus');
        $duration = (int) $request->input('CallDuration', 0);

        if ($status !== 'completed') {
            return response('OK');
        }

        $callLog = CallLog::where('sid', $callSid)->first();

        if (!$callLog) {
            Log::warning("Twilio Callback: Call log not found for SID {$callSid}");
            return response('OK');
        }

        if ($callLog->is_billed) {
            return response('OK');
        }

        // Round up to nearest minute
        $minutes = (int) ceil($duration / 60);
        if ($minutes === 0 && $duration > 0) {
            $minutes = 1;
        }

        $tenant = $callLog->tenant;

        if ($tenant) {
            $this->billingService->deductMinutes($tenant, $minutes);
            
            $callLog->update([
                'is_billed' => true,
                'duration_minutes' => $minutes,
                'duration' => $duration
            ]);

            Log::info("Billed tenant {$tenant->name} for {$minutes} minutes. Call SID: {$callSid}");
        }

        return response('OK');
    }
}
