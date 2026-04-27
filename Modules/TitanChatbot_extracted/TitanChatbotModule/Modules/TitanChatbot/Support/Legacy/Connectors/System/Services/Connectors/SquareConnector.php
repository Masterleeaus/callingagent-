<?php

namespace Extensions\Connectors\System\Services\Connectors;

use Extensions\Connectors\System\Models\ConnectorAccount;

class SquareConnector implements ConnectorInterface
{
    public function provider(): string { return 'square'; }
    public function label(): string { return 'Square'; }
    public function credentialFields(): array { return ['access_token', 'location_id']; }

    public function test(ConnectorAccount $account): ConnectorResult
    {
        $creds = $account->getCredentials();
        $token = trim($creds['access_token'] ?? '');
        if (!$token) return ConnectorResult::error('Missing access_token');

        $client = HttpClient::client(['base_uri' => 'https://connect.squareup.com']);
        $resp = $client->get('/v2/locations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Square-Version' => '2024-07-17'
            ]
        ]);
        $code = $resp->getStatusCode();
        $body = json_decode((string)$resp->getBody(), true) ?: [];
        if ($code >= 200 && $code < 300) {
            $locations = $body['locations'] ?? [];
            return ConnectorResult::success('Square OK', ['locations' => $locations]);
        }
        return ConnectorResult::error('Square test failed', ['code' => $code, 'body' => $body]);
    }

    public function run(ConnectorAccount $account, string $action, array $payload): ConnectorResult
    {
        return match ($action) {
            'create_payment_link' => $this->createPaymentLink($account, $payload),
            default => ConnectorResult::error('Unsupported action: ' . $action),
        };
    }

    protected function createPaymentLink(ConnectorAccount $account, array $payload): ConnectorResult
    {
        $creds = $account->getCredentials();
        $token = trim($creds['access_token'] ?? '');
        if (!$token) return ConnectorResult::error('Missing access_token');

        $locationId = $payload['location_id'] ?? ($creds['location_id'] ?? null);
        $amountCents = (int)($payload['amount_cents'] ?? 0);
        $currency = $payload['currency'] ?? 'AUD';
        $description = $payload['description'] ?? 'Payment';

        if (!$locationId) return ConnectorResult::error('Missing location_id');
        if ($amountCents <= 0) return ConnectorResult::error('amount_cents must be > 0');

        $client = HttpClient::client(['base_uri' => 'https://connect.squareup.com']);
        $resp = $client->post('/v2/online-checkout/payment-links', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Square-Version' => '2024-07-17'
            ],
            'json' => [
                'idempotency_key' => (string)($payload['idempotency_key'] ?? \Str::uuid()),
                'quick_pay' => [
                    'name' => $description,
                    'location_id' => $locationId,
                    'price_money' => [
                        'amount' => $amountCents,
                        'currency' => $currency,
                    ]
                ]
            ]
        ]);

        $code = $resp->getStatusCode();
        $body = json_decode((string)$resp->getBody(), true) ?: [];

        if ($code >= 200 && $code < 300) {
            $url = $body['payment_link']['url'] ?? null;
            return ConnectorResult::success('Payment link created', ['url' => $url, 'body' => $body]);
        }
        return ConnectorResult::error('Square create_payment_link failed', ['code' => $code, 'body' => $body]);
    }
}
