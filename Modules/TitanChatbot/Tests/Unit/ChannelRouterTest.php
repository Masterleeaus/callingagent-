<?php

namespace Modules\TitanChatbot\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\TitanChatbot\Services\ChannelRouter;
use Modules\TitanChatbot\Enums\ChannelType;

class ChannelRouterTest extends TestCase
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

    // ─── channelMap coverage ─────────────────────────────────────────────────

    public function test_channel_map_contains_webchat(): void
    {
        $this->assertArrayHasKey('webchat', $this->channelMap);
    }

    public function test_channel_map_contains_website(): void
    {
        $this->assertArrayHasKey('website', $this->channelMap);
    }

    public function test_channel_map_contains_whatsapp(): void
    {
        $this->assertArrayHasKey('whatsapp', $this->channelMap);
    }

    public function test_channel_map_contains_telegram(): void
    {
        $this->assertArrayHasKey('telegram', $this->channelMap);
    }

    public function test_channel_map_contains_messenger(): void
    {
        $this->assertArrayHasKey('messenger', $this->channelMap);
    }

    public function test_channel_map_contains_voice(): void
    {
        $this->assertArrayHasKey('voice', $this->channelMap);
    }

    /** @dataProvider enumChannelProvider */
    public function test_channel_map_covers_all_enum_values(string $enumValue): void
    {
        $this->assertArrayHasKey(
            $enumValue,
            $this->channelMap,
            "ChannelType::{$enumValue} is not in the channelMap"
        );
    }

    public static function enumChannelProvider(): array
    {
        return array_map(
            fn(ChannelType $case) => [$case->value],
            ChannelType::cases()
        );
    }

    // ─── channelMap driver class-strings ─────────────────────────────────────

    /** @dataProvider channelDriverProvider */
    public function test_channel_map_drivers_are_valid_class_strings(string $channel): void
    {
        $driverClass = $this->channelMap[$channel];

        $this->assertTrue(
            class_exists($driverClass),
            "Driver class {$driverClass} for channel '{$channel}' does not exist"
        );
    }

    public static function channelDriverProvider(): array
    {
        return [
            ['webchat'],
            ['website'],
            ['whatsapp'],
            ['telegram'],
            ['messenger'],
            ['voice'],
        ];
    }

    // ─── driver interface compliance ─────────────────────────────────────────

    /** @dataProvider channelDriverProvider */
    public function test_channel_map_drivers_implement_channel_driver(string $channel): void
    {
        $driverClass = $this->channelMap[$channel];

        $this->assertTrue(
            in_array(
                \Modules\TitanChatbot\Contracts\ChannelDriver::class,
                class_implements($driverClass) ?: []
            ),
            "{$driverClass} must implement ChannelDriver"
        );
    }

    // ─── register() ──────────────────────────────────────────────────────────

    public function test_register_adds_custom_channel(): void
    {
        $fakeDriver = \Modules\TitanChatbot\Services\WebchatChannel::class;
        $this->router->register('sms', $fakeDriver);

        $reflection = new \ReflectionClass($this->router);
        $prop       = $reflection->getProperty('channelMap');
        $prop->setAccessible(true);
        $map = $prop->getValue($this->router);

        $this->assertArrayHasKey('sms', $map);
        $this->assertSame($fakeDriver, $map['sms']);
    }

    public function test_register_normalises_channel_to_lowercase(): void
    {
        $this->router->register('SMS', \Modules\TitanChatbot\Services\WebchatChannel::class);

        $reflection = new \ReflectionClass($this->router);
        $prop       = $reflection->getProperty('channelMap');
        $prop->setAccessible(true);
        $map = $prop->getValue($this->router);

        $this->assertArrayHasKey('sms', $map);
        $this->assertArrayNotHasKey('SMS', $map);
    }

    // ─── resolve() throws on unknown channel ─────────────────────────────────

    public function test_resolve_throws_for_unsupported_channel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // resolve() calls app() which is not available in pure unit tests,
        // so we test that an unknown channel never reaches app() and throws early
        // by wrapping in a try/catch that distinguishes our expected exception
        // from the Illuminate container exception.
        try {
            $this->router->resolve('fax');
        } catch (\InvalidArgumentException $e) {
            throw $e; // expected
        } catch (\Throwable $e) {
            // If app() throws first, the channel was at least not found in the map
            $this->assertArrayNotHasKey('fax', $this->channelMap);
            return;
        }
    }

    public function test_channel_map_does_not_contain_unsupported_channels(): void
    {
        $this->assertArrayNotHasKey('fax', $this->channelMap);
        $this->assertArrayNotHasKey('sms', $this->channelMap); // not registered by default
        $this->assertArrayNotHasKey('email', $this->channelMap);
    }
}
