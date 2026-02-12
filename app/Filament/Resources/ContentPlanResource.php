<?php

namespace App\Filament\Resources;

use App\Models\ContentPlan;
use App\Filament\Resources\ContentPlanResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class ContentPlanResource extends Resource
{
    protected static ?string $model = ContentPlan::class;
    protected static ?string $slug = 'content-plans';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Content Plan Details')
                ->schema([
                    Forms\Components\TextInput::make('month')
                        ->label('Month (YYYY-MM)')
                        ->required(),
                    Forms\Components\TextInput::make('theme')
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(1000),
                    Forms\Components\TagsInput::make('planned_topics')
                        ->separator(','),
                    Forms\Components\KeyValue::make('generated_topics')
                        ->disabled(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'active' => 'Active',
                            'completed' => 'Completed',
                            'archived' => 'Archived',
                        ])
                        ->default('draft'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->sortable(),
                Tables\Columns\TextColumn::make('theme')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'info',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
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
            ->defaultSort('month', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentPlans::route('/'),
            'create' => Pages\CreateContentPlan::route('/create'),
            'edit' => Pages\EditContentPlan::route('/{record}/edit'),
        ];
    }
}
