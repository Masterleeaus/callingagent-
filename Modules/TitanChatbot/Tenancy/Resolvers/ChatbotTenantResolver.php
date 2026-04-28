<?php

namespace Modules\TitanChatbot\Tenancy\Resolvers;

use Modules\TitanChatbot\Models\Chatbot;

class ChatbotTenantResolver
{
    public function resolve(int $chatbotId): ?int
    {
        $chatbot = Chatbot::find($chatbotId);

        if ($chatbot === null) {
            return null;
        }

        return isset($chatbot->company_id) ? (int) $chatbot->company_id : null;
    }
}
