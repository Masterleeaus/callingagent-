<?php

namespace Extensions\Connectors\System\Services\Connectors;

use Extensions\Connectors\System\Models\ConnectorAccount;

class HubspotConnector implements ConnectorInterface
{
    public function provider(): string { return 'hubspot'; }
    public function label(): string { return 'HubSpot'; }
    public function credentialFields(): array { return ['private_app_token']; }

    public function test(ConnectorAccount $account): ConnectorResult
    {
        $creds = $account->getCredentials();
        $token = trim($creds['private_app_token'] ?? '');
        if ($token === '') {
            return ConnectorResult::error('Missing HubSpot private app token.');
        }

        $client = HttpClient::client(['base_uri' => 'https://api.hubapi.com']);
        $resp = HttpClient::request($client, 'GET', '/account-info/v3/details', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);

        if (($resp['status'] ?? 0) === 200) {
            return ConnectorResult::success('Connected', $resp['json'] ?? []);
        }

        return ConnectorResult::error('HubSpot test failed', $resp);
    }

    public function actions(): array
    {
        return [
            'upsert_contact' => ['label' => 'Upsert Contact', 'fields' => ['email','first_name','last_name','phone','company']]
        ];
    }

    public function run(ConnectorAccount $account, string $action, array $payload): ConnectorResult
    {
        if ($action !== 'upsert_contact') {
            return ConnectorResult::error('Unknown action');
        }
        $email = strtolower(trim($payload['email'] ?? ''));
        if ($email === '') {
            return ConnectorResult::error('Email is required');
        }

        $creds = $account->getCredentials();
        $token = trim($creds['private_app_token'] ?? '');
        if ($token === '') {
            return ConnectorResult::error('Missing HubSpot private app token.');
        }

        $client = HttpClient::client(['base_uri' => 'https://api.hubapi.com']);
        $headers = ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'];

        // Search for existing contact by email
        $searchBody = [
            'filterGroups' => [[
                'filters' => [[
                    'propertyName' => 'email',
                    'operator' => 'EQ',
                    'value' => $email,
                ]]
            ]],
            'properties' => ['email'],
            'limit' => 1,
        ];
        $search = HttpClient::request($client, 'POST', '/crm/v3/objects/contacts/search', [
            'headers' => $headers,
            'body' => json_encode($searchBody),
        ]);

        $contactId = null;
        if (($search['status'] ?? 0) === 200) {
            $results = $search['json']['results'] ?? [];
            if (!empty($results[0]['id'])) {
                $contactId = $results[0]['id'];
            }
        }

        $properties = array_filter([
            'email' => $email,
            'firstname' => $payload['first_name'] ?? null,
            'lastname' => $payload['last_name'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'company' => $payload['company'] ?? null,
        ], fn($v) => $v !== null && $v !== '');

        if ($contactId) {
            $update = HttpClient::request($client, 'PATCH', '/crm/v3/objects/contacts/' . $contactId, [
                'headers' => $headers,
                'body' => json_encode(['properties' => $properties]),
            ]);
            if (($update['status'] ?? 0) === 200) {
                return ConnectorResult::success('Contact updated', $update['json'] ?? []);
            }
            return ConnectorResult::error('HubSpot update failed', $update);
        }

        $create = HttpClient::request($client, 'POST', '/crm/v3/objects/contacts', [
            'headers' => $headers,
            'body' => json_encode(['properties' => $properties]),
        ]);
        if (($create['status'] ?? 0) === 201) {
            return ConnectorResult::success('Contact created', $create['json'] ?? []);
        }
        return ConnectorResult::error('HubSpot create failed', $create);
    }
}
