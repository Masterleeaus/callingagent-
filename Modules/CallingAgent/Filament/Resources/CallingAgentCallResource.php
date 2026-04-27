<?php

namespace Modules\CallingAgent\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CallingAgent\Models\CallingAgentCall;

class CallingAgentCallResource extends Resource
{
    protected static ?string $model = CallingAgentCall::class;
    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $navigationLabel = 'Calls';
    protected static ?string $navigationGroup = 'Calling Agent';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('call_sid')
                    ->label('Call SID')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('direction')
                    ->colors(['success' => 'inbound', 'warning' => 'outbound']),
                Tables\Columns\TextColumn::make('from')->searchable(),
                Tables\Columns\TextColumn::make('to')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success'  => 'completed',
                        'warning'  => fn ($state) => in_array($state, ['ringing', 'in-progress']),
                        'danger'   => fn ($state) => in_array($state, ['failed', 'busy', 'no-answer']),
                        'gray'     => 'voicemail',
                    ]),
                Tables\Columns\TextColumn::make('duration')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('i:s', (int) $state) : '—')
                    ->label('Duration'),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->options(['inbound' => 'Inbound', 'outbound' => 'Outbound']),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Completed',
                        'failed'    => 'Failed',
                        'voicemail' => 'Voicemail',
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
