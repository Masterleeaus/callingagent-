<?php
namespace Modules\TitanChatbot\Tests\Unit;
use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\Billing\Usage\UsageRecord;

class UsageRecordTest extends TestCase
{
    public function test_from_array_creates_record()
    {
        $record = UsageRecord::fromArray([
            'provider'          => 'openai',
            'model'             => 'gpt-4o-mini',
            'prompt_tokens'     => 100,
            'completion_tokens' => 50,
            'total_tokens'      => 150,
            'tenant_id'         => 7,
            'chatbot_id'        => 42,
        ]);

        $this->assertSame('openai', $record->provider);
        $this->assertSame('gpt-4o-mini', $record->model);
        $this->assertSame(100, $record->promptTokens);
        $this->assertSame(50, $record->completionTokens);
        $this->assertSame(150, $record->totalTokens);
        $this->assertSame(7, $record->tenantId);
    }

    public function test_normalize_fills_total_tokens()
    {
        $record = UsageRecord::fromArray([
            'prompt_tokens'     => 80,
            'completion_tokens' => 40,
            'total_tokens'      => 0,
        ]);

        $normalized = $record->normalize();
        $this->assertSame(120, $normalized->totalTokens);
    }

    public function test_normalize_fills_recorded_at()
    {
        $record = (new UsageRecord())->normalize();
        $this->assertNotEmpty($record->recordedAt);
    }

    public function test_to_array_has_all_keys()
    {
        $record = new UsageRecord(provider: 'openai', model: 'gpt-4o', tenantId: 1);
        $arr    = $record->toArray();

        $this->assertArrayHasKey('provider', $arr);
        $this->assertArrayHasKey('model', $arr);
        $this->assertArrayHasKey('prompt_tokens', $arr);
        $this->assertArrayHasKey('completion_tokens', $arr);
        $this->assertArrayHasKey('total_tokens', $arr);
        $this->assertArrayHasKey('tenant_id', $arr);
        $this->assertArrayHasKey('chatbot_id', $arr);
        $this->assertArrayHasKey('recorded_at', $arr);
    }

    public function test_normalize_keeps_existing_total()
    {
        $record     = UsageRecord::fromArray(['prompt_tokens' => 10, 'completion_tokens' => 20, 'total_tokens' => 999]);
        $normalized = $record->normalize();
        $this->assertSame(999, $normalized->totalTokens);
    }
}
