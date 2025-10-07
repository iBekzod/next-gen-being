<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(255)
                            ->unique(Setting::class, 'key', ignoreRecord: true),

                        Forms\Components\Select::make('type')
                            ->options([
                                'string' => 'String',
                                'json' => 'JSON',
                                'boolean' => 'Boolean',
                                'integer' => 'Integer',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('group')
                            ->options([
                                'general' => 'General',
                                'seo' => 'SEO',
                                'social' => 'Social Media',
                                'content' => 'Content',
                                'analytics' => 'Analytics',
                                'mail' => 'Email',
                                'subscription' => 'Subscription',
                                'ai' => 'AI',
                            ])
                            ->default('general'),

                        Forms\Components\Toggle::make('is_public')
                            ->helperText('Public settings can be accessed from frontend'),

                        Forms\Components\Textarea::make('description')
                            ->rows(2),

                        Forms\Components\Textarea::make('value')
                            ->required()
                            ->visible(fn ($get) => in_array($get('type'), ['string', 'json']))
                            ->rows(4),

                        Forms\Components\Toggle::make('value')
                            ->visible(fn ($get) => $get('type') === 'boolean'),

                        Forms\Components\TextInput::make('value')
                            ->numeric()
                            ->visible(fn ($get) => $get('type') === 'integer'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'primary',
                        'seo' => 'success',
                        'social' => 'info',
                        'analytics' => 'warning',
                        'mail' => 'danger',
                        'subscription' => 'gray',
                        'content' => 'primary',
                        'ai' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('type')
                    ->badge(),

                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->value),

                Tables\Columns\IconColumn::make('is_public')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'general' => 'General',
                        'seo' => 'SEO',
                        'social' => 'Social Media',
                        'content' => 'Content',
                        'analytics' => 'Analytics',
                        'mail' => 'Email',
                        'subscription' => 'Subscription',
                        'ai' => 'AI',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'json' => 'JSON',
                        'boolean' => 'Boolean',
                        'integer' => 'Integer',
                    ]),
                Tables\Filters\Filter::make('public_only')
                    ->query(fn ($query) => $query->where('is_public', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}


