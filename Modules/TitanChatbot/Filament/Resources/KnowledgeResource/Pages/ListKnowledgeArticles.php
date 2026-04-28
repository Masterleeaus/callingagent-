<?php

namespace Modules\TitanChatbot\Filament\Resources\KnowledgeResource\Pages;

if (class_exists(\Filament\Resources\Pages\ListRecords::class)) {
    class ListKnowledgeArticles extends \Filament\Resources\Pages\ListRecords
    {
        protected static string $resource = \Modules\TitanChatbot\Filament\Resources\KnowledgeResource::class;
    }
} else {
    class ListKnowledgeArticles {}
}
