<?php
namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $set('slug', \Illuminate\Support\Str::slug($state));
                                    }),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Auto-generated from name'),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->options([
                                // Content Management
                                'view_posts' => 'View Posts',
                                'create_posts' => 'Create Posts',
                                'edit_posts' => 'Edit Posts',
                                'delete_posts' => 'Delete Posts',
                                'publish_posts' => 'Publish Posts',

                                // Comments
                                'view_comments' => 'View Comments',
                                'create_comments' => 'Create Comments',
                                'edit_comments' => 'Edit Comments',
                                'delete_comments' => 'Delete Comments',
                                'moderate_comments' => 'Moderate Comments',

                                // Categories & Tags
                                'manage_categories' => 'Manage Categories',
                                'manage_tags' => 'Manage Tags',

                                // Users
                                'view_users' => 'View Users',
                                'create_users' => 'Create Users',
                                'edit_users' => 'Edit Users',
                                'delete_users' => 'Delete Users',

                                // Roles & Permissions
                                'manage_roles' => 'Manage Roles',

                                // Settings
                                'manage_settings' => 'Manage Settings',

                                // Analytics
                                'view_analytics' => 'View Analytics',

                                // Subscriptions
                                'view_subscriptions' => 'View Subscriptions',
                                'manage_subscriptions' => 'Manage Subscriptions',

                                // Admin Access
                                'access_admin' => 'Access Admin Panel',
                                'access_filament' => 'Access Filament',
                            ])
                            ->columns(3)
                            ->gridDirection('row')
                            ->columnSpanFull()
                            ->searchable()
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->slug),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('permissions')
                    ->label('Permissions')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Role')
                    ->modalDescription('Are you sure you want to delete this role? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it')
                    ->action(function (Role $record) {
                        if ($record->users()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Cannot delete role')
                                ->body('This role is assigned to ' . $record->users()->count() . ' users. Please reassign them first.')
                                ->send();
                            return false;
                        }
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Role Details')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('slug')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('users_count')
                                    ->label('Total Users')
                                    ->badge()
                                    ->color('primary'),
                            ]),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Permissions')
                    ->schema([
                        Infolists\Components\TextEntry::make('permissions')
                            ->badge()
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state) => is_array($state) ? $state : [])
                            ->placeholder('No permissions assigned'),
                    ]),

                Infolists\Components\Section::make('Users with this Role')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('users')
                            ->schema([
                                Infolists\Components\TextEntry::make('name'),
                                Infolists\Components\TextEntry::make('email'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
