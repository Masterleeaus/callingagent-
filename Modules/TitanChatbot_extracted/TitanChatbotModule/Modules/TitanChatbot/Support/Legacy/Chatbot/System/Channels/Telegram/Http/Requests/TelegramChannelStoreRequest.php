<?php

namespace App\Extensions\Chatbot\System\Channels\Telegram\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelegramChannelStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'channel'     => 'required|string',
            'user_id'     => 'required',
            'chatbot_id'  => 'required',
            'credentials' => 'array|required',
            'connected_at'=> 'string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id'      => auth()->id(),
            'connected_at' => (string) now(),
            'channel'      => 'telegram',
        ]);
    }
}
