@php
$credentialService = app(\App\Services\OAuthCredentialService::class);
$enabledProviders = $credentialService->getEnabledProviders();
@endphp

@if($enabledProviders)
    <div class="space-y-3 mt-6 border-t pt-6">
        <p class="text-center text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ $title ?? 'Or continue with' }}
        </p>

        @php
        $gridClass = match(count($enabledProviders)) {
            1 => 'grid grid-cols-1',
            2 => 'flex flex-wrap',
            default => 'grid grid-cols-2'
        };
        @endphp

        <div class="{{ $gridClass }} gap-3">
            @foreach($enabledProviders as $provider)
                @php
                $info = $credentialService->getProviderInfo($provider);
                @endphp
                <a href="{{ route('auth.social.redirect', $provider) }}"
                   class="flex items-center justify-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group"
                   title="{{ $info['display_name'] ?? ucfirst($provider) }}">

                    @switch($provider)
                        @case('google')
                            <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Google</span>
                        @break

                        @case('github')
                            <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">GitHub</span>
                        @break

                        @case('facebook')
                            <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Facebook</span>
                        @break

                        @case('discord')
                            <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.885-1.515a.074.074 0 00-.079.0366c-.211.3693-.445.8495-.608 1.2297a18.27 18.27 0 00-5.487 0c-.163-.3802-.397-.8604-.609-1.2297a.077.077 0 00-.079-.0366c-1.687.289-3.29.889-4.885 1.515a.07.07 0 00-.032.0277C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.0577c2.021 1.484 3.98 2.369 5.904 2.973a.078.078 0 00.085-.028c.462-.63.873-1.295 1.226-1.994a.076.076 0 00-.041-.106c-.636-.204-1.243-.478-1.843-.8a.075.075 0 01-.008-.125c.124-.093.248-.19.368-.27a.074.074 0 01.076-.01c3.864 1.759 8.04 1.759 11.877 0a.075.075 0 01.076.01c.12.08.244.177.368.27a.075.075 0 01-.006.125c-.6.322-1.207.596-1.843.8a.077.077 0 00-.041.107c.352.699.763 1.364 1.226 1.994a.076.076 0 00.084.028c1.925-.604 3.884-1.489 5.904-2.973a.077.077 0 00.032-.0577c.504-4.778-.838-8.94-3.549-12.634a.061.061 0 00-.031-.028zM8.02 15.33c-1.183 0-2.157-.965-2.157-2.156c0-1.193.964-2.157 2.157-2.157c1.193 0 2.156.964 2.157 2.157c0 1.19-.964 2.156-2.157 2.156zm7.975 0c-1.183 0-2.157-.965-2.157-2.156c0-1.193.965-2.157 2.157-2.157c1.192 0 2.157.964 2.157 2.157c0 1.19-.965 2.156-2.157 2.156z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Discord</span>
                        @break
                    @endswitch
                </a>
            @endforeach
        </div>
    </div>
@endif
