<?php

namespace Modules\TitanGo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTitanGoEntitlement
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->tenantHasTitanGo()) {
            return $next($request);
        }

        return redirect()->route('dashboard.user.titango.upgrade');
    }

    protected function tenantHasTitanGo(): bool
    {
        // Dev/testing override
        if ((bool) config('titango.force_enabled', false)) {
            return true;
        }

        // MVP entitlement source: company_settings (if present)
        try {
            $companyId = (function_exists('company') && company()) ? company()->id : null;
            if (!$companyId) return false;

            $enabled = \DB::table('company_settings')
                ->where('company_id', $companyId)
                ->where('key', 'titango_enabled')
                ->value('value');

            return (string) $enabled === '1';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
