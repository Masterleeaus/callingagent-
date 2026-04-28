<?php

namespace Modules\TitanChatbot\Services;

use Modules\TitanChatbot\Contracts\ChannelDriver;
use Modules\TitanChatbot\Enums\ChannelType;
use InvalidArgumentException;

class ChannelRouter
{
    /** @var array<string, class-string<ChannelDriver>> */
    private array $channelMap = [
        'webchat'  => WebchatChannel::class,
        'website'  => WebchatChannel::class,
        'whatsapp' => WhatsappChannel::class,
        'telegram' => TelegramChannel::class,
        'messenger'=> MessengerChannel::class,
        'voice'    => VoiceChannel::class,
    ];

    public function route(string $channel, array $payload): string
    {
        $driver = $this->resolve($channel);

        return $driver->handle($payload);
    }

    public function resolve(string $channel): ChannelDriver
    {
        $normalised = strtolower(trim($channel));

        if (isset($this->channelMap[$normalised])) {
            return app($this->channelMap[$normalised]);
        }

        // Try matching against ChannelType enum values
        foreach (ChannelType::cases() as $case) {
            if ($case->value === $normalised) {
                $key = match ($case) {
                    ChannelType::Website   => 'website',
                    ChannelType::Whatsapp  => 'whatsapp',
                    ChannelType::Telegram  => 'telegram',
                    ChannelType::Messenger => 'messenger',
                    ChannelType::Voice     => 'voice',
                };

                return app($this->channelMap[$key]);
            }
        }

        throw new InvalidArgumentException("Unsupported channel: {$channel}");
    }

    public function register(string $channel, string $driverClass): void
    {
        $this->channelMap[strtolower($channel)] = $driverClass;
    }
}
