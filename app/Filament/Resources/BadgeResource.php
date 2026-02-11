<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Badges';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Badge Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000),

                        Forms\Components\TextInput::make('icon')
                            ->label('Icon (URL or Class)')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('color')
                            ->label('Color (Hex)')
                            ->placeholder('#000000')
                            ->maxLength(7),

                        Forms\Components\FileUpload::make('image_url')
                            ->label('Badge Image')
                            ->image()
                            ->directory('badges'),

                        Forms\Components\TextInput::make('points_required')
                            ->label('Points Required')
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Badge')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),

                Tables\Columns\TextColumn::make('points_required')
                    ->label('Points Required')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
}
