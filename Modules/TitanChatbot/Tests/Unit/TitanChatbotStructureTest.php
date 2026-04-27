<?php

namespace Modules\TitanChatbot\Tests\Unit;

use PHPUnit\Framework\TestCase;

class TitanChatbotStructureTest extends TestCase
{
    /** @dataProvider requiredClassProvider */
    public function test_required_class_exists(string $class): void
    {
        $this->assertTrue(class_exists($class), "Class {$class} does not exist");
    }

    public static function requiredClassProvider(): array
    {
        return [
            ['Modules\TitanChatbot\DTOs\MessagePayload'],
            ['Modules\TitanChatbot\Services\ConversationRouter'],
            ['Modules\TitanChatbot\Services\GeneratorBridge'],
            ['Modules\TitanChatbot\Services\ChannelRouter'],
            ['Modules\TitanChatbot\Services\WebchatChannel'],
            ['Modules\TitanChatbot\Services\TrainingPipeline'],
            ['Modules\TitanChatbot\Billing\Meters\VoiceSecondsMeter'],
            ['Modules\TitanChatbot\Billing\Meters\ConversationMeter'],
            ['Modules\TitanChatbot\Billing\Meters\EmbeddingMeter'],
            ['Modules\TitanChatbot\AI\Agents\ConversationAgent'],
            ['Modules\TitanChatbot\AI\Agents\VoiceAgent'],
            ['Modules\TitanChatbot\AI\Pipelines\RagPipeline'],
            ['Modules\TitanChatbot\Models\Chatbot'],
            ['Modules\TitanChatbot\Models\Conversation'],
            ['Modules\TitanChatbot\Models\ChatbotHistory'],
            ['Modules\TitanChatbot\Models\ChatbotEmbedding'],
            ['Modules\TitanChatbot\Providers\ModuleServiceProvider'],
        ];
    }

    /** @dataProvider requiredInterfaceProvider */
    public function test_channel_drivers_implement_interface(string $class): void
    {
        $this->assertTrue(class_exists($class), "Class {$class} does not exist");
        $this->assertTrue(
            in_array(
                \Modules\TitanChatbot\Contracts\ChannelDriver::class,
                class_implements($class) ?: []
            ),
            "{$class} must implement ChannelDriver"
        );
    }

    public static function requiredInterfaceProvider(): array
    {
        return [
            ['Modules\TitanChatbot\Services\WebchatChannel'],
            ['Modules\TitanChatbot\Services\WhatsappChannel'],
            ['Modules\TitanChatbot\Services\TelegramChannel'],
            ['Modules\TitanChatbot\Services\MessengerChannel'],
            ['Modules\TitanChatbot\Services\VoiceChannel'],
        ];
    }
}
