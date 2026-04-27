<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Subscription;

class BillingService
{
    public function hasCredits(Tenant $tenant): bool
    {
        return $tenant->minutes_balance > 0;
    }

    public function deductMinutes(Tenant $tenant, int $minutes)
    {
        $tenant->decrement('minutes_balance', $minutes);
    }
}
