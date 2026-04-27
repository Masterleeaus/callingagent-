<?php

namespace Modules\TitanChatbot\Workflows\Definitions;

class LeadCaptureWorkflow
{
    public function name(): string
    {
        return 'lead_capture';
    }

    public function steps(): array
    {
        return [
            'CollectName',
            'CollectEmail',
            'CollectPhone',
            'SaveLead',
        ];
    }

    public function guards(): array
    {
        return [];
    }
}
