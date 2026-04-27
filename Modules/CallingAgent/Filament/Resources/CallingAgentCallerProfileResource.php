<?php

namespace Modules\CallingAgent\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CallingAgent\Models\CallingAgentCallerProfile;

class CallingAgentCallerProfileResource extends Resource
{
    protected static ?string $model = CallingAgentCallerProfile::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Caller Profiles';
    protected static ?string $navigationGroup = 'Calling Agent';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('phone')->tel(),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\TextInput::make('name'),
            Forms\Components\TextInput::make('company'),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('company')->searchable(),
                Tables\Columns\TextColumn::make('call_count')
                    ->label('Calls')
                    ->default(0),
                Tables\Columns\TextColumn::make('last_call_at')->dateTime()->sortable(),
            ])
            ->defaultSort('last_call_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Filament\Resources\Pages\ListRecords::class,
            'edit'  => \Filament\Resources\Pages\EditRecord::class,
        ];
    }
}
