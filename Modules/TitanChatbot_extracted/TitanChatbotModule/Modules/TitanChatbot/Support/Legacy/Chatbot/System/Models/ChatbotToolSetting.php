<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotToolSetting extends Model
{
    protected $table = 'ext_chatbot_tool_settings';

    protected $fillable = [
        'chatbot_id',
        'tool_key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
