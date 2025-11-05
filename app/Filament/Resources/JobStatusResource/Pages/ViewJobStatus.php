<?php

namespace App\Filament\Resources\JobStatusResource\Pages;

use App\Filament\Resources\JobStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewJobStatus extends ViewRecord
{
    protected static string $resource = JobStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('retry')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn ($record) => $record->isFailed())
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'pending',
                        'error_message' => null,
                        'started_at' => null,
                        'completed_at' => null,
                    ]);

                    $this->notify('success', 'Job reset and ready to retry');
                }),

            Actions\DeleteAction::make()
                ->label('Delete Job'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Job Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('job_id')
                            ->label('Job ID')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('type')
                            ->badge(),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'processing' => 'info',
                                'completed' => 'success',
                                'failed' => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('queue')
                            ->badge(),

                        Infolists\Components\TextEntry::make('progress')
                            ->label('Progress')
                            ->suffix('%'),

                        Infolists\Components\TextEntry::make('attempts'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Progress')
                    ->schema([
                        Infolists\Components\TextEntry::make('progress_message')
                            ->label('Current Status')
                            ->placeholder('No status message'),
                    ])
                    ->visible(fn ($record) => !empty($record->progress_message)),

                Infolists\Components\Section::make('Error Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('Error Message')
                            ->color('danger')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->isFailed()),

                Infolists\Components\Section::make('Related Resource')
                    ->schema([
                        Infolists\Components\TextEntry::make('trackable_type')
                            ->label('Resource Type')
                            ->formatStateUsing(fn ($state) => class_basename($state)),

                        Infolists\Components\TextEntry::make('trackable_id')
                            ->label('Resource ID'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('User'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('metadata')
                            ->label('Additional Data')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => !empty($record->metadata)),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Queued At')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Started At')
                            ->dateTime()
                            ->placeholder('Not started yet'),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Completed At')
                            ->dateTime()
                            ->placeholder('Not completed yet'),

                        Infolists\Components\TextEntry::make('duration')
                            ->label('Duration')
                            ->state(fn ($record) => $record->getFormattedDuration() ?? 'N/A'),
                    ])
                    ->columns(4),
            ]);
    }
}
