<?php
namespace Modules\TitanChatbot\Workflows\Definitions;

class LeadToBookingWorkflow
{
    public function steps(): array
    {
        return ['capture_intent', 'qualify_service_area', 'collect_job_details', 'estimate_quote', 'book_or_escalate'];
    }
}
