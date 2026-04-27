<?php

namespace App\Extensions\Chatbot\System\Voice\Http\Requests\Train;

use App\Extensions\Chatbot\System\Voice\Models\ExtVoiceChatbot;
use Illuminate\Foundation\Http\FormRequest;

class TrainUrlRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'     => 'required|exists:' . (new ExtVoiceChatbot)->getTable() . ',id',
            'url'    => ['required', 'url'],
            'single' => ['required', 'in:1,0'],
        ];
    }
}
