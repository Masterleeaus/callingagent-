<?php

declare(strict_types=1);

namespace App\Extensions\Chatbot\System\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotWorkflowSetting extends Model
{
    protected $table = 'ext_chatbot_workflow_settings';

    protected $fillable = [
        'chatbot_id',
        'workflow_key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'bool',
    ];
}
