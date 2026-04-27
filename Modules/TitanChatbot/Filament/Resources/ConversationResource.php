<?php

namespace Modules\TitanChatbot\Filament\Resources;

use Modules\TitanChatbot\Models\Conversation;

if (class_exists(\Filament\Resources\Resource::class)) {
    class ConversationResource extends \Filament\Resources\Resource
    {
        protected static ?string $model = Conversation::class;

        protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left';

        protected static ?string $navigationLabel = 'Conversations';

        public static function getModelLabel(): string
        {
            return 'Conversation';
        }

        public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
        {
            return $form->schema([
                \Filament\Forms\Components\TextInput::make('session_id')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('channel')
                    ->maxLength(100),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
        }

        public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
        {
            return $table
                ->columns([
                    \Filament\Tables\Columns\TextColumn::make('session_id')
                        ->searchable()
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('channel')
                        ->searchable(),
                    \Filament\Tables\Columns\TextColumn::make('chatbot.name')
                        ->label('Chatbot')
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('started_at')
                        ->dateTime()
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('ended_at')
                        ->dateTime()
                        ->sortable(),
                ])
                ->defaultSort('started_at', 'desc')
                ->filters([]);
        }

        public static function getPages(): array
        {
            return [
                'index' => \Modules\TitanChatbot\Filament\Resources\ConversationResource\Pages\ListConversations::route('/'),
                'view'  => \Modules\TitanChatbot\Filament\Resources\ConversationResource\Pages\ViewConversation::route('/{record}'),
            ];
        }
    }
} else {
    class ConversationResource
    {
        public static function getModelLabel(): string
        {
            return 'Conversation';
        }
    }
}
