<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Http\Controllers\Dashboard;

use App\Extensions\Chatbot\System\Models\Chatbot;
use App\Extensions\Chatbot\System\Models\ChatbotChannel;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ChatbotIntegrationsController
{
    public function index(Chatbot $chatbot): View
    {
        $channels = ChatbotChannel::query()
            ->where('chatbot_id', $chatbot->getKey())
            ->orderBy('id')
            ->get();

        // Optional: show Connector extension accounts if the Connectors extension exists.
        $connectorAccounts = [];
        if (class_exists('Extensions\\Connectors\\System\\Models\\ConnectorAccount')) {
            try {
                /** @var class-string $model */
                $model = 'Extensions\\Connectors\\System\\Models\\ConnectorAccount';
                $connectorAccounts = $model::query()->orderBy('id')->get();
            } catch (\Throwable $e) {
                $connectorAccounts = [];
            }
        }

        // Channel summary rows
        $channelRows = [];
        foreach ($channels as $ch) {
            $creds = is_array($ch->credentials) ? $ch->credentials : (json_decode((string) $ch->credentials, true) ?: []);
            $payload = is_array($ch->payload) ? $ch->payload : (json_decode((string) $ch->payload, true) ?: []);

            $channelRows[] = [
                'id' => $ch->getKey(),
                'channel' => (string) $ch->channel,
                'connected_at' => $ch->connected_at,
                'has_credentials' => !empty($creds),
                'meta' => [
                    'label' => (string) Arr::get($payload, 'label', ''),
                    'account' => (string) Arr::get($payload, 'account', ''),
                ],
            ];
        }

        return view('chatbot::dashboard/integrations/index', [
            'chatbot' => $chatbot,
            'channelRows' => $channelRows,
            'connectorAccounts' => $connectorAccounts,
            'connectorsRouteAvailable' => function_exists('route') && \Illuminate\Support\Facades\Route::has('connectors.index'),
        ]);
    }
}
