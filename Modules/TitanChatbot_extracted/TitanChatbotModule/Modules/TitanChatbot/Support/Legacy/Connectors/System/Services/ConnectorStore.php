<?php

namespace Extensions\Connectors\System\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ConnectorStore
{
    public static function allAccounts(): array
    {
        $rows = DB::table('connector_accounts')->get();
        $out = [];
        foreach ($rows as $row) {
            $out[$row->provider] = [
                'provider' => $row->provider,
                'status' => $row->status,
                'credentials' => self::decryptJson($row->credentials_json),
                'meta' => self::decryptJson($row->meta_json),
            ];
        }
        return $out;
    }

    public static function getAccount(string $provider): array
    {
        $row = DB::table('connector_accounts')->where('provider', $provider)->first();
        if (!$row) {
            return ['provider' => $provider, 'status' => 'disconnected', 'credentials' => [], 'meta' => []];
        }
        return [
            'provider' => $row->provider,
            'status' => $row->status,
            'credentials' => self::decryptJson($row->credentials_json),
            'meta' => self::decryptJson($row->meta_json),
        ];
    }

    public static function saveAccount(string $provider, array $credentials, string $status = 'connected', array $meta = []): void
    {
        $payload = [
            'provider' => $provider,
            'status' => $status,
            'credentials_json' => self::encryptJson($credentials),
            'meta_json' => self::encryptJson($meta),
            'updated_at' => now(),
        ];

        $exists = DB::table('connector_accounts')->where('provider', $provider)->exists();
        if ($exists) {
            DB::table('connector_accounts')->where('provider', $provider)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('connector_accounts')->insert($payload);
        }
    }

    public static function setStatus(string $provider, string $status): void
    {
        DB::table('connector_accounts')->where('provider', $provider)->update(['status' => $status, 'updated_at' => now()]);
    }

    public static function getSetting(string $key, $default = '')
    {
        if (function_exists('setting')) {
            return setting($key) ?? $default;
        }
        return config($key, $default);
    }

    protected static function encryptJson($data): ?string
    {
        if ($data === null) {
            return null;
        }
        $json = json_encode($data);
        if ($json === false) {
            $json = '[]';
        }
        try {
            return Crypt::encryptString($json);
        } catch (\Throwable $e) {
            return $json; // fallback
        }
    }

    protected static function decryptJson($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        $json = $value;
        try {
            // If encrypted, this will work; if plain, it will throw.
            $json = Crypt::decryptString($value);
        } catch (\Throwable $e) {
            // ignore
        }
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }
}
