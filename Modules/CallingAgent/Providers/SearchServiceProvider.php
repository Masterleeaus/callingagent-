<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/search.php', 'calling-agent.search');
    }

    public function boot(): void {}
}
