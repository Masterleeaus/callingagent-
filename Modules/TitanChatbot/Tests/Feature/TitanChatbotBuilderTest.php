<?php

namespace Modules\TitanChatbot\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\DTOs\MessagePayload;

class TitanChatbotBuilderTest extends TestCase
{
    public function test_message_payload_round_trips_correctly(): void
    {
        $payload = MessagePayload::fromArray([
            'chatbot_id' => 99,
            'session_id' => 'test-session',
            'channel'    => 'webchat',
            'message'    => 'Tell me about your services',
            'tenant_id'  => 7,
        ]);

        $this->assertEquals(99, $payload->chatbotId);
        $this->assertEquals('test-session', $payload->sessionId);
        $this->assertEquals('webchat', $payload->channel);
        $this->assertEquals(7, $payload->tenantId);
    }

    public function test_payload_to_array_has_all_keys(): void
    {
        $payload = MessagePayload::fromArray([
            'chatbot_id' => 1,
            'session_id' => 'sess',
            'channel'    => 'voice',
            'message'    => 'hello',
            'tenant_id'  => 3,
            'metadata'   => ['origin' => 'builder'],
        ]);

        $arr = $payload->toArray();

        $this->assertArrayHasKey('chatbot_id', $arr);
        $this->assertArrayHasKey('session_id', $arr);
        $this->assertArrayHasKey('channel', $arr);
        $this->assertArrayHasKey('message', $arr);
        $this->assertArrayHasKey('tenant_id', $arr);
        $this->assertArrayHasKey('metadata', $arr);
        $this->assertEquals('builder', $arr['metadata']['origin']);
    }

    public function test_payload_channel_value_is_preserved(): void
    {
        foreach (['webchat', 'whatsapp', 'telegram', 'messenger', 'voice'] as $channel) {
            $payload = MessagePayload::fromArray([
                'chatbot_id' => 1,
                'session_id' => 'x',
                'channel'    => $channel,
                'message'    => 'test',
            ]);
            $this->assertEquals($channel, $payload->channel);
        }
    }

    public function test_state_store_class_exists(): void
    {
        $this->assertTrue(class_exists(\Modules\TitanChatbot\Services\ConversationStateStore::class));
    }
}
