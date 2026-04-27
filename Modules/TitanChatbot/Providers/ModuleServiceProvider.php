<?php

namespace Modules\TitanChatbot\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\TitanChatbot\AI\Agents\BookingAgent;
use Modules\TitanChatbot\AI\Agents\ConversationAgent;
use Modules\TitanChatbot\AI\Agents\SupportAgent;
use Modules\TitanChatbot\AI\Agents\VoiceAgent;
use Modules\TitanChatbot\AI\Memory\ConversationMemoryStore;
use Modules\TitanChatbot\Billing\Meters\ConversationMeter;
use Modules\TitanChatbot\Billing\Meters\EmbeddingMeter;
use Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter;
use Modules\TitanChatbot\Services\ChannelRouter;
use Modules\TitanChatbot\Services\ChatbotAnalyticsService;
use Modules\TitanChatbot\Services\ConversationRouter;
use Modules\TitanChatbot\Services\ConversationSessionManager;
use Modules\TitanChatbot\Services\ConversationStateStore;
use Modules\TitanChatbot\Services\GeneratorBridge;
use Modules\TitanChatbot\Services\MessengerChannel;
use Modules\TitanChatbot\Services\TelegramChannel;
use Modules\TitanChatbot\Services\VoiceChannel;
use Modules\TitanChatbot\Services\WebchatChannel;
use Modules\TitanChatbot\Services\WhatsappChannel;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigsFromDir([
            'config'       => 'titan-chatbot',
            'ai'           => 'titan-chatbot.ai',
            'billing'      => 'titan-chatbot.billing',
            'channels'     => 'titan-chatbot.channels',
            'connectors'   => 'titan-chatbot.connectors',
            'features'     => 'titan-chatbot.features',
            'navigation'   => 'titan-chatbot.navigation',
            'observability'=> 'titan-chatbot.observability',
            'routes'       => 'titan-chatbot.routes',
            'security'     => 'titan-chatbot.security',
            'tenancy'      => 'titan-chatbot.tenancy',
        ]);

        // Core services
        $this->app->singleton(ConversationRouter::class);
        $this->app->singleton(ConversationSessionManager::class);
        $this->app->singleton(ConversationStateStore::class);
        $this->app->singleton(GeneratorBridge::class);
        $this->app->singleton(ChannelRouter::class);
        $this->app->singleton(ConversationMemoryStore::class);
        $this->app->singleton(ChatbotAnalyticsService::class);

        // AI agents
        $this->app->singleton(ConversationAgent::class);
        $this->app->singleton(BookingAgent::class);
        $this->app->singleton(SupportAgent::class);
        $this->app->singleton(VoiceAgent::class);

        // Billing meters
        $this->app->singleton(ConversationMeter::class);
        $this->app->singleton(VoiceSecondsMeter::class);
        $this->app->singleton(EmbeddingMeter::class);

        // Channel driver bindings
        $this->app->bind('titan.channel.webchat',   WebchatChannel::class);
        $this->app->bind('titan.channel.whatsapp',  WhatsappChannel::class);
        $this->app->bind('titan.channel.telegram',  TelegramChannel::class);
        $this->app->bind('titan.channel.messenger', MessengerChannel::class);
        $this->app->bind('titan.channel.voice',     VoiceChannel::class);
    }

    public function boot(): void
    {
        foreach (['api', 'web', 'admin', 'tenant', 'channels'] as $routeFile) {
            $path = __DIR__ . "/../Routes/{$routeFile}.php";
            if (is_file($path)) {
                $this->loadRoutesFrom($path);
            }
        }

        if (is_dir(__DIR__ . '/../Database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/migrations');
        }

        if (is_dir(__DIR__ . '/../Resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'titan-chatbot');
        }

        if (is_dir(__DIR__ . '/../Resources/lang')) {
            $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'titan-chatbot');
        }
    }

    private function mergeConfigsFromDir(array $map): void
    {
        foreach ($map as $file => $key) {
            $path = __DIR__ . "/../Config/{$file}.php";
            if (is_file($path)) {
                $this->mergeConfigFrom($path, $key);
            }
        }
    }
}
