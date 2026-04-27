<?php
namespace Modules\CallingAgent\Providers;
use Illuminate\Support\ServiceProvider;
use Modules\CallingAgent\Services\TwilioChannelService;
use Modules\CallingAgent\Services\ReceptionistOrchestrator;
use Modules\CallingAgent\AI\Agents\ReceptionistAgent;
class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/config.php','calling-agent.config');
        $this->mergeConfigFrom(__DIR__.'/../Config/routes.php','calling-agent.routes');
        $this->app->singleton(TwilioChannelService::class);
        $this->app->singleton(ReceptionistAgent::class);
        $this->app->singleton(ReceptionistOrchestrator::class);
    }
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/tenant.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views','calling-agent');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang','calling-agent');
    }
}
