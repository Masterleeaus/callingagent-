<?php
namespace Modules\TitanChatbot\AI\DataModels;

class BookingIntentModel extends BaseDataModel
{
    public bool   $isBookingIntent  = false;
    public float  $confidence       = 0.0;
    public string $customerName     = '';
    public string $preferredDate    = '';
    public string $preferredTime    = '';
    public string $contactNumber    = '';
    public string $serviceType      = '';
    public string $notes            = '';

    public function isValid(): bool
    {
        return $this->isBookingIntent && !empty($this->customerName);
    }

    public function isComplete(): bool
    {
        return !empty($this->customerName)
            && !empty($this->preferredDate)
            && !empty($this->contactNumber);
    }
}
