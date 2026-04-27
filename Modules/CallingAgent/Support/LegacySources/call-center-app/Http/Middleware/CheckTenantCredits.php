<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\BillingService;
use App\Models\PhoneNumber;

class CheckTenantCredits
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $to = $request->input('To');
        $phoneNumber = PhoneNumber::where('number', $to)->first();

        if ($phoneNumber && $phoneNumber->tenant) {
            if (!$this->billingService->hasCredits($phoneNumber->tenant)) {
                $settings = $phoneNumber->tenant->settings ?? [];
                $locale = $settings['language'] ?? 'ar-XA';
                $lang = explode('-', $locale)[0];

                return response()->view('twiml.insufficient-credits', [
                    'message' => __('messages.insufficient_credits', [], $lang),
                    'voice' => $settings['voice'] ?? 'Polly.Zeina',
                    'language' => $locale,
                ], 200)->header('Content-Type', 'text/xml');
            }
        }

        return $next($request);
    }
}
