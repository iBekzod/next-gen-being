<?php
namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true),
                            ]),
                        Forms\Components\Textarea::make('bio')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Social Links')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('website')
                                    ->url()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('twitter')
                                    ->maxLength(255)
                                    ->prefix('@'),
                                Forms\Components\TextInput::make('linkedin')
                                    ->maxLength(255),
                            ]),
                    ]),

                Forms\Components\Section::make('Roles & Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->options(Role::pluck('name', 'id'))
                            ->descriptions(Role::pluck('description', 'id')->toArray())
                            ->columns(2),
                    ]),

                Forms\Components\Section::make('Profile Image')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                            ])
                            ->directory('avatars')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'content_manager',
                        'success' => 'lead',
                        'primary' => 'blogger',
                    ]),
                Tables\Columns\TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\Filter::make('has_subscription')
                    ->query(fn (Builder $query): Builder => $query->whereHas('subscriptions'))
                    ->label('Premium Users'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->visible(fn () => auth()->user()->hasRole('admin'))
                    ->action(fn ($record) => redirect()->route('impersonate', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Profile')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\Group::make([
                                    Infolists\Components\ImageEntry::make('avatar')
                                        ->circular()
                                        ->size(100),
                                ]),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('name'),
                                    Infolists\Components\TextEntry::make('email')
                                        ->copyable(),
                                    Infolists\Components\IconEntry::make('is_active')
                                        ->boolean(),
                                ]),
                                Infolists\Components\Group::make([
                                    Infolists\Components\TextEntry::make('created_at')
                                        ->dateTime(),
                                    Infolists\Components\TextEntry::make('last_seen_at')
                                        ->dateTime()
                                        ->since(),
                                ]),
                            ]),
                        Infolists\Components\TextEntry::make('bio')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('posts_count')
                                    ->label('Total Posts'),
                                Infolists\Components\TextEntry::make('comments_count')
                                    ->label('Total Comments'),
                                Infolists\Components\TextEntry::make('likes_received')
                                    ->label('Likes Received')
                                    ->state(fn ($record) => $record->posts->sum('likes_count')),
                                Infolists\Components\TextEntry::make('views_received')
                                    ->label('Total Views')
                                    ->state(fn ($record) => $record->posts->sum('views_count')),
                            ]),
                    ]),

                Infolists\Components\Section::make('Roles & Permissions')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('roles')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('description'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
