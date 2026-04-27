<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System;

use App\Domains\Marketplace\Contracts\ExtensionRegisterKeyProviderInterface;
use App\Extensions\Chatbot\System\Http\Controllers\Api\ChatbotApplicationController;
use App\Extensions\Chatbot\System\Http\Controllers\Api\ChatbotFrameController;
use App\Extensions\Chatbot\System\Http\Controllers\Api\ChatbotWorkflowController;
use App\Extensions\Chatbot\System\Http\Controllers\AvatarController;
use App\Extensions\Chatbot\System\Http\Controllers\ChatbotController;
use App\Extensions\Chatbot\System\Http\Controllers\ChatbotCustomerController;
use App\Extensions\Chatbot\System\Http\Controllers\ChatbotKnowledgeBaseArticleController;
use App\Extensions\Chatbot\System\Http\Controllers\ChatbotMultiChannelController;
use App\Extensions\Chatbot\System\Http\Controllers\ChatbotTrainController;
use App\Extensions\Chatbot\System\Http\Controllers\Dashboard\ChatbotAutomationSettingsController;
use App\Extensions\Chatbot\System\Http\Controllers\Dashboard\ChatbotIntegrationsController;
use App\Extensions\Chatbot\System\Http\Controllers\Dashboard\ChatbotWorkflowSettingsController;
use App\Extensions\Chatbot\System\Http\Middleware\LanguageMiddleware;
use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotKnowledgeBaseArticle;
use App\Extensions\Chatbot\System\Policies\ChatbotKnowledgeBaseArticlePolicy;
use App\Extensions\Chatbot\System\Policies\ChatbotPolicy;
use App\Helpers\Classes\Helper;
use App\Http\Middleware\CheckTemplateTypeAndPlan;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider implements ExtensionRegisterKeyProviderInterface
{
    public function register(): void
    {
        $this->registerConfig();
    }

    public function boot(Kernel $kernel): void
    {
        $this->registerTranslations()
            ->registerViews()
            ->registerRoutes()
            ->registerMigrations()
            ->publishAssets()
            ->registerPolicies()
            ->registerCommand();

    }

    public function registerPolicies(): self
    {
        Gate::policy(Chatbot::class, ChatbotPolicy::class);
        Gate::policy(ChatbotKnowledgeBaseArticle::class, ChatbotKnowledgeBaseArticlePolicy::class);

        return $this;
    }

    public function registerCommand(): static
    {
        if (Helper::appIsDemo()) {
            $this->commands([
                Console\Commands\ClearDemoModeCommand::class,
            ]);

            //            if ($this->app->runningInConsole()) {
            //                $this->app->booted(function () {
            //                    $schedule = $this->app->make(Schedule::class);
            //                    $schedule->command('app:clear-chatbot-demo-mode')->everyMinute();
            //                });
            //            }
        }

        return $this;
    }

    public function publishAssets(): static
    {
        $this->publishes([
            __DIR__ . '/../resources/assets/js'     => public_path('vendor/chatbot/js'),
            __DIR__ . '/../resources/assets/images' => public_path('vendor/chatbot/images'),
            __DIR__ . '/../resources/assets/icons'  => public_path('vendor/chatbot/icons'),
            __DIR__ . '/../resources/assets/icons'  => public_path('vendor/chatbot-multi-channel/icons'),
        ], 'extension');

        // Optional: agent and voice add-ons live inside this combined extension
        $this->publishes([
            __DIR__ . '/../resources/assets/agent' => public_path('vendor/chatbot-agent'),
        ], 'extension');

        $this->publishes([
            __DIR__ . '/../resources/assets/voice' => public_path('vendor/chatbot-voice'),
        ], 'extension');

        return $this;
    }

    public function registerConfig(): static
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/chatbot.php', $this->registerKey());
        // Workflow registry (horizontal, webhook-first)
        $this->mergeConfigFrom(__DIR__ . '/../config/workflows.php', $this->registerKey().'.workflows');
        // Tool registry (metadata + defaults)
        $this->mergeConfigFrom(__DIR__ . '/../config/tools.php', $this->registerKey().'.tools');

        return $this;
    }

    protected function registerTranslations(): static
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', $this->registerKey());

        return $this;
    }

    public function registerViews(): static
    {
        // Base chatbot views
        $this->loadViewsFrom([__DIR__ . '/../resources/views'], $this->registerKey());

        // Channel cards (kept as view namespaces for backward compatibility with existing includes)
        $this->loadViewsFrom([__DIR__ . '/../resources/views/channels/whatsapp'], 'whatsapp-channel');
        $this->loadViewsFrom([__DIR__ . '/../resources/views/channels/telegram'], 'telegram-channel');
        $this->loadViewsFrom([__DIR__ . '/../resources/views/channels/messenger'], 'messenger-channel');

        // Agent + Voice add-ons
        $this->loadViewsFrom([__DIR__ . '/../resources/views/agent'], 'chatbot-agent');
        $this->loadViewsFrom([__DIR__ . '/../resources/views/voice'], 'chatbot-voice');

        return $this;
    }

    public function registerMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        return $this;
    }

    private function registerRoutes(): static
    {
        $this->router()
            ->group([
                'middleware' => 'web',
            ], function (Router $router) {
                $router
                    ->controller(ChatbotFrameController::class)
                    ->group(function (Router $router) {
                        $router->get('chatbot/{chatbot:uuid}/frame', 'frame')->name('chatbot.frame');
                    });
            })
            ->group([
                'middleware'     => ['api', LanguageMiddleware::class],
                'prefix'         => 'api/v2/chatbot',
                'as'             => 'api.v2.chatbot.',
                'controller'     => ChatbotApplicationController::class,
            ], function (Router $router) {
                $router->get('{chatbot:uuid}', 'index')->name('index');
                $router->get('{chatbot:uuid}/articles', 'articles')->name('articles');
                $router->get('{chatbot:uuid}/articles/{id}/show', 'showArticles')->name('articles.show');
                $router->get('{chatbot:uuid}/session/{sessionId}', 'indexSession')->name('index.session');
                $router->post('{chatbot:uuid}/session/{sessionId}/conversation', 'conversionStore')->name('conversion.store');
                $router->post('{chatbot:uuid}/session/{sessionId}/conversation/connect', 'connectSupport')->name('conversion.connect.support');
                $router->get('{chatbot:uuid}/session/{sessionId}/conversation/{chatbotConversation}', 'conversion')->name('conversion.show');
                $router->get('{chatbot:uuid}/session/{sessionId}/conversation/{chatbotConversation}/messages', 'messages')->name('conversion.messages');
                $router->get('{chatbot:uuid}/session/{sessionId}/conversation/{chatbotConversation}/export', 'export')->name('conversion.export');
                $router->post('{chatbot:uuid}/session/{sessionId}/conversation/{chatbotConversation}/messages', 'storeMessage')->name('conversion.store.message');
                $router->post('{chatbot:uuid}/session/{sessionId}/conversation/{chatbotConversation}/file', 'storeFile')->name('conversion.store.file');
                $router->post('{chatbot:uuid}/session/{sessionId}/send-email', 'sendEmail')->name('send-email.store');
                $router->any('{chatbot:uuid}/session/{sessionId}/enable-sound', 'enableSound')->name('enable-sound');
                $router->post('{chatbot:uuid}/session/{sessionId}/collect-email', 'collectEmail')->name('collect.email');
            })

            // Workflows (horizontal, webhook-first)
            ->group([
                'middleware'     => ['api', LanguageMiddleware::class],
                'prefix'         => 'api/v2/chatbot',
                'as'             => 'api.v2.chatbot.workflows.',
                'controller'     => ChatbotWorkflowController::class,
            ], function (Router $router) {
                $router->get('{chatbot:uuid}/session/{sessionId}/workflows', 'list')->name('list');
                $router->post('{chatbot:uuid}/session/{sessionId}/workflows/propose', 'propose')->name('propose');
                $router->post('{chatbot:uuid}/session/{sessionId}/workflows/confirm', 'confirm')->name('confirm');
                $router->post('{chatbot:uuid}/session/{sessionId}/workflows/execute', 'execute')->name('execute');
            })

            // Channel webhooks (combined add-ons)
            ->group([
                'middleware'     => ['api', LanguageMiddleware::class],
                'prefix'         => 'api/v2/chatbot',
                'as'             => 'api.v2.chatbot.channel.',
            ], function (Router $router) {
                $router->any('{chatbotId}/channel/{channelId}/whatsapp', [\App\Extensions\Chatbot\System\Channels\Whatsapp\Http\Controllers\Webhook\ChatbotTwilioController::class, 'handle'])->name('whatsapp.post.handle');
                $router->any('{chatbotId}/channel/{channelId}/telegram', [\App\Extensions\Chatbot\System\Channels\Telegram\Http\Controllers\Webhook\ChatbotTelegramWebhookController::class, 'handle'])->name('telegram.post.handle');
                $router->any('{chatbotId}/channel/{channelId}/messenger', [\App\Extensions\Chatbot\System\Channels\Messenger\Http\Controllers\Webhook\ChatbotMessengerWebhookController::class, 'handle'])->name('messenger.post.handle');
            })

            ->group([
                'middleware' => ['web', 'auth'],
            ], function (Router $route) {
                $route->controller(ChatbotMultiChannelController::class)
                    ->name('dashboard.chatbot-multi-channel.')
                    ->prefix('dashboard/chatbot-multi-channel')
                    ->group(function () {
                        Route::any('', 'index')->name('index');
                        Route::POST('delete', 'delete')->name('delete');
                    });
                // Channel connect (store) endpoints
                $route->post('dashboard/chatbot-multi-channel/whatsapp/store', [\App\Extensions\Chatbot\System\Channels\Whatsapp\Http\Controllers\ChatbotWhatsappController::class, 'store'])->name('dashboard.chatbot-multi-channel.whatsapp.store');
                $route->post('dashboard/chatbot-multi-channel/telegram/store', [\App\Extensions\Chatbot\System\Channels\Telegram\Http\Controllers\ChatbotTelegramController::class, 'store'])->name('dashboard.chatbot-multi-channel.telegram.store');
                $route->post('dashboard/chatbot-multi-channel/messenger/store', [\App\Extensions\Chatbot\System\Channels\Messenger\Http\Controllers\ChatbotMessengerController::class, 'store'])->name('dashboard.chatbot-multi-channel.messenger.store');
                $route->group([
                    'prefix'         => 'dashboard/chatbot',
                    'as'             => 'dashboard.chatbot.',
                ], function (Router $router) {
                    $router->resource('knowledge-base-article', ChatbotKnowledgeBaseArticleController::class);
                    $router->resource('chatbot-customer', ChatbotCustomerController::class);
                    // Workflow settings (horizontal / multi-SaaS)
                    $router->get('{chatbot}/workflows', [ChatbotWorkflowSettingsController::class, 'index'])->name('workflows.index');
                    $router->post('{chatbot}/workflows', [ChatbotWorkflowSettingsController::class, 'update'])->name('workflows.update');

                    // Unified Automation settings (workflows + tools + gateway)
                    $router->get('{chatbot}/automation', [ChatbotAutomationSettingsController::class, 'index'])->name('automation.index');
                    $router->post('{chatbot}/automation', [ChatbotAutomationSettingsController::class, 'update'])->name('automation.update');
                    // Integrations (Channels + Connectors)
                    $router->get('{chatbot}/integrations', [\App\Extensions\Chatbot\System\Http\Controllers\Dashboard\ChatbotIntegrationsController::class, 'index'])->name('integrations.index');

                });
                $route
                    ->controller(ChatbotController::class)
                    ->prefix('dashboard/chatbot')
                    ->name('dashboard.chatbot.')
                    ->group(function (Router $route) {
                        $route->get('', 'index')
                            ->name('index')
                            ->middleware(CheckTemplateTypeAndPlan::class);
                        $route->post('', 'store')->name('store');
                        $route->post('update', 'update')->name('update');
                        $route->post('delete', 'delete')->name('delete');

                        // conversation
                        $route->get('conversations', 'conversations')->name('conversations');
                        $route->get('conversations-with-paginate', 'conversationsWithPaginate')->name('conversations.with.paginate');
                        $route->post('conversations/search', 'searchConversation')->name('conversations.search');

                        // ended routes
                        $route->get('{chatbot}/enbed', 'enbed')->name('enbed');
                    });
                $route
                    ->controller(ChatbotTrainController::class)
                    ->prefix('dashboard/chatbot/train')
                    ->name('dashboard.chatbot.train.')
                    ->group(function (Router $route) {
                        // train routes
                        $route->get('data', 'trainData')->name('data');
                        $route->post('delete-embedding', 'deleteEmbedding')->name('delete');
                        $route->post('generate-embedding', 'generateEmbedding')->name('generate.embedding');
                        $route->get('{chatbot}', 'train')->name('index');
                        $route->post('url', 'trainUrl')->name('url');
                        $route->post('file', 'trainFile')->name('file');
                        $route->post('text', 'trainText')->name('text');
                        $route->post('qa', 'trainQa')->name('qa');
                    });
                $route->post('dashboard/chatbot/avatar/upload', AvatarController::class)
                    ->name('dashboard.chatbot.upload.avatar');
            });

        return $this;
    }

    private function router(): Router|Route
    {
        return $this->app['router'];
    }

    public function registerKey(): string
    {
        return 'chatbot';
    }
}
