<?php

namespace Modules\TitanChatbot\Filament\Resources\ChannelResource\Pages;

if (class_exists(\Filament\Resources\Pages\ListRecords::class)) {
    class ListChannels extends \Filament\Resources\Pages\ListRecords
    {
        protected static string $resource = \Modules\TitanChatbot\Filament\Resources\ChannelResource::class;
    }
} else {
    class ListChannels {}
}
