<?php

namespace Modules\TitanChatbot\AI\Pipelines;

use Illuminate\Support\Facades\Log;
use Modules\TitanChatbot\Services\GeneratorBridge;
use Throwable;

class RagAnswerPipeline
{
    public function answer(string $query, int $chatbotId, array $ragContext = []): string
    {
        $contextBlock = '';

        foreach ($ragContext as $chunk) {
            $content = $chunk['content'] ?? '';
            if ($content !== '') {
                $contextBlock .= "- {$content}\n";
            }
        }

        $prompt = $contextBlock !== ''
            ? "Use the following context to answer the question.\n\nContext:\n{$contextBlock}\nQuestion: {$query}"
            : $query;

        try {
            return app(GeneratorBridge::class)->generate($prompt);
        } catch (Throwable $e) {
            Log::warning('RagAnswerPipeline: generation failed.', ['error' => $e->getMessage()]);

            return 'I\'m sorry, I couldn\'t retrieve an answer at this time.';
        }
    }
}
