<?php

namespace Modules\TitanChatbot\Filament\Resources\ConversationResource\Pages;

if (class_exists(\Filament\Resources\Pages\ViewRecord::class)) {
    class ViewConversation extends \Filament\Resources\Pages\ViewRecord
    {
        protected static string $resource = \Modules\TitanChatbot\Filament\Resources\ConversationResource::class;
    }
} else {
    class ViewConversation {}
}
