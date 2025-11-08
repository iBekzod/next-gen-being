@props(['user' => null])

@php
    if (!$user) {
        $user = auth()->user();
    }

    $providers = ['google', 'github', 'discord', 'twitter', 'facebook', 'linkedin'];
    $connectedProviders = $user->socialAccounts()->pluck('provider')->toArray();
    $connectedProviders = array_map(fn($p) => strtolower($p), $connectedProviders);
@endphp

<div class="space-y-4">
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Connected Accounts</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Link your social media accounts for faster login and content sharing.</p>
    </div>

    <div class="space-y-2">
        @foreach($providers as $provider)
        @php
            $isConnected = in_array($provider, $connectedProviders);
            $socialAccount = $isConnected ? $user->socialAccounts()->where('provider', $provider)->first() : null;

            $displayNames = [
                'google' => 'Google',
                'github' => 'GitHub',
                'discord' => 'Discord',
                'twitter' => 'X (Twitter)',
                'facebook' => 'Facebook',
                'linkedin' => 'LinkedIn',
            ];
            $displayName = $displayNames[$provider] ?? ucfirst($provider);

            $icons = [
                'google' => '🔵',
                'github' => '⚫',
                'discord' => '💜',
                'twitter' => '⚫',
                'facebook' => '🔵',
                'linkedin' => '🔷',
            ];
            $icon = $icons[$provider] ?? '🔗';
        @endphp

        <div class="flex items-center justify-between p-4 rounded-lg border {{ $isConnected ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-slate-800 border-gray-200 dark:border-slate-700' }}">
            <div class="flex items-center gap-3">
                <span class="text-2xl">{{ $icon }}</span>
                <div>
                    <p class="font-semibold {{ $isConnected ? 'text-green-900 dark:text-green-100' : 'text-gray-900 dark:text-white' }}">
                        {{ $displayName }}
                    </p>
                    @if($isConnected && $socialAccount)
                    <p class="text-sm {{ $isConnected ? 'text-green-700 dark:text-green-300' : 'text-gray-600 dark:text-gray-400' }}">
                        Connected as <strong>{{ $socialAccount->provider_name ?? $socialAccount->provider_email }}</strong>
                    </p>
                    @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Not connected
                    </p>
                    @endif
                </div>
            </div>

            <div>
                @if($isConnected)
                <form action="{{ route('auth.social.disconnect', $provider) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 font-semibold rounded-lg transition-colors text-sm">
                        Disconnect
                    </button>
                </form>
                @else
                <a href="{{ route('auth.social.redirect', $provider) }}"
                   class="inline-flex px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                    Connect
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Security Notice -->
    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <p class="text-sm text-blue-900 dark:text-blue-200">
            <strong>💡 Tip:</strong> Connecting your accounts allows you to sign in with any connected provider. You can manage all connected accounts here.
        </p>
    </div>
</div>
