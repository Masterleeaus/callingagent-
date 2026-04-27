<?php

namespace Modules\TitanChatbot\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    protected $table = 'ext_chatbot_knowledge_base_articles';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'content',
        'is_featured',
        'chatbots',
    ];

    protected $casts = [
        'chatbots'    => 'array',
        'is_featured' => 'boolean',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];
}
