<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CallingAgent\Interfaces\CallingAgentRepositoryInterface;
use Modules\CallingAgent\Repositories\CallingAgentRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CallingAgentRepositoryInterface::class, CallingAgentRepository::class);
    }

    public function boot(): void {}
}
