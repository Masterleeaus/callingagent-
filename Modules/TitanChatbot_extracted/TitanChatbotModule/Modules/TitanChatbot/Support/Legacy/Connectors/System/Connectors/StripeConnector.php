<?php

namespace Extensions\Connectors\System\Connectors;

use Extensions\Connectors\System\Services\ConnectorStore;
use Illuminate\Support\Facades\Http;

class StripeConnector
{
    protected string $provider = 'stripe';

    protected function creds(): array
    {
        return ConnectorStore::getAccount($this->provider)['credentials'] ?? [];
    }

    public function test(): array
    {
        $c = $this->creds();
        $key = (string)($c['secret_key'] ?? '');
        if ($key === '') {
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Missing secret_key'];
        }

        try {
            $res = Http::withToken($key)
                ->asForm()
                ->get('https://api.stripe.com/v1/account');
            if ($res->successful()) {
                return ['ok' => true, 'provider' => $this->provider, 'message' => 'Connected', 'data' => $res->json()];
            }
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Stripe error', 'data' => $res->json()];
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
        $key = (string)($c['secret_key'] ?? '');
        if ($key === '') {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Missing secret_key'];
        }

        $amount = (float)($payload['amount'] ?? 0);
        $currency = strtolower((string)($payload['currency'] ?? 'aud'));
        $description = (string)($payload['description'] ?? 'Payment');
        $successUrl = (string)($c['success_url'] ?? url('/dashboard'));
        $cancelUrl = (string)($c['cancel_url'] ?? url('/dashboard'));

        $unitAmount = (int) round($amount * 100);
        if ($unitAmount <= 0) {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Amount must be > 0'];
        }

        try {
            $res = Http::withToken($key)->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => $currency,
                'line_items[0][price_data][unit_amount]' => $unitAmount,
                'line_items[0][price_data][product_data][name]' => $description,
                'customer_email' => (string)($payload['customer_email'] ?? ''),
            ]);

            if ($res->successful()) {
                $j = $res->json();
                return ['ok' => true, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Created', 'data' => $j, 'url' => $j['url'] ?? null];
            }

            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => 'Stripe error', 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'create_payment_link', 'message' => $e->getMessage()];
        }
    }
}
