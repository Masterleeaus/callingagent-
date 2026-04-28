<?php

namespace Modules\TitanChatbot\AI\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingResolver
{
    public function resolve(string $text): array
    {
        $apiKey = config('titan-chatbot.ai.openai_api_key', config('openai.api_key', env('OPENAI_API_KEY')));
        $model  = config('titan-chatbot.ai.embeddings_model', 'text-embedding-3-small');

        if (empty($apiKey)) {
            Log::warning('EmbeddingResolver: no API key configured, returning empty embedding.');

            return [];
        }

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.openai.com/v1/embeddings', [
                    'input' => $text,
                    'model' => $model,
                ]);

            if ($response->failed()) {
                Log::warning('EmbeddingResolver: API request failed.', ['status' => $response->status()]);

                return [];
            }

            return $response->json('data.0.embedding', []);
        } catch (\Throwable $e) {
            Log::warning('EmbeddingResolver: exception during resolve.', ['error' => $e->getMessage()]);

            return [];
        }
    }
}
