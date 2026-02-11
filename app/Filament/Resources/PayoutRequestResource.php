<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayoutRequestResource\Pages;
use App\Filament\Resources\PayoutRequestResource\RelationManagers;
use App\Models\PayoutRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class PayoutRequestResource extends Resource
{
    protected static ?string $model = PayoutRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payout Requests';

    protected static ?string $modelLabel = 'Payout Request';

    protected static ?string $pluralModelLabel = 'Payout Requests';

    protected static ?string $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Blogger')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\TextInput::make('amount')
                            ->label('Payout Amount')
                            ->prefix('$')
                            ->numeric()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\Select::make('payout_method')
                            ->label('Payout Method')
                            ->options([
                                'wise' => 'Wise (TransferWise)',
                                'payoneer' => 'Payoneer',
                                'stripe' => 'Stripe',
                                'bank_wire' => 'Bank Wire (SWIFT)',
                                'crypto_usdt' => 'Crypto (USDT)',
                                'crypto_usdc' => 'Crypto (USDC)',
                                'paypal' => 'PayPal',
                            ])
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\Textarea::make('notes')
                            ->label('Blogger Notes')
                            ->rows(3)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Processing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('Transaction Reference')
                            ->placeholder('e.g., TXN-123456789')
                            ->maxLength(255)
                            ->required(fn ($get) => $get('status') === 'completed'),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes (Internal)')
                            ->placeholder('Notes about processing, issues, or rejection reason...')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('processed_at')
                            ->label('Processing Date')
                            ->displayFormat('M d, Y H:i')
                            ->disabled(),

                        Forms\Components\Select::make('processed_by')
                            ->label('Processed By')
                            ->relationship('processor', 'name')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Blogger')
                    ->searchable()
                    ->sortable()
                    ->description(fn (PayoutRequest $record): string =>
                        '@' . $record->user->username . ' • ' . $record->user->email
                    ),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),

                Tables\Columns\TextColumn::make('payout_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'wise' => 'Wise',
                        'payoneer' => 'Payoneer',
                        'stripe' => 'Stripe',
                        'bank_wire' => 'Bank Wire',
                        'crypto_usdt' => 'USDT',
                        'crypto_usdc' => 'USDC',
                        'paypal' => 'PayPal',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'wise' => 'success',
                        'payoneer' => 'info',
                        'stripe' => 'primary',
                        'bank_wire' => 'warning',
                        'crypto_usdt' => 'danger',
                        'crypto_usdc' => 'danger',
                        'paypal' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'processing' => 'heroicon-o-arrow-path',
                        'completed' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->description(fn (PayoutRequest $record): string =>
                        $record->created_at->format('M d, Y H:i')
                    ),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Processed')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('Not processed')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Processed By')
                    ->placeholder('—')
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('transaction_reference')
                    ->label('Transaction Ref')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Reference copied!')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payout_method')
                    ->label('Method')
                    ->options([
                        'wise' => 'Wise (TransferWise)',
                        'payoneer' => 'Payoneer',
                        'stripe' => 'Stripe',
                        'bank_wire' => 'Bank Wire (SWIFT)',
                        'crypto_usdt' => 'Crypto (USDT)',
                        'crypto_usdc' => 'Crypto (USDC)',
                        'paypal' => 'PayPal',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('Min Amount')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('Max Amount')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['amount_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Min: $' . number_format($data['amount_from'], 2))
                                ->removeField('amount_from');
                        }
                        if ($data['amount_to'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Max: $' . number_format($data['amount_to'], 2))
                                ->removeField('amount_to');
                        }
                        return $indicators;
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Requested From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Requested Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PayoutRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('Transaction Reference')
                            ->placeholder('e.g., TXN-123456789')
                            ->required(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes (Optional)')
                            ->rows(3),
                    ])
                    ->action(function (PayoutRequest $record, array $data): void {
                        $record->markAsCompleted(Auth::user(), $data['transaction_reference']);

                        if (!empty($data['admin_notes'])) {
                            $record->update(['admin_notes' => $data['admin_notes']]);
                        }

                        Notification::make()
                            ->title('Payout Approved')
                            ->body('Payout request #' . $record->id . ' for $' . number_format($record->amount, 2) . ' has been approved.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PayoutRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->placeholder('Explain why this payout is being rejected...')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (PayoutRequest $record, array $data): void {
                        $record->markAsRejected(Auth::user(), $data['reason']);

                        Notification::make()
                            ->title('Payout Rejected')
                            ->body('Payout request #' . $record->id . ' has been rejected.')
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(fn (PayoutRequest $record): bool => $record->status !== 'completed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_processing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $count = $records->filter(fn (PayoutRequest $record) => $record->status === 'pending')
                                ->each(fn (PayoutRequest $record) => $record->update(['status' => 'processing']))
                                ->count();

                            Notification::make()
                                ->title("{$count} requests marked as processing")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Bulk Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Approve Payout Requests')
                        ->modalDescription('This will approve all selected payout requests. Make sure you have processed the payments externally.')
                        ->form([
                            Forms\Components\TextInput::make('transaction_prefix')
                                ->label('Transaction Reference Prefix')
                                ->placeholder('e.g., BULK-2025-01')
                                ->required()
                                ->helperText('Each request will get: PREFIX-{request_id}'),
                            Forms\Components\Textarea::make('admin_notes')
                                ->label('Admin Notes (Optional)')
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $approved = 0;
                            $records->each(function (PayoutRequest $record) use ($data, &$approved) {
                                if ($record->status === 'pending') {
                                    $txnRef = $data['transaction_prefix'] . '-' . $record->id;
                                    $record->markAsCompleted(Auth::user(), $txnRef);

                                    if (!empty($data['admin_notes'])) {
                                        $record->update(['admin_notes' => $data['admin_notes']]);
                                    }

                                    $approved++;
                                }
                            });

                            Notification::make()
                                ->title("Bulk Approval Complete")
                                ->body("{$approved} payout requests approved successfully.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('export_csv')
                        ->label('Export to CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (Collection $records): \Symfony\Component\HttpFoundation\StreamedResponse {
                            return response()->streamDownload(function () use ($records) {
                                $csv = fopen('php://output', 'w');

                                // Headers
                                fputcsv($csv, [
                                    'ID', 'Blogger Name', 'Email', 'Amount', 'Method',
                                    'Status', 'Transaction Ref', 'Requested At', 'Processed At', 'Notes'
                                ]);

                                // Data
                                foreach ($records as $record) {
                                    fputcsv($csv, [
                                        $record->id,
                                        $record->user->name,
                                        $record->user->email,
                                        $record->amount,
                                        $record->payout_method,
                                        $record->status,
                                        $record->transaction_reference ?? '',
                                        $record->created_at->format('Y-m-d H:i:s'),
                                        $record->processed_at?->format('Y-m-d H:i:s') ?? '',
                                        $record->notes ?? '',
                                    ]);
                                }

                                fclose($csv);
                            }, 'payout-requests-' . now()->format('Y-m-d') . '.csv');
                        }),
                ]),
            ])
            ->poll('30s'); // Auto-refresh every 30 seconds
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
            'index' => Pages\ListPayoutRequests::route('/'),
            'create' => Pages\CreatePayoutRequest::route('/create'),
            'edit' => Pages\EditPayoutRequest::route('/{record}/edit'),
        ];
    }
}
