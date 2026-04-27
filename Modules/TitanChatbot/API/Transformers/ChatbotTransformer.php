<?php

namespace Modules\TitanChatbot\API\Transformers;

class ChatbotTransformer
{
    public function transform($chatbot): array
    {
        return [
            'id'         => $chatbot->id,
            'name'       => $chatbot->name,
            'model'      => $chatbot->model,
            'channels'   => $chatbot->channels ?? [],
            'is_active'  => (bool) $chatbot->is_active,
            'created_at' => $chatbot->created_at?->toIso8601String(),
        ];
    }

    public function transformCollection(iterable $chatbots): array
    {
        $result = [];
        foreach ($chatbots as $chatbot) {
            $result[] = $this->transform($chatbot);
        }

        return $result;
    }
}
