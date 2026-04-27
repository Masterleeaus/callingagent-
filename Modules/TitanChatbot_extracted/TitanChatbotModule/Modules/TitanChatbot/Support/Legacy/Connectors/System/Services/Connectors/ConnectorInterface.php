<?php

namespace Extensions\Connectors\System\Services\Connectors;

use Extensions\Connectors\System\Models\ConnectorAccount;

interface ConnectorInterface
{
    public function provider(): string;

    public function label(): string;

    /** Return keys expected in saveProvider */
    public function credentialFields(): array;

    public function test(ConnectorAccount $account): ConnectorResult;

    public function actions(): array;

    public function run(ConnectorAccount $account, string $action, array $payload): ConnectorResult;
}
