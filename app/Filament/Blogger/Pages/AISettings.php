<?php

namespace App\Filament\Blogger\Pages;

use App\Services\EnhancedAIGenerationService;
use App\Services\LemonSqueezyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AISettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'AI Settings';

    protected static ?string $title = 'AI Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.blogger.pages.ai-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'groq_api_key' => $user->groq_api_key,
            'openai_api_key' => $user->openai_api_key,
            'unsplash_api_key' => $user->unsplash_api_key,
        ]);
    }

    public function form(Form $form): Form
    {
        $user = Auth::user();
        $aiService = app(EnhancedAIGenerationService::class);
        $stats = $aiService->getUsageStats($user);
        $tierLimits = $aiService->getTierLimits($user->ai_tier);

        return $form
            ->schema([
                Forms\Components\Section::make('Current AI Subscription')
                    ->description('Your current AI tier and usage statistics')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Placeholder::make('current_tier')
                                    ->label('Current Tier')
                                    ->content(fn () => $user->getAITierName()),

                                Forms\Components\Placeholder::make('posts_quota')
                                    ->label('Posts Used')
                                    ->content(function () use ($stats) {
                                        $limit = $stats['posts_limit'] ?? 'unlimited';
                                        return "{$stats['posts_used']}/{$limit}";
                                    }),

                                Forms\Components\Placeholder::make('images_quota')
                                    ->label('Images Used')
                                    ->content(function () use ($stats) {
                                        $limit = $stats['images_limit'] ?? 'unlimited';
                                        return "{$stats['images_used']}/{$limit}";
                                    }),

                                Forms\Components\Placeholder::make('reset_date')
                                    ->label('Quota Resets')
                                    ->content(fn () => $stats['reset_date'] ? $stats['reset_date']->format('M d, Y') : 'N/A'),
                            ]),

                        Forms\Components\Placeholder::make('tier_features')
                            ->label('Features')
                            ->content(function () use ($tierLimits) {
                                $features = $tierLimits['features'] ?? [];
                                return implode(' • ', $features);
                            }),
                    ]),

                Forms\Components\Section::make('API Keys (Free Tier)')
                    ->description('Add your own API keys to use AI features for free')
                    ->schema([
                        Forms\Components\TextInput::make('groq_api_key')
                            ->label('Groq API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('gsk_...')
                            ->helperText('Get your free API key from https://console.groq.com')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('openai_api_key')
                            ->label('OpenAI API Key (Optional)')
                            ->password()
                            ->revealable()
                            ->placeholder('sk-...')
                            ->helperText('Only needed if you want to use your own OpenAI credits')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('unsplash_api_key')
                            ->label('Unsplash API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('Access Key from Unsplash')
                            ->helperText('Get your free API key from https://unsplash.com/developers')
                            ->maxLength(255),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('save_api_keys')
                                ->label('Save API Keys')
                                ->icon('heroicon-o-check')
                                ->color('success')
                                ->action(function (array $data) use ($user) {
                                    $user->update([
                                        'groq_api_key' => $data['groq_api_key'] ?? null,
                                        'openai_api_key' => $data['openai_api_key'] ?? null,
                                        'unsplash_api_key' => $data['unsplash_api_key'] ?? null,
                                    ]);

                                    Notification::make()
                                        ->title('API Keys Saved')
                                        ->body('Your API keys have been securely encrypted and saved.')
                                        ->success()
                                        ->send();
                                }),
                        ]),
                    ])
                    ->visible(fn () => $user->ai_tier === 'free'),

                Forms\Components\Section::make('Upgrade Your AI Subscription')
                    ->description('Get more AI credits and access to premium features')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('basic_tier')
                                    ->label('Basic - $9.99/mo')
                                    ->content('50 posts • 100 images • Groq + Unsplash • Priority support'),

                                Forms\Components\Placeholder::make('premium_tier')
                                    ->label('Premium - $29.99/mo')
                                    ->content('Unlimited posts • Unlimited images • GPT-4 + DALL-E 3 • Priority support'),

                                Forms\Components\Placeholder::make('enterprise_tier')
                                    ->label('Enterprise - $99.99/mo')
                                    ->content('Everything in Premium • Dedicated support • Custom models • API access'),
                            ]),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('upgrade_basic')
                                ->label('Upgrade to Basic')
                                ->icon('heroicon-o-arrow-up')
                                ->color('info')
                                ->url(fn () => $this->getCheckoutUrl('basic', $user))
                                ->openUrlInNewTab()
                                ->visible(fn () => $user->ai_tier === 'free'),

                            Forms\Components\Actions\Action::make('upgrade_premium')
                                ->label('Upgrade to Premium')
                                ->icon('heroicon-o-arrow-up')
                                ->color('warning')
                                ->url(fn () => $this->getCheckoutUrl('premium', $user))
                                ->openUrlInNewTab()
                                ->visible(fn () => in_array($user->ai_tier, ['free', 'basic'])),

                            Forms\Components\Actions\Action::make('upgrade_enterprise')
                                ->label('Upgrade to Enterprise')
                                ->icon('heroicon-o-arrow-up')
                                ->color('danger')
                                ->url(fn () => $this->getCheckoutUrl('enterprise', $user))
                                ->openUrlInNewTab()
                                ->visible(fn () => in_array($user->ai_tier, ['free', 'basic', 'premium'])),
                        ]),
                    ])
                    ->visible(fn () => $user->ai_tier !== 'enterprise'),

                Forms\Components\Section::make('How to Get API Keys')
                    ->description('Step-by-step guides for getting free API keys')
                    ->schema([
                        Forms\Components\Placeholder::make('groq_guide')
                            ->label('Groq (Free Tier)')
                            ->content('1. Visit https://console.groq.com
2. Sign up for a free account
3. Go to API Keys section
4. Create a new API key
5. Copy and paste it above

Groq offers fast, free AI with Llama 3.3 70B model.'),

                        Forms\Components\Placeholder::make('unsplash_guide')
                            ->label('Unsplash (Free Images)')
                            ->content('1. Visit https://unsplash.com/developers
2. Sign up for a free account
3. Create a new application
4. Copy your Access Key
5. Paste it above

Unsplash offers 50 free image requests per hour.'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(true),

                Forms\Components\Section::make('Manage Subscription')
                    ->description('Manage your AI subscription billing')
                    ->schema([
                        Forms\Components\Placeholder::make('subscription_info')
                            ->label('Subscription Status')
                            ->content(function () use ($user) {
                                if ($user->ai_tier_expires_at) {
                                    $daysRemaining = now()->diffInDays($user->ai_tier_expires_at, false);
                                    return "Expires in {$daysRemaining} days ({$user->ai_tier_expires_at->format('M d, Y')})";
                                }
                                return 'Active monthly subscription';
                            }),

                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('manage_billing')
                                ->label('Manage Billing')
                                ->icon('heroicon-o-credit-card')
                                ->color('gray')
                                ->url(fn () => $this->getCustomerPortalUrl($user))
                                ->openUrlInNewTab(),

                            Forms\Components\Actions\Action::make('cancel_subscription')
                                ->label('Cancel Subscription')
                                ->icon('heroicon-o-x-circle')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading('Cancel AI Subscription?')
                                ->modalDescription('You will lose access to premium AI features at the end of your billing period.')
                                ->action(function () use ($user) {
                                    $this->cancelAISubscription($user);
                                }),
                        ]),
                    ])
                    ->visible(fn () => $user->hasAISubscription()),
            ])
            ->statePath('data');
    }

    public function getUsageStats(): array
    {
        $aiService = app(EnhancedAIGenerationService::class);
        return $aiService->getUsageStats(Auth::user());
    }

    /**
     * Generate checkout URL for AI subscription upgrade
     */
    private function getCheckoutUrl(string $tier, $user): string
    {
        $variantId = match($tier) {
            'basic' => config('services.lemonsqueezy.ai_basic_variant_id'),
            'premium' => config('services.lemonsqueezy.ai_premium_variant_id'),
            'enterprise' => config('services.lemonsqueezy.ai_enterprise_variant_id'),
            default => null,
        };

        if (!$variantId) {
            return '#'; // Fallback if variant ID not configured
        }

        $lemonSqueezy = new LemonSqueezyService();
        $checkoutUrl = $lemonSqueezy->createCheckout([
            'variant_id' => $variantId,
            'email' => $user->email,
            'name' => $user->name,
            'checkout_data' => [
                'custom' => [
                    'user_id' => $user->id,
                    'tier' => $tier,
                ],
            ],
        ]);

        return $checkoutUrl ?? '#';
    }

    /**
     * Get customer portal URL for subscription management
     */
    private function getCustomerPortalUrl($user): string
    {
        if (!$user->lemonsqueezy_subscription_id) {
            return '#';
        }

        $lemonSqueezy = new LemonSqueezyService();
        $portalUrl = $lemonSqueezy->getCustomerPortalUrl($user->lemonsqueezy_subscription_id);

        return $portalUrl ?? '#';
    }

    /**
     * Cancel AI subscription via LemonSqueezy
     */
    private function cancelAISubscription($user): void
    {
        if (!$user->lemonsqueezy_subscription_id) {
            Notification::make()
                ->title('No Active Subscription')
                ->body('You don\'t have an active AI subscription to cancel.')
                ->warning()
                ->send();
            return;
        }

        $lemonSqueezy = new LemonSqueezyService();
        $success = $lemonSqueezy->cancelSubscription($user->lemonsqueezy_subscription_id);

        if ($success) {
            $user->update([
                'ai_tier' => 'free',
                'lemonsqueezy_subscription_id' => null,
                'ai_tier_expires_at' => null,
            ]);

            Notification::make()
                ->title('Subscription Cancelled')
                ->body('Your AI subscription has been successfully cancelled. You\'ll revert to free tier at the end of your billing period.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Cancellation Failed')
                ->body('Unable to cancel subscription. Please try again or contact support.')
                ->danger()
                ->send();
        }
    }
}
