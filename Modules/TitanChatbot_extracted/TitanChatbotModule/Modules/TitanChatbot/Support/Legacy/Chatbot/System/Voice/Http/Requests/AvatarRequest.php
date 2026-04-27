<?php

namespace App\Extensions\Chatbot\System\Voice\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvatarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'max:4096'],
        ];
    }
}
