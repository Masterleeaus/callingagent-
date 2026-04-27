<?php

namespace Modules\TitanChatbot\Filament\Resources\KnowledgeResource\Pages;

if (class_exists(\Filament\Resources\Pages\CreateRecord::class)) {
    class CreateKnowledge extends \Filament\Resources\Pages\CreateRecord
    {
        protected static string $resource = \Modules\TitanChatbot\Filament\Resources\KnowledgeResource::class;
    }
} else {
    class CreateKnowledge {}
}
