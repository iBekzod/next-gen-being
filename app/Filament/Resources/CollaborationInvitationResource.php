<?php

namespace App\Filament\Resources;

use App\Models\CollaborationInvitation;
use App\Filament\Resources\CollaborationInvitationResource\Pages;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

class CollaborationInvitationResource extends Resource
{
    protected static ?string $model = CollaborationInvitation::class;
    protected static ?string $slug = 'collaboration-invitations';
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationGroup = 'Collaboration';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Collaboration Invitation Details')
                ->schema([
                    Forms\Components\Select::make('post_id')
                        ->relationship('post', 'title')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('inviter_id')
                        ->relationship('inviter', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required(),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable(),
                    Forms\Components\Select::make('role')
                        ->options([
                            'owner' => 'Owner',
                            'editor' => 'Editor',
                            'reviewer' => 'Reviewer',
                            'viewer' => 'Viewer',
                        ])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'accepted' => 'Accepted',
                            'declined' => 'Declined',
                            'cancelled' => 'Cancelled',
                        ])
                        ->default('pending'),
                    Forms\Components\TextInput::make('token')
                        ->disabled(),
                    Forms\Components\DateTimePicker::make('expires_at'),
                    Forms\Components\DateTimePicker::make('accepted_at'),
                    Forms\Components\DateTimePicker::make('declined_at'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('inviter.name')
                    ->label('Invited By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'owner' => 'danger',
                        'editor' => 'warning',
                        'reviewer' => 'info',
                        'viewer' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'declined' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('role'),
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
            'index' => Pages\ListCollaborationInvitations::route('/'),
            'create' => Pages\CreateCollaborationInvitation::route('/create'),
            'edit' => Pages\EditCollaborationInvitation::route('/{record}/edit'),
        ];
    }
}
