<?php

namespace Extensions\Connectors\System\Services\Connectors;

class ConnectorManager
{
    /** @var array<string, class-string<ConnectorInterface>> */
    protected array $map = [
        'hubspot' => HubspotConnector::class,
        'mailchimp' => MailchimpConnector::class,
        'wordpress' => WordpressConnector::class,
        'stripe' => StripeConnector::class,
        'square' => SquareConnector::class,
    ];

    public function all(): array
    {
        $out = [];
        foreach ($this->map as $provider => $class) {
            $out[$provider] = app($class);
        }
        return $out;
    }

    public function get(string $provider): ?ConnectorInterface
    {
        $provider = strtolower(trim($provider));
        if (!isset($this->map[$provider])) {
            return null;
        }
        return app($this->map[$provider]);
    }
}
