<?php

namespace Extensions\Connectors\System\Services\Connectors;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    public static function client(array $options = []): Client
    {
        return new Client(array_merge([
            'timeout' => 20,
            'http_errors' => false,
        ], $options));
    }

    public static function request($clientOrMethod, ?string $method = null, ?string $url = null, array $options = []): array
    {
        // Supports two call styles:
        // 1) request($client, 'GET', '/path', [...])
        // 2) request('GET', 'https://...', [...])
        if ($clientOrMethod instanceof Client) {
            $client = $clientOrMethod;
            $resp = $client->request($method ?? 'GET', $url ?? '/', $options);
        } else {
            $method = (string)$clientOrMethod;
            $url = (string)$method;
            $url = (string)($url ?? '');
            $resp = self::client()->request($method, $url, $options);
        }

        return self::normalize($resp);
    }

    public static function normalize(ResponseInterface $resp): array
    {
        $body = (string)$resp->getBody();
        $json = json_decode($body, true);
        return [
            'status' => $resp->getStatusCode(),
            'body' => $body,
            'json' => is_array($json) ? $json : null,
            'headers' => $resp->getHeaders(),
        ];
    }
}
