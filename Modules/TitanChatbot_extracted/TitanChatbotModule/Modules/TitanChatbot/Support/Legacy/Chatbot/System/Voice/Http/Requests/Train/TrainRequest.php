<?php

namespace App\Extensions\Chatbot\System\Voice\Http\Requests\Train;

use App\Extensions\Chatbot\System\Voice\Models\ExtVoiceChatbot;
use App\Extensions\Chatbot\System\Voice\Models\ExtVoicechatbotTrain;
use Illuminate\Foundation\Http\FormRequest;

class TrainRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'     => 'required|exists:' . (new ExtVoiceChatbot)->getTable() . ',id',
            'data'   => 'required|array',
            'data.*' => 'required|exists:' . (new ExtVoicechatbotTrain)->getTable() . ',id',
        ];
    }
}
