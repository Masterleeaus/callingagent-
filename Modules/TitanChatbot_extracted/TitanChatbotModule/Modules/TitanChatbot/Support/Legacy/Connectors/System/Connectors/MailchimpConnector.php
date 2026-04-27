<?php

namespace Extensions\Connectors\System\Connectors;

use Extensions\Connectors\System\Services\ConnectorStore;
use Illuminate\Support\Facades\Http;

class MailchimpConnector
{
    protected string $provider = 'mailchimp';

    protected function creds(): array
    {
        return ConnectorStore::getAccount($this->provider)['credentials'] ?? [];
    }

    protected function baseUrl(): string
    {
        $key = (string)($this->creds()['api_key'] ?? '');
        $dc = '';
        if (str_contains($key, '-')) {
            $dc = substr($key, strrpos($key, '-') + 1);
        }
        return $dc ? "https://{$dc}.api.mailchimp.com/3.0" : 'https://api.mailchimp.com/3.0';
    }

    protected function client()
    {
        $key = (string)($this->creds()['api_key'] ?? '');
        return Http::withBasicAuth('anystring', $key)->baseUrl($this->baseUrl());
    }

    public function test(): array
    {
        $c = $this->creds();
        $key = (string)($c['api_key'] ?? '');
        $audience = (string)($c['audience_id'] ?? '');
        if ($key === '' || $audience === '') {
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Missing api_key or audience_id'];
        }

        try {
            $res = $this->client()->get('/lists/' . $audience);
            if ($res->successful()) {
                return ['ok' => true, 'provider' => $this->provider, 'message' => 'Connected', 'data' => $res->json()];
            }
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Mailchimp error', 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'message' => $e->getMessage()];
        }
    }

    public function run(string $action, array $payload): array
    {
        return match ($action) {
            'upsert_member' => $this->upsertMember($payload),
            default => ['ok' => false, 'provider' => $this->provider, 'action' => $action, 'message' => 'Unknown action'],
        };
    }

    protected function upsertMember(array $payload): array
    {
        $c = $this->creds();
        $audience = (string)($c['audience_id'] ?? '');
        if ($audience === '') {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_member', 'message' => 'Missing audience_id'];
        }
        $email = strtolower(trim((string)($payload['email'] ?? '')));
        if ($email === '') {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_member', 'message' => 'Missing email'];
        }

        $name = trim((string)($payload['name'] ?? ''));
        $phone = trim((string)($payload['phone'] ?? ''));
        $tags = $payload['tags'] ?? [];
        if (!is_array($tags)) {
            $tags = [];
        }

        $hash = md5($email);

        try {
            $put = $this->client()->put('/lists/' . $audience . '/members/' . $hash, [
                'email_address' => $email,
                'status_if_new' => 'subscribed',
                'merge_fields' => [
                    'FNAME' => $name,
                    'PHONE' => $phone,
                ],
            ]);

            if (!$put->successful()) {
                return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_member', 'message' => 'Upsert failed', 'data' => $put->json()];
            }

            $tagRes = null;
            if (count($tags) > 0) {
                $tagRes = $this->client()->post('/lists/' . $audience . '/members/' . $hash . '/tags', [
                    'tags' => array_map(fn($t) => ['name' => (string)$t, 'status' => 'active'], $tags),
                ]);
            }

            return [
                'ok' => true,
                'provider' => $this->provider,
                'action' => 'upsert_member',
                'message' => 'Upserted',
                'data' => [
                    'member' => $put->json(),
                    'tags' => $tagRes ? $tagRes->json() : null,
                ],
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_member', 'message' => $e->getMessage()];
        }
    }
}
