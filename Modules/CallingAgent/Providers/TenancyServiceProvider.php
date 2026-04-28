<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CallingAgent\Scopes\TenantScope;
use Modules\CallingAgent\Models\CallingAgent;
use Modules\CallingAgent\Models\CallingAgentCall;
use Modules\CallingAgent\Models\CallingAgentPhoneNumber;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/tenancy.php', 'calling-agent.tenancy');
    }

    public function boot(): void
    {
        if (config('calling-agent.tenancy.enabled', false)) {
            CallingAgent::addGlobalScope(new TenantScope);
            CallingAgentCall::addGlobalScope(new TenantScope);
            CallingAgentPhoneNumber::addGlobalScope(new TenantScope);
        }
    }
}
