<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\CallingAgent\Events\CallStatusChanged;
use Modules\CallingAgent\Listeners\RecordCallUsage;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CallStatusChanged::class => [
            RecordCallUsage::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
