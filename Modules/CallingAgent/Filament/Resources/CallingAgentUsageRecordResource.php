<?php

namespace Modules\CallingAgent\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CallingAgent\Models\CallingAgentUsageRecord;

class CallingAgentUsageRecordResource extends Resource
{
    protected static ?string $model = CallingAgentUsageRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Usage';
    protected static ?string $navigationGroup = 'Calling Agent';
    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('meter')->searchable(),
                Tables\Columns\TextColumn::make('quantity')->sortable(),
                Tables\Columns\TextColumn::make('unit'),
                Tables\Columns\TextColumn::make('calling_agent_id')
                    ->label('Agent ID'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('meter')
                    ->options([
                        'voice_seconds'   => 'Voice Seconds',
                        'sms_messages'    => 'SMS Messages',
                        'whatsapp_messages' => 'WhatsApp Messages',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Filament\Resources\Pages\ListRecords::class,
        ];
    }
}
