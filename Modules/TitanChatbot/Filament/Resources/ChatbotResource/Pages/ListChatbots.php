<?php

namespace Modules\TitanChatbot\Filament\Resources\ChatbotResource\Pages;

if (class_exists(\Filament\Resources\Pages\ListRecords::class)) {
    class ListChatbots extends \Filament\Resources\Pages\ListRecords
    {
        protected static string $resource = \Modules\TitanChatbot\Filament\Resources\ChatbotResource::class;
    }
} else {
    class ListChatbots {}
}
