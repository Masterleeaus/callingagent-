<?php

namespace Modules\CallingAgent\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CallingAgent\Services\TwilioChannelService;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\AI\Agents\ReceptionistAgent;

use Modules\CallingAgent\Services\Realtime\RealtimeSessionTokenService;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php', 'calling-agent.config');
        $this->mergeConfigFrom(__DIR__.'/../Config/routes.php', 'calling-agent.routes');
        $this->mergeConfigFrom(__DIR__.'/../Config/features.php', 'calling-agent.features');
        $this->mergeConfigFrom(__DIR__.'/../Config/ai.php', 'calling-agent.ai');
        $this->mergeConfigFrom(__DIR__.'/../Config/providers.php', 'calling-agent.providers');
        $this->mergeConfigFrom(__DIR__.'/../Config/permissions.php', 'calling-agent.permissions');
        $this->mergeConfigFrom(__DIR__.'/../Config/routing.php', 'calling-agent.routing');
        $this->mergeConfigFrom(__DIR__.'/../Config/personas.php', 'calling-agent.personas');
        $this->mergeConfigFrom(__DIR__.'/../Config/ui.php', 'calling-agent.ui');

        $this->app->register(EventServiceProvider::class);
        $this->app->register(BillingServiceProvider::class);
        $this->app->register(PolicyServiceProvider::class);
        $this->app->register(RepositoryServiceProvider::class);
        $this->app->register(WorkflowServiceProvider::class);
        $this->app->register(AutomationServiceProvider::class);
        $this->app->register(TenancyServiceProvider::class);
        $this->app->register(SearchServiceProvider::class);
        $this->app->register(FilamentServiceProvider::class);
        $this->app->register(ModuleBootServiceProvider::class);

        $this->app->singleton(TwilioChannelService::class);
        $this->app->singleton(ReceptionistAgent::class);
        $this->app->singleton(ReceptionistOrchestrator::class);
        $this->app->singleton(RealtimeSessionTokenService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/tenant.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/internal.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'calling-agent');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'calling-agent');
    }
}
