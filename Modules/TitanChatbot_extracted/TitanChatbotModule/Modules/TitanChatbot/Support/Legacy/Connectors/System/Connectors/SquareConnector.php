<?php

namespace Extensions\Connectors\System\Connectors;

use Extensions\Connectors\System\Services\ConnectorStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SquareConnector
{
    protected string $provider = 'square';

    protected function creds(): array
    {
        return ConnectorStore::getAccount($this->provider)['credentials'] ?? [];
    }

    public function test(): array
    {
        $c = $this->creds();
        $token = (string)($c['access_token'] ?? '');
        if ($token === '') {
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Missing access_token'];
        }

        try {
            $res = Http::withToken($token)
                ->withHeaders(['Square-Version' => (string)($c['square_version'] ?? '2024-06-04')])
                ->get('https://connect.squareup.com/v2/locations');

            if ($res->successful()) {
                return ['ok' => true, 'provider' => $this->provider, 'message' => 'Connected', 'data' => $res->json()];
            }
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Square error', 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'message' => $e->getMessage()];
        }
    }

    public function run(string $action, array $payload): array
    {
        return match ($action) {
            'create_payment_link' => $this->createPaymentLink($payload),
            default => ['ok' => false, 'provider' => $this->provider, 'action' => $action, 'message' => 'Unknown action'],
        };
    }

    protected function createPaymentLink(array $payload): array
    {
        $c = $this->creds();
        $token = (string)($c['access_token'] ?? '');
        $locationId = (string)($c['location_id'] ?? '');
        if ($token === '' || $locationId === '') {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Missing access_token or location_id'];
        }

        $amount = (float)($payload['amount'] ?? 0);
        $currency = strtoupper((string)($payload['currency'] ?? 'AUD'));
        $description = (string)($payload['description'] ?? 'Payment');

        $minor = (int) round($amount * 100);
        if ($minor <= 0) {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Amount must be > 0'];
        }

        $body = [
            'idempotency_key' => (string) Str::uuid(),
            'quick_pay' => [
                'name' => $description,
                'price_money' => [
                    'amount' => $minor,
                    'currency' => $currency,
                ],
                'location_id' => $locationId,
            ],
        ];

        try {
            $res = Http::withToken($token)
                ->withHeaders(['Square-Version' => (string)($c['square_version'] ?? '2024-06-04')])
                ->post('https://connect.squareup.com/v2/online-checkout/payment-links', $body);

            if ($res->successful()) {
                $j = $res->json();
                $url = $j['payment_link']['url'] ?? null;
                return ['ok' => true, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Created', 'data' => $j, 'url' => $url];
            }

            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Square error', 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => $e->getMessage()];
        }
    }
}
