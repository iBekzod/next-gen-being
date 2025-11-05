<?php

namespace App\Filament\Blogger\Resources\EarningResource\Pages;

use App\Filament\Blogger\Resources\EarningResource;
use App\Models\PayoutRequest;
use App\Services\BloggerMonetizationService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;

class ListEarnings extends ListRecords
{
    protected static string $resource = EarningResource::class;

    protected function getHeaderActions(): array
    {
        $service = app(BloggerMonetizationService::class);
        $blogger = Auth::user();
        $isEligible = $service->isEligibleForPayout($blogger);

        return [
            Actions\Action::make('requestPayout')
                ->label('Request Payout')
                ->icon('heroicon-o-banknotes')
                ->color($isEligible ? 'success' : 'gray')
                ->disabled(!$isEligible)
                ->modalHeading('Request Payout')
                ->modalDescription('Submit a payout request for your pending earnings.')
                ->modalWidth(MaxWidth::Large)
                ->form([
                    \Filament\Forms\Components\Placeholder::make('pending_amount')
                        ->label('Pending Amount')
                        ->content(function () use ($service, $blogger) {
                            $earnings = $service->getTotalEarnings($blogger);
                            return '$' . number_format($earnings['pending'], 2);
                        }),

                    \Filament\Forms\Components\Select::make('payout_method')
                        ->label('Payout Method')
                        ->options([
                            'wise' => 'Wise (TransferWise) - Recommended',
                            'payoneer' => 'Payoneer',
                            'stripe' => 'Stripe',
                            'bank_wire' => 'Bank Wire Transfer (SWIFT)',
                            'crypto_usdt' => 'Crypto (USDT)',
                            'crypto_usdc' => 'Crypto (USDC)',
                            'paypal' => 'PayPal (Not available in all countries)',
                        ])
                        ->required()
                        ->helperText('Select your preferred payout method. Wise is recommended for international transfers.'),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes (Optional)')
                        ->placeholder('Payment details, account information, etc.')
                        ->rows(3),
                ])
                ->action(function (array $data) use ($blogger, $service) {
                    // Check if there's already a pending payout request
                    $existingRequest = PayoutRequest::where('user_id', $blogger->id)
                        ->where('status', 'pending')
                        ->first();

                    if ($existingRequest) {
                        \Filament\Notifications\Notification::make()
                            ->title('Payout Request Already Exists')
                            ->body('You already have a pending payout request. Please wait for it to be processed before submitting another.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Get pending earnings amount
                    $earnings = $service->getTotalEarnings($blogger);
                    $pendingAmount = $earnings['pending'];

                    // Create payout request
                    PayoutRequest::create([
                        'user_id' => $blogger->id,
                        'amount' => $pendingAmount,
                        'payout_method' => $data['payout_method'],
                        'notes' => $data['notes'] ?? null,
                        'status' => 'pending',
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Payout Request Submitted')
                        ->body('Your payout request for $' . number_format($pendingAmount, 2) . ' has been submitted for review. We will process it within 3-5 business days.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTitle(): string
    {
        $service = app(BloggerMonetizationService::class);
        $stats = $service->getTotalEarnings(Auth::user());

        return 'My Earnings - Total: $' . number_format($stats['total'], 2);
    }
}
