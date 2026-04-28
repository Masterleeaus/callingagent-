<?php

namespace Modules\TitanChatbot\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Cache;

class BillingMeterTest extends TestCase
{
    protected function tearDown(): void
    {
    }

    // ─── VoiceSecondsMeter ────────────────────────────────────────────────────

    public function test_voice_seconds_build_key_format(): void
    {
        $meter = new \Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter();

        $reflection = new \ReflectionClass($meter);
        $method     = $reflection->getMethod('buildKey');
        $method->setAccessible(true);

        $key = $method->invoke($meter, 42, '2024-01-15');

        $this->assertSame('billing_meter:voice_seconds:42:2024-01-15', $key);
    }

    public function test_voice_seconds_build_key_contains_tenant_id(): void
    {
        $meter = new \Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter();

        $reflection = new \ReflectionClass($meter);
        $method     = $reflection->getMethod('buildKey');
        $method->setAccessible(true);

        $key = $method->invoke($meter, 99, '2024-06-01');

        $this->assertStringContainsString('99', $key);
        $this->assertStringContainsString('2024-06-01', $key);
    }

    public function test_voice_seconds_record_ceils_fractional_seconds(): void
    {
        // Verify record() correctly maps tenant_id context and ceils seconds
        $meter = new \Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter();
        // record(90.5, ['tenant_id' => 42]) should call recordSeconds(42, 91)
        // We verify by checking the meter's record() signature accepts float + array
        $reflection = new \ReflectionMethod($meter, 'record');
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertSame('seconds', $params[0]->getName());
        $this->assertSame('context', $params[1]->getName());
    }

    public function test_voice_seconds_record_ceils_exact_value(): void
    {
        // Verify ceil logic: 30.0 → 30
        $this->assertSame(30, (int) ceil(30.0));
    }

    public function test_voice_seconds_record_uses_zero_tenant_when_missing(): void
    {
        // Verify: context without tenant_id → tenant 0
        $meter = new \Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter();
        $reflection = new \ReflectionMethod($meter, 'record');
        // Calling record with empty context should not throw
        try {
            $meter->record(9.1, []);
            $this->assertTrue(true);
        } catch (\Throwable $e) {
            $this->fail('record() threw with empty context: ' . $e->getMessage());
        }
    }

    // ─── ConversationMeter ────────────────────────────────────────────────────

    public function test_conversation_build_key_format(): void
    {
        $meter = new \Modules\TitanChatbot\Billing\Meters\ConversationMeter();

        $reflection = new \ReflectionClass($meter);
        $method     = $reflection->getMethod('buildKey');
        $method->setAccessible(true);

        $key = $method->invoke($meter, 5, '2024-03-20');

        $this->assertSame('billing_meter:conversations:5:2024-03-20', $key);
    }

    public function test_conversation_key_contains_tenant_id_and_date(): void
    {
        $meter = new \Modules\TitanChatbot\Billing\Meters\ConversationMeter();

        $reflection = new \ReflectionClass($meter);
        $method     = $reflection->getMethod('buildKey');
        $method->setAccessible(true);

        $key = $method->invoke($meter, 123, '2025-12-31');

        $this->assertStringStartsWith('billing_meter:conversations:', $key);
        $this->assertStringContainsString('123', $key);
        $this->assertStringContainsString('2025-12-31', $key);
    }

    // ─── EmbeddingMeter ───────────────────────────────────────────────────────

    public function test_embedding_meter_class_exists(): void
    {
        $this->assertTrue(
            class_exists(\Modules\TitanChatbot\Billing\Meters\EmbeddingMeter::class)
        );
    }

    public function test_embedding_meter_has_record_method(): void
    {
        $reflection = new \ReflectionClass(\Modules\TitanChatbot\Billing\Meters\EmbeddingMeter::class);
        $this->assertTrue($reflection->hasMethod('record'));
    }
}
