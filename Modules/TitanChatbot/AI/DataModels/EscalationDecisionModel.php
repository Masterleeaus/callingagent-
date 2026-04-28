<?php
namespace Modules\TitanChatbot\AI\DataModels;

class EscalationDecisionModel extends BaseDataModel
{
    public bool   $shouldEscalate   = false;
    public float  $confidence       = 1.0;
    public string $reason           = '';
    public string $urgency          = 'normal'; // low, normal, high, critical
    public string $suggestedTeam    = '';
    public array  $triggerKeywords  = [];

    public function isValid(): bool
    {
        return in_array($this->urgency, ['low', 'normal', 'high', 'critical'], true);
    }
}
