<?php

namespace Modules\TitanChatbot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotHistory extends Model
{
    protected $table = 'ext_chatbot_histories';

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'chatbot_id',
        'conversation_id',
        'message_id',
        'user_id',
        'role',
        'model',
        'message',
        'type',
        'message_type',
        'content_type',
        'read_at',
        'media_url',
        'media_name',
    ];

    protected $casts = [
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class, 'chatbot_id');
    }
}
