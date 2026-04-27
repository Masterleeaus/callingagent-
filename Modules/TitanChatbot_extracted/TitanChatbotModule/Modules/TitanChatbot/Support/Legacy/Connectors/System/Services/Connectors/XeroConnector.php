<?php

namespace Extensions\Connectors\System\Services\Connectors;

use Extensions\Connectors\System\Models\ConnectorAccount;

class XeroConnector implements ConnectorInterface
{
    public function provider(): string { return 'xero'; }

    public function label(): string { return 'Xero'; }

    public function credentialFields(): array
    {
        // OAuth is handled outside this MVP. Keep empty so the UI does not ask for secrets here.
        return [];
    }

    public function test(ConnectorAccount $account): ConnectorResult
    {
        if (!class_exists('Dcblogdev\Xero\Facades\Xero')) {
            return ConnectorResult::error('Dcblogdev Xero package not found. Install/configure it in the host app to enable Xero.');
        }

        try {
            $xero = \Dcblogdev\Xero\Facades\Xero::getFacadeRoot();
            if (method_exists($xero, 'isConnected')) {
                $ok = \Dcblogdev\Xero\Facades\Xero::isConnected();
                return $ok ? ConnectorResult::success('Connected') : ConnectorResult::error('Not connected');
            }
            return ConnectorResult::success('Xero facade available (connection status unknown)');
        } catch (\Throwable $e) {
            return ConnectorResult::error('Xero test failed: ' . $e->getMessage());
        }
    }

    public function actions(): array
    {
        return [
            'create_invoice_draft' => 'Create draft invoice (requires full Xero wiring)',
        ];
    }

    public function run(ConnectorAccount $account, string $action, array $payload = []): ConnectorResult
    {
        return ConnectorResult::error('Xero action is not implemented in this build. Enable full OAuth + tenant selection first.');
    }
}
