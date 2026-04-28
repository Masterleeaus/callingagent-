<?php

namespace Modules\TitanChatbot\Filament\Resources\KnowledgeResource\Pages;

if (class_exists(\Filament\Resources\Pages\EditRecord::class)) {
    class EditKnowledgeArticle extends \Filament\Resources\Pages\EditRecord
    {
        protected static string $resource = \Modules\TitanChatbot\Filament\Resources\KnowledgeResource::class;
    }
} else {
    class EditKnowledgeArticle {}
}
