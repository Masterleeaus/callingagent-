<?php
namespace Modules\TitanChatbot\AI\Tools;

class QuoteEstimatorTool
{
    public function estimate(array $payload): array
    {
        return [
            'status' => 'needs_pricing_rules',
            'required_fields' => ['service_type', 'bedrooms', 'bathrooms', 'postcode', 'preferred_date'],
            'payload' => $payload,
        ];
    }
}
