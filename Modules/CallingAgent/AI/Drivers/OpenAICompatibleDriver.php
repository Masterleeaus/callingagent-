<?php

declare(strict_types=1);

namespace Modules\CallingAgent\AI\Drivers;

use Modules\CallingAgent\AI\Core\AgentConfig;
use Modules\CallingAgent\AI\Core\AgentEngine;
use Modules\CallingAgent\AI\Messages\AssistantMessage;
use Modules\CallingAgent\AI\Messages\MessageCollection;
use Modules\CallingAgent\AI\Usage\UsageRecord;

final class OpenAICompatibleDriver implements DriverInterface, AgentEngine
{
    private UsageRecord $lastUsage;

    private static array $supportedDrivers = [
        'openai',
        'openai-compatible',
        'groq',
        'openrouter',
    ];

    public function __construct(
        private string $apiKey,
        private string $baseUrl = 'https://api.openai.com/v1',
        private string $defaultModel = 'gpt-4o',
    ) {
        $this->lastUsage = UsageRecord::empty('openai', $defaultModel);
    }

    public function send(array $messages, array $config = []): array
    {
        $model = $config['model'] ?? $this->defaultModel;
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $config['max_tokens'] ?? $config['maxTokens'] ?? 1024,
            'temperature' => $config['temperature'] ?? 0.7,
        ];

        if (! empty($config['tools'])) {
            $payload['tools'] = $config['tools'];
        }

        $response = $this->httpPost('/chat/completions', $payload);

        $this->lastUsage = UsageRecord::fromOpenAiResponse($response, $model);

        return $response;
    }

    public function sendStream(array $messages, array $config, callable $onChunk): void
    {
        $model = $config['model'] ?? $this->defaultModel;
        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $config['max_tokens'] ?? $config['maxTokens'] ?? 1024,
            'temperature' => $config['temperature'] ?? 0.7,
            'stream' => true,
        ], $config);

        if (! function_exists('curl_init')) {
            throw new \RuntimeException('cURL is required for streaming.');
        }

        $ch = curl_init($this->baseUrl . '/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_WRITEFUNCTION => static function ($ch, $data) use ($onChunk): int {
                $onChunk($data);
                return strlen($data);
            },
        ]);

        $success = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($success === false) {
            throw new \RuntimeException('Stream request failed: ' . $error);
        }
    }

    public function getUsage(): UsageRecord
    {
        return $this->lastUsage;
    }

    public function getName(): string
    {
        return 'openai';
    }

    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        return class_exists(\Illuminate\Support\Facades\Http::class) || function_exists('curl_init');
    }

    public function supports(string $driver): bool
    {
        return in_array($driver, self::$supportedDrivers, true);
    }

    // AgentEngine implementation
    public function generate(MessageCollection $messages, AgentConfig $config): AssistantMessage
    {
        $response = $this->send($messages->toOpenAiArray(), [
            'model' => $config->model,
            'max_tokens' => $config->maxTokens,
            'temperature' => $config->temperature,
        ]);

        $choice = $response['choices'][0] ?? [];
        $message = $choice['message'] ?? [];
        $content = (string) ($message['content'] ?? '');
        $toolCalls = $message['tool_calls'] ?? [];

        return new AssistantMessage($content, $toolCalls);
    }

    private function httpPost(string $path, array $payload): array
    {
        $url = rtrim($this->baseUrl, '/') . $path;
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if (class_exists(\Illuminate\Support\Facades\Http::class)) {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if (! $response->successful()) {
                throw new \RuntimeException(
                    'OpenAI API request failed: ' . $response->status() . ' ' . $response->body(),
                );
            }

            return $response->json();
        }

        if (! function_exists('curl_init')) {
            throw new \RuntimeException('Neither Laravel Http facade nor cURL is available.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            throw new \RuntimeException('cURL request failed: ' . $curlError);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException('OpenAI API error ' . $httpCode . ': ' . $result);
        }

        $decoded = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode API response: ' . json_last_error_msg());
        }

        return $decoded;
    }
}
