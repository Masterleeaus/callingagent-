<?php

namespace Modules\TitanChatbot\Filament\Resources\ChatbotResource\Pages;

if (class_exists(\Filament\Resources\Pages\CreateRecord::class)) {
    class CreateChatbot extends \Filament\Resources\Pages\CreateRecord
    {
        protected static string $resource = \Modules\TitanChatbot\Filament\Resources\ChatbotResource::class;
    }
} else {
    class CreateChatbot {}
}
