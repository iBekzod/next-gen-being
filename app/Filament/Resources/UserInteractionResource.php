<?php
namespace App\Filament\Resources;

use App\Filament\Resources\UserInteractionResource\Pages;
use App\Models\UserInteraction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserInteractionResource extends Resource
{
    protected static ?string $model = UserInteraction::class;
    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-ripple';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'User Interactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Interaction Details')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'like' => 'Like',
                                        'bookmark' => 'Bookmark',
                                        'view' => 'View',
                                        'share' => 'Share',
                                        'click' => 'Click',
                                    ])
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('interactable_type')
                                    ->label('Content Type')
                                    ->placeholder('App\Models\Post')
                                    ->required(),
                                Forms\Components\TextInput::make('interactable_id')
                                    ->label('Content ID')
                                    ->numeric()
                                    ->required(),
                            ]),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->user ? route('filament.admin.resources.users.view', $record->user) : null),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'danger' => 'like',
                        'warning' => 'bookmark',
                        'success' => 'view',
                        'primary' => 'share',
                        'secondary' => 'click',
                    ])
                    ->icons([
                        'heroicon-o-heart' => 'like',
                        'heroicon-o-bookmark' => 'bookmark',
                        'heroicon-o-eye' => 'view',
                        'heroicon-o-share' => 'share',
                        'heroicon-o-cursor-arrow-rays' => 'click',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('interactable_type')
                    ->label('Content Type')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('interactable_id')
                    ->label('Content ID')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('interactable.title')
                    ->label('Content')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('M d, Y H:i')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'like' => 'Like',
                        'bookmark' => 'Bookmark',
                        'view' => 'View',
                        'share' => 'Share',
                        'click' => 'Click',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('interactable_type')
                    ->label('Content Type')
                    ->options([
                        'App\\Models\\Post' => 'Post',
                        'App\\Models\\Comment' => 'Comment',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('60s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserInteractions::route('/'),
            'create' => Pages\CreateUserInteraction::route('/create'),
            'edit' => Pages\EditUserInteraction::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
