<?php

namespace Modules\CallingAgent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTwilioSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        if (env('CALLING_AGENT_SKIP_TWILIO_VALIDATION', false)) {
            return $next($request);
        }

        $authToken = config('services.twilio.token', env('TWILIO_AUTH_TOKEN', ''));

        if (empty($authToken)) {
            return $next($request);
        }

        $url = $request->fullUrl();
        $postData = $request->post();
        ksort($postData);
        $str = $url;
        foreach ($postData as $key => $val) {
            $str .= $key . $val;
        }

        $signature = base64_encode(hash_hmac('sha1', $str, $authToken, true));
        $expected = $request->header('X-Twilio-Signature', '');

        if (!hash_equals($signature, $expected)) {
            return response()->json(['error' => 'Invalid Twilio signature'], 403);
        }

        return $next($request);
    }
}
