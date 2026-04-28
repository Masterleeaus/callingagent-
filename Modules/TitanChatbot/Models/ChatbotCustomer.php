<?php

namespace Modules\TitanChatbot\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotCustomer extends Model
{
    protected $table = 'ext_chatbot_customers';

    protected $fillable = [
        'user_id',
        'avatar',
        'name',
        'email',
        'phone',
        'chatbot_id',
        'session_id',
        'country_code',
        'ip_address',
        'chatbot_channel',
        'payload',
        'enabled_sound',
    ];

    protected $casts = [
        'payload'       => 'array',
        'enabled_sound' => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'chatbot_customer_id');
    }
}
