<?php

namespace Extensions\Connectors\System\Services;

use Extensions\Connectors\System\Connectors\HubspotConnector;
use Extensions\Connectors\System\Connectors\MailchimpConnector;
use Extensions\Connectors\System\Connectors\StripeConnector;
use Extensions\Connectors\System\Connectors\SquareConnector;
use Extensions\Connectors\System\Connectors\WordpressConnector;
use Illuminate\Support\Facades\DB;

class ConnectorRunner
{
    protected function adapter(string $provider)
    {
        return match ($provider) {
            'hubspot' => new HubspotConnector(),
            'mailchimp' => new MailchimpConnector(),
            'wordpress' => new WordpressConnector(),
            'stripe' => new StripeConnector(),
            'square' => new SquareConnector(),
            default => null,
        };
    }

    public function test(string $provider): array
    {
        $adapter = $this->adapter($provider);
        if (!$adapter) {
            return ['ok' => false, 'provider' => $provider, 'message' => 'Unknown provider'];
        }
        return $adapter->test();
    }

    public function runClientPack(array $input): array
    {
        $email = trim((string)($input['email'] ?? ''));
        $name = trim((string)($input['name'] ?? ''));
        $phone = trim((string)($input['phone'] ?? ''));
        $notes = trim((string)($input['notes'] ?? ''));
        $templateId = $input['template_id'] ?? null;
        $payments_provider = (string)($input['payments_provider'] ?? 'stripe');

        $template = $this->loadTemplate($templateId);

        $results = [];

        // 1) HubSpot upsert
        $results[] = (new HubspotConnector())->run('upsert_contact', [
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'notes' => $notes,
        ]);

        // 2) Mailchimp upsert + tags
        $results[] = (new MailchimpConnector())->run('upsert_member', [
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'tags' => $template['mailchimp_tags'] ?? [],
        ]);

        // 3) WordPress publish post
        $results[] = (new WordpressConnector())->run('publish_post', [
            'title' => $template['wp_title'] ?? ('Job update: ' . ($name ?: $email)),
            'content' => $this->renderTemplateText($template['wp_content'] ?? "New job update for {{name}} ({{email}}).\n\n{{notes}}", [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'notes' => $notes,
            ]),
            'status' => $template['wp_status'] ?? 'publish',
            'categories' => $template['wp_categories'] ?? [],
        ]);

        // 4) Payments link (Stripe or Square)
        if (in_array($payments_provider, ['stripe', 'square'], true)) {
            $adapter = $this->adapter($payments_provider);
            if ($adapter) {
                $results[] = $adapter->run('create_payment_link', [
                    'amount' => (float)($input['amount'] ?? ($template['amount'] ?? 0)),
                    'currency' => (string)($input['currency'] ?? ($template['currency'] ?? 'AUD')),
                    'description' => (string)($input['payment_description'] ?? ($template['payment_description'] ?? 'Deposit / Payment')),
                    'customer_email' => $email,
                ]);
            }
        }

        return [
            'ok' => true,
            'client_pack' => [
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'template' => $template,
            ],
            'results' => $results,
        ];
    }

    protected function loadTemplate($templateId): array
    {
        $row = null;
        if ($templateId) {
            $row = DB::table('connector_client_pack_templates')->where('id', $templateId)->first();
        }
        if (!$row) {
            $row = DB::table('connector_client_pack_templates')->where('is_default', true)->first();
        }
        if (!$row) {
            // fallback defaults
            return [
                'mailchimp_tags' => ['Lead'],
                'wp_title' => 'New Job Lead',
                'wp_content' => "New lead: {{name}} ({{email}})\nPhone: {{phone}}\n\nNotes:\n{{notes}}",
                'wp_status' => 'publish',
                'wp_categories' => [],
                'amount' => 0,
                'currency' => 'AUD',
                'payment_description' => 'Deposit / Payment',
            ];
        }

        $tpl = json_decode((string)$row->template_json, true);
        return is_array($tpl) ? $tpl : [];
    }

    protected function renderTemplateText(string $text, array $vars): string
    {
        foreach ($vars as $k => $v) {
            $text = str_replace('{{' . $k . '}}', (string)$v, $text);
        }
        return $text;
    }
}
