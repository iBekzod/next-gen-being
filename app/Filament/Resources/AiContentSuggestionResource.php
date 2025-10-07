<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AiContentSuggestionResource\Pages;
use App\Models\AiContentSuggestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class AiContentSuggestionResource extends Resource
{
    protected static ?string $model = AiContentSuggestion::class;
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'AI Suggestions';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Content Suggestion')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('source_url')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/source'),

                        Forms\Components\TextInput::make('relevance_score')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(1)
                            ->default(0.00)
                            ->helperText('Score from 0.00 to 1.00'),
                    ])->columns(2),

                Forms\Components\Section::make('Topics & Keywords')
                    ->schema([
                        Forms\Components\TagsInput::make('topics')
                            ->placeholder('Add topics...')
                            ->helperText('Press Enter to add each topic'),

                        Forms\Components\TagsInput::make('keywords')
                            ->placeholder('Add keywords...')
                            ->helperText('Press Enter to add each keyword'),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Review')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'used' => 'Used in Content',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\Select::make('suggested_by')
                            ->relationship('suggestedBy', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('reviewed_by')
                            ->relationship('reviewedBy', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Review Date'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->limit(50),

                Tables\Columns\TextColumn::make('description')
                    ->limit(100)
                    ->tooltip(function ($record) {
                        return $record->description;
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'used',
                    ])
                    ->icons([
                        'heroicon-m-clock' => 'pending',
                        'heroicon-m-check-circle' => 'approved',
                        'heroicon-m-x-circle' => 'rejected',
                        'heroicon-m-star' => 'used',
                    ]),

                Tables\Columns\TextColumn::make('relevance_score')
                    ->label('Score')
                    ->badge()
                    ->color(fn ($state) => $state >= 0.8 ? 'success' : ($state >= 0.6 ? 'warning' : 'danger'))
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),

                Tables\Columns\TextColumn::make('topics')
                    ->badge()
                    ->separator(',')
                    ->limit(3)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('suggestedBy.name')
                    ->label('Suggested By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reviewedBy.name')
                    ->label('Reviewed By')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'used' => 'Used',
                    ]),

                Tables\Filters\Filter::make('high_score')
                    ->label('High Relevance (≥0.8)')
                    ->query(fn (Builder $query): Builder => $query->where('relevance_score', '>=', 0.8)),

                Tables\Filters\Filter::make('needs_review')
                    ->label('Needs Review')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending')),

                Tables\Filters\SelectFilter::make('suggested_by')
                    ->relationship('suggestedBy', 'name')
                    ->label('Suggested By'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'approved',
                                'reviewed_by' => auth()->id(),
                                'reviewed_at' => now(),
                            ]);
                        })
                        ->visible(fn ($record) => $record->status === 'pending'),

                    Tables\Actions\Action::make('reject')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'rejected',
                                'reviewed_by' => auth()->id(),
                                'reviewed_at' => now(),
                            ]);
                        })
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === 'pending'),

                    Tables\Actions\Action::make('mark_used')
                        ->icon('heroicon-m-star')
                        ->color('info')
                        ->action(function ($record) {
                            $record->update(['status' => 'used']);
                        })
                        ->visible(fn ($record) => $record->status === 'approved'),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                $record->update([
                                    'status' => 'approved',
                                    'reviewed_by' => auth()->id(),
                                    'reviewed_at' => now(),
                                ]);
                            });
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAiContentSuggestions::route('/'),
            'create' => Pages\CreateAiContentSuggestion::route('/create'),
            'view' => Pages\ViewAiContentSuggestion::route('/{record}'),
            'edit' => Pages\EditAiContentSuggestion::route('/{record}/edit'),
        ];
    }
}
