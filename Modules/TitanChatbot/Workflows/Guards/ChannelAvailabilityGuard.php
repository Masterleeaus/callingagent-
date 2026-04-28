<?php

namespace Modules\TitanChatbot\Workflows\Guards;

class ChannelAvailabilityGuard
{
    public function passes(array $context): bool
    {
        $channel = $context['channel'] ?? null;

        if ($channel === null) {
            return false;
        }

        $channelConfig = $context['channel_config'] ?? [];

        if (isset($channelConfig['enabled']) && $channelConfig['enabled'] === false) {
            return false;
        }

        return true;
    }
}
