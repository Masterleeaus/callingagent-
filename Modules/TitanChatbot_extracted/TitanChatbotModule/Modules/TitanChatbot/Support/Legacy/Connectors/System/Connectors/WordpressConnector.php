<?php

namespace Extensions\Connectors\System\Connectors;

use Extensions\Connectors\System\Services\ConnectorStore;
use Illuminate\Support\Facades\Http;

class WordpressConnector
{
    protected string $provider = 'wordpress';

    protected function creds(): array
    {
        return ConnectorStore::getAccount($this->provider)['credentials'] ?? [];
    }

    protected function baseUrl(): string
    {
        $url = rtrim((string)($this->creds()['site_url'] ?? ''), '/');
        return $url;
    }

    protected function client()
    {
        $c = $this->creds();
        $user = (string)($c['username'] ?? '');
        $pass = (string)($c['app_password'] ?? '');
        return Http::withBasicAuth($user, $pass)->baseUrl($this->baseUrl());
    }

    public function test(): array
    {
        $c = $this->creds();
        if (empty($c['site_url']) || empty($c['username']) || empty($c['app_password'])) {
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'Missing site_url/username/app_password'];
        }

        try {
            $res = $this->client()->get('/wp-json/wp/v2/users/me');
            if ($res->successful()) {
                return ['ok' => true, 'provider' => $this->provider, 'message' => 'Connected', 'data' => $res->json()];
            }
            return ['ok' => false, 'provider' => $this->provider, 'message' => 'WordPress error', 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'message' => $e->getMessage()];
        }
    }

    public function run(string $action, array $payload): array
    {
        return match ($action) {
            'publish_post' => $this->publishPost($payload),
            default => ['ok' => false, 'provider' => $this->provider, 'action' => $action, 'message' => 'Unknown action'],
        };
    }

    protected function publishPost(array $payload): array
    {
        $title = (string)($payload['title'] ?? 'New Post');
        $content = (string)($payload['content'] ?? '');
        $status = (string)($payload['status'] ?? 'publish');
        $categories = $payload['categories'] ?? [];
        if (!is_array($categories)) {
            $categories = [];
        }

        try {
            $res = $this->client()->post('/wp-json/wp/v2/posts', [
                'title' => $title,
                'content' => $content,
                'status' => $status,
                'categories' => $categories,
            ]);

            if ($res->successful()) {
                return ['ok' => true, 'provider' => $this->provider, 'action' => 'publish_post', 'message' => 'Published', 'data' => $res->json()];
            }

            return ['ok' => false, 'provider' => $this->provider, 'action' => 'publish_post', 'message' => 'Publish failed', 'data' => $res->json()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'provider' => $this->provider, 'action' => 'publish_post', 'message' => $e->getMessage()];
        }
    }
}
