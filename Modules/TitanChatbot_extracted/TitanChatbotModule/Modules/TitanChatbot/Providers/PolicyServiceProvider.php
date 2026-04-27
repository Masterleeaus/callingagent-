<?php
namespace Modules\TitanChatbot\Providers;

use Illuminate\Support\ServiceProvider;

class PolicyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (is_file(__DIR__ . '/../Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/../Config/config.php', 'titan-chatbot');
        }
    }

    public function boot(): void
    {
        if (is_dir(__DIR__ . '/../Database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
        }

        if (is_dir(__DIR__ . '/../Resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'titan-chatbot');
        }
    }
}
