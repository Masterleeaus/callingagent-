<?php

namespace App\Extensions\Chatbot\System\Voice\Models;

use App\Extensions\Chatbot\System\Voice\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtVoicechatbotHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'message',
    ];

    public $casts = [
        'role' => RoleEnum::class,
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ExtVoicechabotConversation::class, 'conversation_id');
    }
}
