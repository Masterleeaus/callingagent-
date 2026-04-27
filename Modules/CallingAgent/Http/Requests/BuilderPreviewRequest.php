<?php
namespace Modules\CallingAgent\Http\Requests;

class BuilderPreviewRequest
{
    public function rules(): array
    {
        return [
            'agent' => ['array'],
            'caller' => ['array'],
            'context' => ['array'],
            'outcome' => ['array'],
            'routing' => ['array'],
        ];
    }
}
