<?php

namespace Extensions\Connectors\System\Services\Connectors;

use Extensions\Connectors\System\Models\ConnectorAccount;

class MailchimpConnector implements ConnectorInterface
{
    public function provider(): string { return 'mailchimp'; }
    public function label(): string { return 'Mailchimp'; }
    public function credentialFields(): array { return ['api_key', 'audience_id']; }

    protected function baseUrl(string $apiKey): ?string
    {
        if (!str_contains($apiKey, '-')) {
            return null;
        }
        $parts = explode('-', $apiKey);
        $dc = end($parts);
        return 'https://' . $dc . '.api.mailchimp.com/3.0';
    }

    public function test(ConnectorAccount $account): ConnectorResult
    {
        $creds = $account->getCredentials();
        $key = trim($creds['api_key'] ?? '');
        if ($key === '') {
            return ConnectorResult::error('Missing api_key');
        }
        $base = $this->baseUrl($key);
        if (!$base) {
            return ConnectorResult::error('Invalid Mailchimp API key (missing -usX suffix)');
        }

        $client = HttpClient::client(['base_uri' => $base]);
        $resp = $client->get('/ping', [
            'auth' => ['anystring', $key],
        ]);
        $code = $resp->getStatusCode();
        if ($code >= 200 && $code < 300) {
            return ConnectorResult::success('Mailchimp connected');
        }
        return ConnectorResult::error('Mailchimp test failed', ['http' => $code, 'body' => (string)$resp->getBody()]);
    }

    public function actions(): array
    {
        return [
            'upsert_subscriber' => 'Add/Update subscriber',
        ];
    }

    public function run(ConnectorAccount $account, string $action, array $payload): ConnectorResult
    {
        return match ($action) {
            'upsert_subscriber' => $this->upsertSubscriber($account, $payload),
            default => ConnectorResult::error('Unknown action'),
        };
    }

    protected function upsertSubscriber(ConnectorAccount $account, array $payload): ConnectorResult
    {
        $creds = $account->getCredentials();
        $key = trim($creds['api_key'] ?? '');
        $audience = trim($creds['audience_id'] ?? '');
        if ($key === '' || $audience === '') {
            return ConnectorResult::error('Missing api_key or audience_id');
        }
        $base = $this->baseUrl($key);
        if (!$base) {
            return ConnectorResult::error('Invalid Mailchimp API key');
        }
        $email = strtolower(trim($payload['email'] ?? ''));
        if ($email === '') {
            return ConnectorResult::error('Missing email');
        }
        $hash = md5($email);

        $body = [
            'email_address' => $email,
            'status_if_new' => $payload['status_if_new'] ?? 'subscribed',
            'status' => $payload['status'] ?? null,
            'merge_fields' => $payload['merge_fields'] ?? [],
        ];
        if (!empty($payload['tags']) && is_array($payload['tags'])) {
            // Mailchimp tags are managed via a separate endpoint; we will apply after upsert.
        }

        $client = HttpClient::client(['base_uri' => $base]);
        $resp = $client->put("/lists/{$audience}/members/{$hash}", [
            'auth' => ['anystring', $key],
            'json' => $body,
        ]);
        $code = $resp->getStatusCode();
        $data = json_decode((string)$resp->getBody(), true) ?: [];
        if ($code >= 200 && $code < 300) {
            // Apply tags if supplied
            if (!empty($payload['tags']) && is_array($payload['tags'])) {
                $tagsResp = $client->post("/lists/{$audience}/members/{$hash}/tags", [
                    'auth' => ['anystring', $key],
                    'json' => [
                        'tags' => array_map(fn($t) => ['name' => (string)$t, 'status' => 'active'], $payload['tags']),
                    ],
                ]);
                $tagsCode = $tagsResp->getStatusCode();
                if (!($tagsCode >= 200 && $tagsCode < 300)) {
                    return ConnectorResult::success('Subscriber upserted (tags failed)', ['member' => $data, 'tags_http' => $tagsCode]);
                }
            }
            return ConnectorResult::success('Subscriber upserted', ['member' => $data]);
        }
        return ConnectorResult::error('Upsert failed', ['http' => $code, 'body' => $data]);
    }
}
