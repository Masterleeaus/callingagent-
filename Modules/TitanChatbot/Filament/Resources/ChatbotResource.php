<?php

namespace Modules\TitanChatbot\Filament\Resources;

use Modules\TitanChatbot\Models\Chatbot;

if (class_exists(\Filament\Resources\Resource::class)) {
    class ChatbotResource extends \Filament\Resources\Resource
    {
        protected static ?string $model = Chatbot::class;

        protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

        protected static ?string $navigationLabel = 'Chatbots';

        public static function getModelLabel(): string
        {
            return 'Chatbot';
        }

        public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
        {
            return $form->schema([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('model')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
        }

        public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
        {
            return $table
                ->columns([
                    \Filament\Tables\Columns\TextColumn::make('name')
                        ->searchable()
                        ->sortable(),
                    \Filament\Tables\Columns\TextColumn::make('model')
                        ->searchable(),
                    \Filament\Tables\Columns\IconColumn::make('is_active')
                        ->boolean(),
                    \Filament\Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable(),
                ])
                ->filters([]);
        }

        public static function getPages(): array
        {
            return [
                'index'  => \Modules\TitanChatbot\Filament\Resources\ChatbotResource\Pages\ListChatbots::route('/'),
                'create' => \Modules\TitanChatbot\Filament\Resources\ChatbotResource\Pages\CreateChatbot::route('/create'),
                'edit'   => \Modules\TitanChatbot\Filament\Resources\ChatbotResource\Pages\EditChatbot::route('/{record}/edit'),
            ];
        }
    }
} else {
    class ChatbotResource
    {
        public static function getModelLabel(): string
        {
            return 'Chatbot';
        }
    }
}
