<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HealthCheckService;
use App\Models\User;
use App\Notifications\ApiHealthAlert;
use Illuminate\Support\Facades\Notification;

class SendHealthAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-health-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check API health and notify admins of issues';

    /**
     * Execute the console command.
     */
    public function handle(HealthCheckService $healthService)
    {
        $services = [
            'twilio' => $healthService->checkTwilio(),
            'openai' => $healthService->checkOpenAI(),
            'elevenlabs' => $healthService->checkElevenLabs(),
        ];

        $admins = User::where('is_admin', true)->get();

        foreach ($services as $service => $result) {
            if ($result['status'] === 'unhealthy' || $result['status'] === 'degraded') {
                $this->warn("Alert: {$service} is {$result['status']}");
                
                if ($admins->count() > 0) {
                    Notification::send($admins, new ApiHealthAlert($service, $result['status'], $result['message']));
                }
            }
        }

        $this->info('Health alerts check complete.');
    }
}
