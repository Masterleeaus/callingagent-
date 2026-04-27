<?php

namespace App\Extensions\Chatbot\System\Voice\Http\Requests\Train;

use App\Extensions\Chatbot\System\Voice\Enums\TrainTypeEnum;
use App\Extensions\Chatbot\System\Voice\Models\ExtVoiceChatbot;
use Illuminate\Foundation\Http\FormRequest;

class DataRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'         => 'required|exists:' . (new ExtVoiceChatbot)->getTable() . ',id',
            'type'       => ['sometimes', 'nullable', 'in:' . implode(',', TrainTypeEnum::toArray())],
        ];
    }
}
