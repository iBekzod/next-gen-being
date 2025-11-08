<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialMediaAccountResource\Pages;
use App\Models\SocialMediaAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialMediaAccountResource extends Resource
{
    protected static ?string $model = SocialMediaAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Social Media';

    protected static ?string $navigationGroup = 'Distribution';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('platform')
                    ->label('Platform')
                    ->options([
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter / X',
                        'linkedin' => 'LinkedIn',
                        'telegram' => 'Telegram',
                    ])
                    ->required()
                    ->disabled(),

                Forms\Components\TextInput::make('platform_username')
                    ->label('Username')
                    ->disabled(),

                Forms\Components\Select::make('account_type')
                    ->options([
                        'personal' => 'Personal',
                        'business' => 'Business',
                        'official' => 'Official (NextGen Being)',
                    ])
                    ->default('personal')
                    ->required(),

                Forms\Components\Toggle::make('auto_publish')
                    ->label('Auto-publish new videos')
                    ->helperText('Automatically publish generated videos to this account')
                    ->default(false),

                Forms\Components\KeyValue::make('publish_schedule')
                    ->label('Publishing Schedule')
                    ->helperText('Define when to auto-publish (optional)')
                    ->keyLabel('Day')
                    ->valueLabel('Time')
                    ->nullable(),

                Forms\Components\Placeholder::make('token_expires_at')
                    ->label('Token Expires')
                    ->content(fn ($record) => $record?->token_expires_at?->diffForHumans() ?? 'Never'),

                Forms\Components\Placeholder::make('created_at')
                    ->label('Connected At')
                    ->content(fn ($record) => $record?->created_at?->diffForHumans()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->label('Platform')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter / X',
                        'linkedin' => 'LinkedIn',
                        'telegram' => 'Telegram',
                        default => ucfirst($state),
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'youtube' => 'danger',
                        'instagram' => 'warning',
                        'facebook' => 'info',
                        'twitter' => 'primary',
                        'linkedin' => 'success',
                        'telegram' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('platform_username')
                    ->label('Username')
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_type')
                    ->badge()
                    ->colors([
                        'primary' => 'personal',
                        'success' => 'business',
                        'warning' => 'official',
                    ]),

                Tables\Columns\IconColumn::make('auto_publish')
                    ->label('Auto-publish')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('token_expires_at')
                    ->label('Token Status')
                    ->formatStateUsing(function ($record) {
                        if (!$record->token_expires_at) {
                            return 'Never expires';
                        }

                        if ($record->isTokenExpired()) {
                            return '⚠️ Expired';
                        }

                        return '✓ Valid until ' . $record->token_expires_at->format('M d, Y');
                    })
                    ->color(fn ($record) => $record?->isTokenExpired() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Connected')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'youtube' => 'YouTube',
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'twitter' => 'Twitter / X',
                        'linkedin' => 'LinkedIn',
                        'telegram' => 'Telegram',
                    ]),

                Tables\Filters\TernaryFilter::make('auto_publish')
                    ->label('Auto-publish enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Disconnect')
                    ->modalHeading('Disconnect Account')
                    ->modalDescription('Are you sure you want to disconnect this social media account? You will need to reconnect it to publish to this platform again.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No social media accounts connected')
            ->emptyStateDescription('Connect your social media accounts to auto-publish your videos')
            ->emptyStateActions([
                Tables\Actions\Action::make('connect_youtube')
                    ->label('Connect YouTube')
                    ->icon('heroicon-o-play')
                    ->url(fn () => route('social.auth.redirect', 'youtube'))
                    ->color('danger'),

                Tables\Actions\Action::make('connect_instagram')
                    ->label('Connect Instagram')
                    ->icon('heroicon-o-camera')
                    ->url(fn () => route('social.auth.redirect', 'instagram'))
                    ->color('warning'),

                Tables\Actions\Action::make('connect_twitter')
                    ->label('Connect Twitter')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->url(fn () => route('social.auth.redirect', 'twitter'))
                    ->color('primary'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialMediaAccounts::route('/'),
            'edit' => Pages\EditSocialMediaAccount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin panel shows all social media accounts
        return parent::getEloquentQuery();
    }
}
