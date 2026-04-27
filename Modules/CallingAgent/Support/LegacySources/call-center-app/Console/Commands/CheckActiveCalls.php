<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActiveCall;
use App\Services\BillingService;
use App\Services\TwilioService;

class CheckActiveCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-active-calls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor active calls and terminate if tenant credits are exhausted';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService, TwilioService $twilio)
    {
        $activeCalls = ActiveCall::with('tenant')->get();

        foreach ($activeCalls as $call) {
            if (!$billingService->hasCredits($call->tenant)) {
                $this->info("Terminating call {$call->sid} for tenant {$call->tenant->name} - No credits.");
                
                try {
                    $twilio->hangupCall($call->sid);
                    $call->delete();
                } catch (\Exception $e) {
                    $this->error("Failed to hangup call {$call->sid}: " . $e->getMessage());
                }
            }
        }
    }
}
