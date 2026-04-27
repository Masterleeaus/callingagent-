<?php

namespace Modules\TitanChatbot\Filament\Resources;

if (class_exists(\Filament\Resources\Resource::class)) {
    class KnowledgeResource extends \Filament\Resources\Resource
    {
        protected static ?string $model = null;

        protected static ?string $navigationIcon = 'heroicon-o-book-open';

        protected static ?string $navigationLabel = 'Knowledge Base';

        protected static ?string $slug = 'knowledge';

        public static function getModelLabel(): string
        {
            return 'Knowledge Article';
        }

        public static function getModel(): string
        {
            return \Modules\TitanChatbot\Models\KnowledgeArticle::class;
        }

        public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
        {
            return $form->schema([
                \Filament\Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('content')
                    ->required()
                    ->rows(10),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
        }

        public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
        {
            return $table
                ->columns([
                    \Filament\Tables\Columns\TextColumn::make('title')
                        ->searchable()
                        ->sortable(),
                    \Filament\Tables\Columns\IconColumn::make('is_active')
                        ->boolean(),
                    \Filament\Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable(),
                ])
                ->defaultSort('created_at', 'desc')
                ->filters([]);
        }

        public static function getPages(): array
        {
            return [
                'index'  => \Modules\TitanChatbot\Filament\Resources\KnowledgeResource\Pages\ListKnowledge::route('/'),
                'create' => \Modules\TitanChatbot\Filament\Resources\KnowledgeResource\Pages\CreateKnowledge::route('/create'),
                'edit'   => \Modules\TitanChatbot\Filament\Resources\KnowledgeResource\Pages\EditKnowledge::route('/{record}/edit'),
            ];
        }
    }
} else {
    class KnowledgeResource
    {
        public static function getModelLabel(): string
        {
            return 'Knowledge Article';
        }
    }
}
