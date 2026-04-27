<?php

namespace Extensions\Connectors\System\Connectors;

use Extensions\Connectors\System\Services\ConnectorStore;
use Illuminate\Support\Facades\Http;

class HubspotConnector
{
    protected string $provider = 'hubspot';

    protected function creds(): array
    {
        return ConnectorStore::getAccount($this->provider)['credentials'] ?? [];
    }

    protected function client()
    {
        $token = (string)($this->creds()['access_token'] ?? '');
        return Http::withToken($token)->baseUrl('https://api.hubapi.com');
    }

    public function test(): array
    {
        $c = $this->creds();
        $token = (string)($c['access_token'] ?? '');
        if ($token === '') {
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Missing access_token'];
        }

        try {
            $res = $this->client()->get('/crm/v3/objects/contacts', ['limit' => 1]);
            if ($res->successful()) {
                return ['ok' => true, 'provider' => $this->provider, 'message' => 'Connected', 'data' => $res->json()];
            }
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'HubSpot error', 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'message' => $e->getMessage()];
        }
    }

    public function run(string $action, array $payload): array
    {
        return match ($action) {
            'upsert_contact' => $this->upsertContact($payload),
            default => ['ok' => false, 'provider' => $this->provider, 'action' => $action, 'message' => 'Unknown action'],
        };
    }

    protected function upsertContact(array $payload): array
    {
        $email = trim((string)($payload['email'] ?? ''));
        if ($email === '') {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_contact', 'message' => 'Missing email'];
        }

        $name = trim((string)($payload['name'] ?? ''));
        $phone = trim((string)($payload['phone'] ?? ''));
        $notes = trim((string)($payload['notes'] ?? ''));

        $props = [
            'email' => $email,
        ];
        if ($name !== '') {
            // Simple split
            $parts = preg_split('/\s+/', $name);
            $props['firstname'] = $parts[0] ?? $name;
            if (count($parts) > 1) {
                $props['lastname'] = implode(' ', array_slice($parts, 1));
            }
        }
        if ($phone !== '') {
            $props['phone'] = $phone;
        }
        if ($notes !== '') {
            $props['notes'] = $notes;
        }

        try {
            // Search by email
            $search = $this->client()->post('/crm/v3/objects/contacts/search', [
                'filterGroups' => [[
                    'filters' => [[
                        'propertyName' => 'email',
                        'operator' => 'EQ',
                        'value' => $email,
                    ]],
                ]],
                'properties' => ['email'],
                'limit' => 1,
            ]);

            if ($search->successful()) {
                $found = $search->json('results.0');
                if ($found && isset($found['id'])) {
                    $id = $found['id'];
                    $upd = $this->client()->patch('/crm/v3/objects/contacts/' . $id, ['properties' => $props]);
                    if ($upd->successful()) {
                        return ['ok' => true, 'provider' => $this->provider, 'action' => 'upsert_contact', 'message' => 'Updated', 'data' => $upd->json()];
                    }
                    return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_contact', 'message' => 'Update failed', 'data' => $upd->json()];
                }
            }

            // Create
            $create = $this->client()->post('/crm/v3/objects/contacts', ['properties' => $props]);
            if ($create->successful()) {
                return ['ok' => true, 'provider' => $this->provider, 'action' => 'upsert_contact', 'message' => 'Created', 'data' => $create->json()];
            }
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_contact', 'message' => 'Create failed', 'data' => $create->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'upsert_contact', 'message' => $e->getMessage()];
        }
    }
}
