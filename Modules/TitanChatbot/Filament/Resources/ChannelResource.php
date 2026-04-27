<?php

namespace Modules\TitanChatbot\Filament\Resources;

if (class_exists(\Filament\Resources\Resource::class)) {
    class ChannelResource extends \Filament\Resources\Resource
    {
        protected static ?string $model = null;

        protected static ?string $navigationIcon = 'heroicon-o-signal';

        protected static ?string $navigationLabel = 'Channels';

        protected static ?string $slug = 'channels';

        public static function getModelLabel(): string
        {
            return 'Channel';
        }

        public static function getModel(): string
        {
            return \Modules\TitanChatbot\Models\ChatbotChannel::class;
        }

        public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
        {
            return $form->schema([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Select::make('type')
                    ->options([
                        'webchat'   => 'Webchat',
                        'whatsapp'  => 'WhatsApp',
                        'telegram'  => 'Telegram',
                        'messenger' => 'Messenger',
                        'voice'     => 'Voice',
                    ])
                    ->required(),
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
                    \Filament\Tables\Columns\TextColumn::make('type')
                        ->badge()
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
                'index'  => \Modules\TitanChatbot\Filament\Resources\ChannelResource\Pages\ListChannels::route('/'),
                'create' => \Modules\TitanChatbot\Filament\Resources\ChannelResource\Pages\CreateChannel::route('/create'),
                'edit'   => \Modules\TitanChatbot\Filament\Resources\ChannelResource\Pages\EditChannel::route('/{record}/edit'),
            ];
        }
    }
} else {
    class ChannelResource
    {
        public static function getModelLabel(): string
        {
            return 'Channel';
        }
    }
}
