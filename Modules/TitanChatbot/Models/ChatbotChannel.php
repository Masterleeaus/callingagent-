<?php

namespace Modules\TitanChatbot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotChannel extends Model
{
    protected $table = 'ext_chatbot_channels';

    protected $fillable = [
        'user_id',
        'chatbot_id',
        'channel',
        'credentials',
        'payload',
        'connected_at',
    ];

    protected $casts = [
        'credentials'  => 'array',
        'payload'      => 'array',
        'connected_at' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class, 'chatbot_id');
    }
}
