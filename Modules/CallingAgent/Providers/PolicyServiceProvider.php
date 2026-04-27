<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\CallingAgent\Models\CallingAgent;
use Modules\CallingAgent\Policies\CallingAgentPolicy;

class PolicyServiceProvider extends ServiceProvider
{
    protected $policies = [
        CallingAgent::class => CallingAgentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('manage-calling-agent', CallingAgentPolicy::class.'@update');
    }
}
