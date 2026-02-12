<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterCampaignResource\Pages;
use App\Models\NewsletterCampaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NewsletterCampaignResource extends Resource
{
    protected static ?string $model = NewsletterCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Newsletter Campaigns';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Campaign Details')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('content')
                            ->label('Content')
                            ->required()
                            ->rows(8)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'promotional' => 'Promotional',
                                'educational' => 'Educational',
                                'announcement' => 'Announcement',
                                'engagement' => 'Engagement',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'scheduled' => 'Scheduled',
                                'sent' => 'Sent',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Scheduled At'),

                        Forms\Components\DateTimePicker::make('sent_at')
                            ->label('Sent At')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Engagement Metrics')
                    ->schema([
                        Forms\Components\TextInput::make('recipients_count')
                            ->label('Recipients')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('opened_count')
                            ->label('Opened')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('clicked_count')
                            ->label('Clicked')
                            ->numeric()
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'gray' => 'promotional',
                        'info' => 'educational',
                        'warning' => 'announcement',
                        'success' => 'engagement',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'scheduled',
                        'success' => 'sent',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('recipients_count')
                    ->label('Recipients')
                    ->numeric()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('opened_count')
                    ->label('Opens')
                    ->numeric()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('clicked_count')
                    ->label('Clicks')
                    ->numeric()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'sent' => 'Sent',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'promotional' => 'Promotional',
                        'educational' => 'Educational',
                        'announcement' => 'Announcement',
                        'engagement' => 'Engagement',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterCampaigns::route('/'),
            'create' => Pages\CreateNewsletterCampaign::route('/create'),
            'edit' => Pages\EditNewsletterCampaign::route('/{record}/edit'),
        ];
    }
}
