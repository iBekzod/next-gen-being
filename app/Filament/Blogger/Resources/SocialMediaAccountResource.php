<?php

namespace App\Filament\Blogger\Resources;

use App\Filament\Blogger\Resources\SocialMediaAccountResource\Pages;
use App\Models\SocialMediaAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SocialMediaAccountResource extends Resource
{
    protected static ?string $model = SocialMediaAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Social Accounts';

    protected static ?string $modelLabel = 'Social Account';

    protected static ?string $pluralModelLabel = 'Social Accounts';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    // Only show accounts from the current user
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Details')
                    ->description('Configure your social media publishing settings')
                    ->schema([
                        Forms\Components\Placeholder::make('platform_display')
                            ->label('Platform')
                            ->content(fn ($record) => match($record?->platform) {
                                'youtube' => '▶️ YouTube',
                                'instagram' => '📷 Instagram',
                                'facebook' => '📘 Facebook',
                                'twitter' => '🐦 Twitter / X',
                                'linkedin' => '💼 LinkedIn',
                                'telegram' => '✈️ Telegram',
                                default => ucfirst($record?->platform ?? 'Unknown'),
                            }),

                        Forms\Components\Placeholder::make('username_display')
                            ->label('Username')
                            ->content(fn ($record) => $record?->platform_username ?? 'Not set'),

                        Forms\Components\Placeholder::make('token_status')
                            ->label('Connection Status')
                            ->content(function ($record) {
                                if (!$record) return 'New account';

                                if (!$record->token_expires_at) {
                                    return '✅ Connected (No expiration)';
                                }

                                if ($record->isTokenExpired()) {
                                    return '⚠️ Expired - Please reconnect';
                                }

                                return '✅ Connected until ' . $record->token_expires_at->format('M d, Y');
                            })
                            ->color(fn ($record) => $record?->isTokenExpired() ? 'danger' : 'success'),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('Publishing Settings')
                    ->description('Control how your videos are published to this platform')
                    ->schema([
                        Forms\Components\Select::make('account_type')
                            ->label('Account Type')
                            ->options([
                                'personal' => 'Personal',
                                'business' => 'Business',
                            ])
                            ->default('personal')
                            ->required()
                            ->helperText('Choose the type of account you want to use for publishing'),

                        Forms\Components\Toggle::make('auto_publish')
                            ->label('Auto-publish new videos')
                            ->helperText('Automatically publish generated videos to this account')
                            ->default(false)
                            ->live(),

                        Forms\Components\Placeholder::make('auto_publish_info')
                            ->label('')
                            ->content('💡 When enabled, all new videos will be automatically published to this platform. You can still manually publish individual videos.')
                            ->visible(fn (Forms\Get $get) => $get('auto_publish')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Platform-Specific Settings')
                    ->description('Additional settings for this platform')
                    ->schema([
                        Forms\Components\Textarea::make('publish_schedule')
                            ->label('Publishing Schedule (JSON, Optional)')
                            ->helperText('Define specific days and times to auto-publish in JSON format. Example: {"days":["mon","wed"],"times":["09:00","18:00"]}. Leave empty to publish immediately.')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->label('Platform')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'youtube' => '▶️ YouTube',
                        'instagram' => '📷 Instagram',
                        'facebook' => '📘 Facebook',
                        'twitter' => '🐦 Twitter / X',
                        'linkedin' => '💼 LinkedIn',
                        'telegram' => '✈️ Telegram',
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
                    ->searchable()
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('account_type')
                    ->badge()
                    ->colors([
                        'primary' => 'personal',
                        'success' => 'business',
                    ]),

                Tables\Columns\IconColumn::make('auto_publish')
                    ->label('Auto-publish')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_count')
                    ->label('Published')
                    ->getStateUsing(function ($record) {
                        return \App\Models\SocialMediaPost::where('social_media_account_id', $record->id)
                            ->where('status', 'published')
                            ->count();
                    })
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-document-check'),

                Tables\Columns\TextColumn::make('token_expires_at')
                    ->label('Status')
                    ->formatStateUsing(function ($record) {
                        if (!$record->token_expires_at) {
                            return '✓ Active';
                        }

                        if ($record->isTokenExpired()) {
                            return '⚠️ Expired';
                        }

                        return '✓ Active';
                    })
                    ->badge()
                    ->color(fn ($record) => $record?->isTokenExpired() ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Connected')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

                Tables\Filters\Filter::make('expired')
                    ->label('Expired tokens')
                    ->query(fn (Builder $query) => $query->whereNotNull('token_expires_at')
                        ->where('token_expires_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('reconnect')
                    ->label('Reconnect')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->isTokenExpired())
                    ->url(fn ($record) => route('social.auth.redirect', $record->platform))
                    ->tooltip('Your token has expired. Click to reconnect.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Disconnect')
                    ->modalHeading('Disconnect Account')
                    ->modalDescription('Are you sure you want to disconnect this social media account? You will need to reconnect it to publish to this platform again.')
                    ->successNotificationTitle('Account disconnected'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Disconnect Selected'),
                ]),
            ])
            ->emptyStateHeading('No social media accounts connected')
            ->emptyStateDescription('Connect your social media accounts to auto-publish your videos and reach a wider audience.')
            ->emptyStateIcon('heroicon-o-share')
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
                    ->label('Connect Twitter / X')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->url(fn () => route('social.auth.redirect', 'twitter'))
                    ->color('primary'),

                Tables\Actions\Action::make('connect_facebook')
                    ->label('Connect Facebook')
                    ->icon('heroicon-o-users')
                    ->url(fn () => route('social.auth.redirect', 'facebook'))
                    ->color('info'),

                Tables\Actions\Action::make('connect_linkedin')
                    ->label('Connect LinkedIn')
                    ->icon('heroicon-o-briefcase')
                    ->url(fn () => route('social.auth.redirect', 'linkedin'))
                    ->color('success'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialMediaAccounts::route('/'),
            'edit' => Pages\EditSocialMediaAccount::route('/{record}/edit'),
        ];
    }
}
