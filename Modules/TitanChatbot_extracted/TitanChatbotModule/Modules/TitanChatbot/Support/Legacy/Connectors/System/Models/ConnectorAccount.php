<?php

namespace Extensions\Connectors\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ConnectorAccount extends Model
{
    protected $table = 'connector_accounts';

    protected $fillable = [
        'tenant_id', 'user_id', 'provider', 'status', 'credentials_json', 'meta_json', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function setCredentials(array $creds): void
    {
        $this->credentials_json = Crypt::encryptString(json_encode($creds));
    }

    public function getCredentials(): array
    {
        if (empty($this->credentials_json)) {
            return [];
        }
        try {
            $json = Crypt::decryptString($this->credentials_json);
            return json_decode($json, true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function setMeta(array $meta): void
    {
        $this->meta_json = json_encode($meta);
    }

    public function getMeta(): array
    {
        return json_decode($this->meta_json ?? '[]', true) ?: [];
    }
}
