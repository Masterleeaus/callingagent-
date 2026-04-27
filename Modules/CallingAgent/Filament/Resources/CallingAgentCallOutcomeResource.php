<?php

namespace Modules\CallingAgent\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CallingAgent\Models\CallingAgentCallOutcome;

class CallingAgentCallOutcomeResource extends Resource
{
    protected static ?string $model = CallingAgentCallOutcome::class;
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'Call Outcomes';
    protected static ?string $navigationGroup = 'Calling Agent';
    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('call_sid')
                    ->label('Call SID')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('intent')
                    ->colors([
                        'success' => 'booking',
                        'warning' => 'sales',
                        'info'    => 'support',
                        'danger'  => 'retention',
                    ]),
                Tables\Columns\BadgeColumn::make('urgency')
                    ->colors(['danger' => 'urgent', 'warning' => 'high', 'gray' => 'normal']),
                Tables\Columns\BadgeColumn::make('sentiment')
                    ->colors(['success' => 'positive', 'danger' => 'negative', 'gray' => 'neutral']),
                Tables\Columns\IconColumn::make('handoff_required')->boolean(),
                Tables\Columns\IconColumn::make('booking_requested')->boolean(),
                Tables\Columns\TextColumn::make('summary')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record?->summary),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('intent')
                    ->options([
                        'booking'   => 'Booking',
                        'sales'     => 'Sales',
                        'support'   => 'Support',
                        'retention' => 'Retention',
                        'general'   => 'General',
                    ]),
                Tables\Filters\SelectFilter::make('urgency')
                    ->options(['urgent' => 'Urgent', 'high' => 'High', 'normal' => 'Normal']),
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
