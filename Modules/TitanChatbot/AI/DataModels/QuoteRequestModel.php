<?php
namespace Modules\TitanChatbot\AI\DataModels;

class QuoteRequestModel extends BaseDataModel
{
    public bool   $isQuoteRequest   = false;
    public float  $confidence       = 0.0;
    public string $serviceType      = '';
    public string $propertyType     = '';
    public string $areaSize         = '';
    public string $frequency        = '';
    public string $customerName     = '';
    public string $contactNumber    = '';
    public string $preferredDate    = '';
    public array  $extras           = [];

    public function isValid(): bool
    {
        return $this->isQuoteRequest && !empty($this->serviceType);
    }
}
