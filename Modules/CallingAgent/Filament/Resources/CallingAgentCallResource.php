<?php

namespace Modules\CallingAgent\Filament\Resources;

use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;
use Modules\CallingAgent\Models\CallingAgentCall;
use Modules\CallingAgent\Models\CallingAgentCallOutcome;
use Modules\CallingAgent\Models\CallingAgentTranscript;

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
                        'gray'     => fn ($state) => in_array($state, ['voicemail', 'sdk-unavailable']),
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
                        'completed'  => 'Completed',
                        'failed'     => 'Failed',
                        'voicemail'  => 'Voicemail',
                        'in-progress'=> 'In Progress',
                        'ringing'    => 'Ringing',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('transfer')
                    ->label('Transfer')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('warning')
                    ->visible(fn (CallingAgentCall $record) => in_array($record->status, ['in-progress', 'ringing']))
                    ->form([
                        Forms\Components\TextInput::make('target')
                            ->label('Transfer to (phone or SIP URI)')
                            ->required()
                            ->placeholder('+15005550099'),
                    ])
                    ->action(function (CallingAgentCall $record, array $data) {
                        $callSid = $record->call_sid;
                        $target  = $data['target'];
                        try {
                            $response = Http::withToken('')->post(
                                url("/api/calling-agent/calls/{$callSid}/transfer"),
                                ['target' => $target]
                            );
                            if ($response->successful()) {
                                Notification::make()->title('Transfer initiated')->success()->send();
                            } else {
                                Notification::make()->title('Transfer failed: ' . $response->json('error', 'Unknown'))->danger()->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()->title('Transfer error: ' . $e->getMessage())->danger()->send();
                        }
                    }),
                Action::make('hangup')
                    ->label('Hang Up')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CallingAgentCall $record) => in_array($record->status, ['in-progress', 'ringing']))
                    ->requiresConfirmation()
                    ->action(function (CallingAgentCall $record) {
                        $callSid = $record->call_sid;
                        try {
                            $response = Http::withToken('')->post(
                                url("/api/calling-agent/calls/{$callSid}/hangup")
                            );
                            if ($response->successful()) {
                                Notification::make()->title('Call ended')->success()->send();
                            } else {
                                Notification::make()->title('Hangup failed: ' . $response->json('error', 'Unknown'))->danger()->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()->title('Hangup error: ' . $e->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Filament\Resources\Pages\ListRecords::class,
        ];
    }
}
