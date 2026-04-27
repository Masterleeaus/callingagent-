<?php

namespace Extensions\Connectors\System\Services\Connectors;

use Extensions\Connectors\System\Models\ConnectorAccount;

class StripeConnector implements ConnectorInterface
{
    public function provider(): string { return 'stripe'; }
    public function label(): string { return 'Stripe'; }
    public function credentialFields(): array { return ['secret_key']; }

    public function test(ConnectorAccount $account): ConnectorResult
    {
        $creds = $account->getCredentials();
        $key = $creds['secret_key'] ?? '';
        if (!$key) return ConnectorResult::error('Missing Stripe secret key.');
        $client = HttpClient::client();
        $resp = $client->request('GET', 'https://api.stripe.com/v1/account', [
            'headers' => ['Authorization' => 'Bearer '.$key],
        ]);
        $code = $resp->getStatusCode();
        $body = json_decode((string)$resp->getBody(), true) ?: [];
        if ($code >= 200 && $code < 300) {
            return ConnectorResult::success('Stripe connected.', ['id' => $body['id'] ?? null]);
        }
        return ConnectorResult::error('Stripe test failed.', ['status' => $code, 'body' => $body]);
    }

    public function actions(): array
    {
        return [
            'create_checkout_session' => 'Create Checkout Session (Payment Link)',
        ];
    }

    public function run(string $action, array $payload, ConnectorAccount $account): ConnectorResult
    {
        if ($action !== 'create_checkout_session') {
            return ConnectorResult::error('Unknown action.');
        }
        $creds = $account->getCredentials();
        $key = $creds['secret_key'] ?? '';
        if (!$key) return ConnectorResult::error('Missing Stripe secret key.');

        $amountCents = (int)($payload['amount_cents'] ?? 0);
        $currency = $payload['currency'] ?? 'aud';
        $name = $payload['name'] ?? 'Job payment';
        $successUrl = $payload['success_url'] ?? url('/dashboard');
        $cancelUrl = $payload['cancel_url'] ?? url('/dashboard');

        if ($amountCents <= 0) {
            return ConnectorResult::error('amount_cents must be > 0');
        }

        $client = HttpClient::client();
        $resp = $client->request('POST', 'https://api.stripe.com/v1/checkout/sessions', [
            'headers' => ['Authorization' => 'Bearer '.$key],
            'form_params' => [
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => $currency,
                'line_items[0][price_data][unit_amount]' => $amountCents,
                'line_items[0][price_data][product_data][name]' => $name,
            ],
        ]);
        $code = $resp->getStatusCode();
        $body = json_decode((string)$resp->getBody(), true) ?: [];
        if ($code >= 200 && $code < 300) {
            return ConnectorResult::success('Checkout session created.', ['url' => $body['url'] ?? null, 'id' => $body['id'] ?? null]);
        }
        return ConnectorResult::error('Stripe create session failed.', ['status' => $code, 'body' => $body]);
    }
}
