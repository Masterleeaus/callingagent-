<?php

namespace Modules\TitanChatbot\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\DTOs\MessagePayload;

class MessagePayloadTest extends TestCase
{
    public function test_from_array_creates_payload(): void
    {
        $payload = MessagePayload::fromArray([
            'chatbot_id' => 1,
            'session_id' => 'abc123',
            'channel'    => 'webchat',
            'message'    => 'Hello',
        ]);

        $this->assertEquals(1, $payload->chatbotId);
        $this->assertEquals('abc123', $payload->sessionId);
        $this->assertEquals('webchat', $payload->channel);
        $this->assertEquals('Hello', $payload->message);
        $this->assertNull($payload->tenantId);
        $this->assertEquals([], $payload->metadata);
    }

    public function test_to_array_returns_correct_keys(): void
    {
        $payload = new MessagePayload(1, 'sess', 'voice', 'hi', null, 5, ['a' => 'b']);
        $arr     = $payload->toArray();

        $this->assertArrayHasKey('chatbot_id', $arr);
        $this->assertArrayHasKey('session_id', $arr);
        $this->assertArrayHasKey('channel', $arr);
        $this->assertArrayHasKey('message', $arr);
        $this->assertArrayHasKey('tenant_id', $arr);
        $this->assertArrayHasKey('metadata', $arr);
        $this->assertEquals(5, $arr['tenant_id']);
    }

    public function test_from_array_supports_camel_case_keys(): void
    {
        $payload = MessagePayload::fromArray([
            'chatbotId' => 2,
            'sessionId' => 'xyz',
            'channel'   => 'telegram',
            'message'   => 'test',
        ]);

        $this->assertEquals(2, $payload->chatbotId);
        $this->assertEquals('xyz', $payload->sessionId);
    }

    public function test_to_array_round_trips_via_from_array(): void
    {
        $original = MessagePayload::fromArray([
            'chatbot_id' => 99,
            'session_id' => 'round-trip',
            'channel'    => 'whatsapp',
            'message'    => 'test message',
            'tenant_id'  => 7,
            'metadata'   => ['foo' => 'bar'],
        ]);

        $arr       = $original->toArray();
        $recreated = MessagePayload::fromArray($arr);

        $this->assertEquals($original->chatbotId, $recreated->chatbotId);
        $this->assertEquals($original->sessionId, $recreated->sessionId);
        $this->assertEquals($original->channel, $recreated->channel);
        $this->assertEquals($original->message, $recreated->message);
        $this->assertEquals($original->tenantId, $recreated->tenantId);
    }

    public function test_from_array_accepts_optional_tenant_id(): void
    {
        $payload = MessagePayload::fromArray([
            'chatbot_id' => 3,
            'session_id' => 's1',
            'channel'    => 'messenger',
            'message'    => 'hi',
            'tenant_id'  => 42,
        ]);

        $this->assertEquals(42, $payload->tenantId);
    }

    public function test_from_array_accepts_optional_metadata(): void
    {
        $payload = MessagePayload::fromArray([
            'chatbot_id' => 4,
            'session_id' => 's2',
            'channel'    => 'webchat',
            'message'    => 'hello',
            'metadata'   => ['source' => 'widget'],
        ]);

        $this->assertIsArray($payload->metadata);
        $this->assertEquals('widget', $payload->metadata['source']);
    }

    public function test_payload_properties_are_readonly(): void
    {
        $payload = new MessagePayload(10, 'sess', 'voice', 'msg');

        $reflection = new \ReflectionClass($payload);
        $prop       = $reflection->getProperty('chatbotId');

        $this->assertTrue($prop->isReadOnly());
    }

    public function test_to_array_metadata_key_present(): void
    {
        $payload = new MessagePayload(1, 's', 'webchat', 'm', null, null, ['k' => 'v']);
        $arr     = $payload->toArray();

        $this->assertArrayHasKey('metadata', $arr);
        $this->assertEquals(['k' => 'v'], $arr['metadata']);
    }
}
