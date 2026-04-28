<?php

namespace Modules\TitanGo\Services;

use Illuminate\Support\Facades\Http;

class TitanZeroActionClient
{
    public function dispatch(array $payload): array
    {
        $url = config('titango.titanzero_action_url');

        if (!$url) {
            return [
                'ok' => false,
                'error' => 'TitanZero action endpoint not configured. Set TITANGO_TITANZERO_ACTION_URL in .env',
            ];
        }

        $resp = Http::asJson()->post($url, $payload);

        if (!$resp->ok()) {
            return [
                'ok' => false,
                'status' => $resp->status(),
                'body' => $resp->json(),
            ];
        }

        return $resp->json();
    }
}
