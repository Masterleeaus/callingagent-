<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CallingAgent\Workflows\Definitions\ReceptionistCallWorkflow;
use Modules\CallingAgent\Workflows\Definitions\ReceptionistWorkflow;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (class_exists(\Workflow\WorkflowRegistry::class)) {
            \Workflow\WorkflowRegistry::register(ReceptionistCallWorkflow::class);
            \Workflow\WorkflowRegistry::register(ReceptionistWorkflow::class);
        }
    }
}
