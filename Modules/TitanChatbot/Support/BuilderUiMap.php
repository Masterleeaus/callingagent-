<?php

namespace Modules\TitanChatbot\Support;

final class BuilderUiMap
{
    public const VIEW_NAMESPACE = 'titan-chatbot';

    public static function tabs(): array
    {
        return [
            'configure' => 'index',
            'customize' => 'customization.index',
            'train' => 'training.index',
            'embed' => 'embed.index',
            'channel' => 'channel.index',
            'frontend' => 'frontend-ui.frontend-ui',
        ];
    }
}
