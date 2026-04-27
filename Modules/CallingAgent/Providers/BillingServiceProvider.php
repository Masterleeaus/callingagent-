<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CallingAgent\Billing\Meters\VoiceSecondsMeter;
use Modules\CallingAgent\Billing\Meters\RealtimeCreditGuard;
use Modules\CallingAgent\Billing\Meters\MinuteRoundingPolicy;
use Modules\CallingAgent\Billing\Usage\CallUsageRecorder;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/billing.php', 'calling-agent.billing');
        $this->app->singleton(VoiceSecondsMeter::class);
        $this->app->singleton(RealtimeCreditGuard::class);
        $this->app->singleton(MinuteRoundingPolicy::class);
        $this->app->singleton(CallUsageRecorder::class);
    }

    public function boot(): void {}
}
