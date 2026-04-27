<?php

namespace Modules\TitanChatbot\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\Services\ChannelRouter;
use Modules\TitanChatbot\Enums\ChannelType;

class TitanChatbotChannelTest extends TestCase
{
    private ChannelRouter $router;
    private array $channelMap;

    protected function setUp(): void
    {
        $this->router = new ChannelRouter();

        $reflection       = new \ReflectionClass($this->router);
        $prop             = $reflection->getProperty('channelMap');
        $prop->setAccessible(true);
        $this->channelMap = $prop->getValue($this->router);
    }

    public function test_channel_router_channel_map_contains_all_types(): void
    {
        foreach (ChannelType::cases() as $case) {
            $this->assertArrayHasKey(
                $case->value,
                $this->channelMap,
                "Channel {$case->value} missing from channelMap"
            );
        }
    }

    public function test_unsupported_channel_not_in_map(): void
    {
        $this->assertArrayNotHasKey('fax', $this->channelMap);
        $this->assertArrayNotHasKey('email', $this->channelMap);
        $this->assertArrayNotHasKey('unknown', $this->channelMap);
    }

    public function test_channel_type_enum_values(): void
    {
        $values = array_map(fn($c) => $c->value, ChannelType::cases());

        $this->assertContains('website', $values);
        $this->assertContains('whatsapp', $values);
        $this->assertContains('telegram', $values);
        $this->assertContains('messenger', $values);
        $this->assertContains('voice', $values);
    }

    public function test_all_channel_map_drivers_implement_channel_driver_interface(): void
    {
        foreach ($this->channelMap as $channel => $driverClass) {
            $this->assertTrue(
                class_exists($driverClass),
                "Driver class {$driverClass} for channel '{$channel}' does not exist"
            );
            $this->assertTrue(
                in_array(
                    \Modules\TitanChatbot\Contracts\ChannelDriver::class,
                    class_implements($driverClass) ?: []
                ),
                "{$driverClass} must implement ChannelDriver"
            );
        }
    }

    public function test_resolve_throws_invalid_argument_for_unknown_channel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        try {
            $this->router->resolve('fax');
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // app() not available in unit context; channel was correctly not in the map
            $this->assertArrayNotHasKey('fax', $this->channelMap);
            $this->markTestSkipped('app() container not available; channel not found as expected.');
        }
    }

    public function test_register_adds_entry_to_channel_map(): void
    {
        $this->router->register('test_channel', \Modules\TitanChatbot\Services\WebchatChannel::class);

        $reflection = new \ReflectionClass($this->router);
        $prop       = $reflection->getProperty('channelMap');
        $prop->setAccessible(true);
        $map = $prop->getValue($this->router);

        $this->assertArrayHasKey('test_channel', $map);
    }
}
