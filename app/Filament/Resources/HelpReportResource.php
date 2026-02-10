<?php
namespace App\Filament\Resources;

use App\Filament\Resources\HelpReportResource\Pages;
use App\Models\HelpReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class HelpReportResource extends Resource
{
    protected static ?string $model = HelpReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?string $navigationLabel = 'Help & Reports';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Report Details')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'help' => 'Help Request',
                                'report' => 'Report Issue',
                                'bug' => 'Bug Report',
                                'feature_request' => 'Feature Request',
                            ])
                            ->required()
                            ->default('help'),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'normal' => 'Normal',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('normal')
                            ->required(),

                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(6)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Assignment & Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->default('open')
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Reported By')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->label('Assigned To')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Resolution Date'),
                    ])->columns(2),

                Forms\Components\Section::make('Technical Information')
                    ->schema([
                        Forms\Components\Textarea::make('metadata')
                            ->label('Additional Data (JSON)')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->collapsible()->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'help' => 'info',
                        'report' => 'warning',
                        'bug' => 'danger',
                        'feature_request' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'help' => 'heroicon-m-question-mark-circle',
                        'report' => 'heroicon-m-flag',
                        'bug' => 'heroicon-m-bug-ant',
                        'feature_request' => 'heroicon-m-light-bulb',
                        default => 'heroicon-m-document',
                    }),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->limit(60),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'normal' => 'primary',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'gray',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'secondary',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'open' => 'heroicon-m-clock',
                        'in_progress' => 'heroicon-m-play',
                        'resolved' => 'heroicon-m-check-circle',
                        'closed' => 'heroicon-m-archive-box',
                        default => 'heroicon-m-document',
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Reporter')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Reported'),

                Tables\Columns\TextColumn::make('resolved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'help' => 'Help Request',
                        'report' => 'Report Issue',
                        'bug' => 'Bug Report',
                        'feature_request' => 'Feature Request',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'normal' => 'Normal',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                Tables\Filters\Filter::make('urgent')
                    ->label('Urgent Only')
                    ->query(fn (Builder $query): Builder => $query->where('priority', 'urgent')),

                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned')
                    ->query(fn (Builder $query): Builder => $query->whereNull('assigned_to')),

                Tables\Filters\Filter::make('my_assignments')
                    ->label('My Assignments')
                    ->query(fn (Builder $query): Builder => $query->where('assigned_to', auth()->id())),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('assign_to_me')
                        ->icon('heroicon-m-user-plus')
                        ->color('info')
                        ->action(fn ($record) => $record->update(['assigned_to' => auth()->id()]))
                        ->visible(fn ($record) => !$record->assigned_to),

                    Tables\Actions\Action::make('mark_in_progress')
                        ->icon('heroicon-m-play')
                        ->color('warning')
                        ->action(fn ($record) => $record->update(['status' => 'in_progress']))
                        ->visible(fn ($record) => $record->status === 'open'),

                    Tables\Actions\Action::make('mark_resolved')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'resolved',
                                'resolved_at' => now(),
                            ]);
                        })
                        ->visible(fn ($record) => in_array($record->status, ['open', 'in_progress'])),

                    Tables\Actions\Action::make('close')
                        ->icon('heroicon-m-archive-box')
                        ->color('secondary')
                        ->action(fn ($record) => $record->update(['status' => 'closed']))
                        ->visible(fn ($record) => $record->status === 'resolved')
                        ->requiresConfirmation(),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assign_to_me')
                        ->label('Assign to Me')
                        ->icon('heroicon-m-user-plus')
                        ->color('info')
                        ->action(fn ($records) => $records->each->update(['assigned_to' => auth()->id()]))
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('mark_resolved')
                        ->label('Mark as Resolved')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update([
                                'status' => 'resolved',
                                'resolved_at' => now(),
                            ]);
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            return static::getModel()::whereIn('status', ['open', 'in_progress'])->count() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        try {
            $urgentCount = static::getModel()::where('priority', 'urgent')
                ->whereIn('status', ['open', 'in_progress'])
                ->count();

            return $urgentCount > 0 ? 'danger' : 'warning';
        } catch (\Exception $e) {
            return 'warning';
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHelpReports::route('/'),
            'create' => Pages\CreateHelpReport::route('/create'),
            // 'view' => Pages\ViewHelpReport::route('/{record}'),
            'edit' => Pages\EditHelpReport::route('/{record}/edit'),
        ];
    }
}
