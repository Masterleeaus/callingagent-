<?php

namespace Extensions\Connectors\System\Services\Connectors;

use Extensions\Connectors\System\Models\ConnectorAccount;

class WordpressConnector implements ConnectorInterface
{
    public function provider(): string { return 'wordpress'; }
    public function label(): string { return 'WordPress'; }
    public function credentialFields(): array { return ['site_url','username','app_password']; }

    public function test(ConnectorAccount $account): ConnectorResult
    {
        $creds = $account->getCredentials();
        $site = rtrim((string)($creds['site_url'] ?? ''), '/');
        $user = (string)($creds['username'] ?? '');
        $pass = (string)($creds['app_password'] ?? '');
        if (!$site || !$user || !$pass) {
            return ConnectorResult::error('Missing site_url/username/app_password');
        }

        $resp = HttpClient::request('GET', $site . '/wp-json/wp/v2/users/me', [
            'auth' => [$user, $pass],
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (($resp['status'] ?? 0) >= 200 && ($resp['status'] ?? 0) < 300) {
            $account->status = 'connected';
            $account->save();
            return ConnectorResult::success('Connected', ['user' => $resp['json']]);
        }

        return ConnectorResult::error('Auth failed', ['status' => $resp['status'], 'body' => $resp['body']]);
    }

    public function actions(): array
    {
        return [
            'publish_post' => [
                'label' => 'Publish Post',
                'fields' => ['title','content','status','categories'],
            ],
        ];
    }

    public function runAction(ConnectorAccount $account, string $action, array $payload): ConnectorResult
    {
        if ($action !== 'publish_post') {
            return ConnectorResult::error('Unknown action');
        }
        $creds = $account->getCredentials();
        $site = rtrim((string)($creds['site_url'] ?? ''), '/');
        $user = (string)($creds['username'] ?? '');
        $pass = (string)($creds['app_password'] ?? '');
        if (!$site || !$user || !$pass) {
            return ConnectorResult::error('Missing credentials');
        }

        $body = [
            'title' => (string)($payload['title'] ?? ''),
            'content' => (string)($payload['content'] ?? ''),
            'status' => (string)($payload['status'] ?? 'publish'),
        ];
        if (!empty($payload['categories'])) {
            $body['categories'] = is_array($payload['categories']) ? $payload['categories'] : [(int)$payload['categories']];
        }

        $resp = HttpClient::request('POST', $site . '/wp-json/wp/v2/posts', [
            'auth' => [$user, $pass],
            'headers' => ['Accept' => 'application/json'],
            'json' => $body,
        ]);

        if (($resp['status'] ?? 0) >= 200 && ($resp['status'] ?? 0) < 300) {
            return ConnectorResult::success('Post published', ['post' => $resp['json']]);
        }

        return ConnectorResult::error('Publish failed', ['status' => $resp['status'], 'body' => $resp['body']]);
    }
}
