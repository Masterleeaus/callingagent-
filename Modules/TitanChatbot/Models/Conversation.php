<?php

namespace Modules\TitanChatbot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $table = 'ext_chatbot_conversations';

    protected $fillable = [
        'chatbot_id',
        'session_id',
        'conversation_name',
        'ip_address',
        'connect_agent_at',
        'last_activity_at',
        'customer_payload',
        'chatbot_channel',
        'chatbot_channel_id',
        'customer_channel_id',
        'customer_id',
        'company_id',
        'ticket_status',
        'country_code',
        'chatbot_customer_id',
        'pinned',
        'send_email_at',
        'is_showed_on_history',
    ];

    protected $casts = [
        'customer_payload'  => 'array',
        'connect_agent_at'  => 'datetime',
        'last_activity_at'  => 'datetime',
        'send_email_at'     => 'datetime',
        'pinned'            => 'boolean',
        'is_showed_on_history' => 'boolean',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class, 'chatbot_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotHistory::class, 'conversation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ChatbotCustomer::class, 'chatbot_customer_id');
    }
}
