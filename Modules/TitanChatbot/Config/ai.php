<?php
return [
    'module'           => 'TitanChatbot',
    'area'             => 'ai',
    'enabled'          => true,
    'provider'         => env('TITAN_CHATBOT_AI_PROVIDER', 'openai'),
    'openai_api_key'   => env('OPENAI_API_KEY'),
    'model'            => env('TITAN_CHATBOT_MODEL', 'gpt-4o-mini'),
    'embeddings_model' => env('TITAN_CHATBOT_EMBEDDINGS_MODEL', 'text-embedding-3-small'),
    'max_tokens'       => (int) env('TITAN_CHATBOT_MAX_TOKENS', 1024),
    'temperature'      => (float) env('TITAN_CHATBOT_TEMPERATURE', 0.7),
    'rag_chunks_limit' => (int) env('TITAN_CHATBOT_RAG_CHUNKS', 5),
    'memory_limit'     => (int) env('TITAN_CHATBOT_MEMORY_LIMIT', 20),
    'fallback_message' => env('TITAN_CHATBOT_FALLBACK', "I'm sorry, I can't answer that right now. Please try again shortly."),
];
