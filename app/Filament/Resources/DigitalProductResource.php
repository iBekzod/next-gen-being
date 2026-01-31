<?php

namespace App\Filament\Resources;

use App\Models\DigitalProduct;
use App\Filament\Resources\DigitalProductResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class DigitalProductResource extends Resource
{
    protected static ?string $model = DigitalProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Monetization';
    protected static ?string $navigationLabel = 'Digital Products';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Product Information')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state ?? ''))),

                                Forms\Components\TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->unique(DigitalProduct::class, 'slug', ignoreRecord: true),

                                Forms\Components\Select::make('type')
                                    ->options([
                                        'prompt' => 'Prompt Template',
                                        'template' => 'Template',
                                        'tutorial' => 'Tutorial',
                                        'course' => 'Course',
                                        'cheatsheet' => 'Cheatsheet',
                                        'code_example' => 'Code Example',
                                    ])
                                    ->required(),

                                Forms\Components\Textarea::make('short_description')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('description')
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('content')
                                    ->label('Preview Content')
                                    ->helperText('First 200 characters will be shown as preview')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Pricing & Access')
                            ->schema([
                                Forms\Components\Toggle::make('is_free')
                                    ->reactive(),

                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->hidden(fn(Forms\Get $get) => $get('is_free'))
                                    ->required(fn(Forms\Get $get) => !$get('is_free')),

                                Forms\Components\TextInput::make('original_price')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->helperText('Set if product is on sale'),

                                Forms\Components\Select::make('tier_required')
                                    ->options([
                                        'free' => 'Free (No subscription required)',
                                        'basic' => 'Basic ($9.99/mo)',
                                        'pro' => 'Pro ($19.99/mo)',
                                        'team' => 'Team ($49.99/mo)',
                                    ])
                                    ->default('free'),

                                Forms\Components\TextInput::make('revenue_share_percentage')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->default(70)
                                    ->suffix('%')
                                    ->helperText('Creator percentage (e.g., 70)'),
                            ]),

                        Forms\Components\Section::make('File & Media')
                            ->schema([
                                Forms\Components\FileUpload::make('file_path')
                                    ->disk('private')
                                    ->directory('products')
                                    ->acceptedFileTypes(['text/plain', 'application/pdf', 'text/csv'])
                                    ->maxSize(50000), // 50MB

                                Forms\Components\FileUpload::make('preview_file_path')
                                    ->disk('private')
                                    ->directory('products/previews')
                                    ->acceptedFileTypes(['text/plain', 'application/pdf'])
                                    ->maxSize(10000),

                                Forms\Components\FileUpload::make('thumbnail')
                                    ->image()
                                    ->directory('products/thumbnails'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Publishing')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'pending_review' => 'Pending Review',
                                        'published' => 'Published',
                                        'archived' => 'Archived',
                                    ])
                                    ->default('draft')
                                    ->required(),

                                Forms\Components\DateTimePicker::make('published_at'),
                            ]),

                        Forms\Components\Section::make('Metadata')
                            ->schema([
                                Forms\Components\TextInput::make('category'),

                                Forms\Components\TagsInput::make('tags')
                                    ->placeholder('Add tags'),

                                Forms\Components\TagsInput::make('features')
                                    ->placeholder('Add features'),

                                Forms\Components\TagsInput::make('includes')
                                    ->placeholder('What\'s included'),
                            ]),

                        Forms\Components\Section::make('Statistics')
                            ->schema([
                                Forms\Components\TextInput::make('downloads_count')
                                    ->disabled(),

                                Forms\Components\TextInput::make('purchases_count')
                                    ->disabled(),

                                Forms\Components\TextInput::make('rating')
                                    ->disabled(),

                                Forms\Components\TextInput::make('reviews_count')
                                    ->disabled(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('category')
                    ->sortable(),

                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('purchases_count')
                    ->label('Purchases')
                    ->sortable(),

                TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'pending_review',
                        'success' => 'published',
                        'danger' => 'archived',
                    ])
                    ->sortable(),

                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'prompt' => 'Prompt Template',
                        'template' => 'Template',
                        'tutorial' => 'Tutorial',
                        'course' => 'Course',
                        'cheatsheet' => 'Cheatsheet',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_review' => 'Pending Review',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
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
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDigitalProducts::route('/'),
            'create' => Pages\CreateDigitalProduct::route('/create'),
            'edit' => Pages\EditDigitalProduct::route('/{record}/edit'),
        ];
    }
}
