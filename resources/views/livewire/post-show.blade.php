<div>
    <!-- Reading Progress Bar -->
    <div x-data="readingProgress()"
         x-init="init()"
         class="fixed top-0 left-0 right-0 z-50 h-1 bg-gray-200">
        <div class="h-full transition-all duration-150 bg-blue-600"
             :style="`width: ${progress}%`"></div>
    </div>

    <article class="max-w-4xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex items-center mb-4 space-x-3">
            <span class="px-3 py-1 bg-{{ $post->category->color ?? 'blue' }}-500 text-white text-sm font-medium rounded-full">
                {{ $post->category->name }}
            </span>
            @if($post->is_premium)
            <span class="flex items-center px-3 py-1 text-sm font-medium text-white rounded-full bg-gradient-to-r from-yellow-400 to-orange-500">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Premium Content
            </span>
            @endif
            <span class="text-sm text-gray-700 dark:text-gray-400">{{ $post->read_time }} min read</span>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}"
                     alt="{{ $post->author->name }}"
                     class="w-12 h-12 rounded-full">
                <div>
                    <div class="flex items-center space-x-3">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $post->author->name }}</p>
                        @if(auth()->check() && auth()->id() !== $post->author_id)
                        <button wire:click="toggleFollow"
                                class="px-3 py-1 text-xs font-medium rounded-full transition-all duration-200 {{ auth()->user()->isFollowing($post->author) ? 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 hover:shadow-md' : 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow-md hover:scale-105' }}">
                            {{ auth()->user()->isFollowing($post->author) ? 'Following' : '+ Follow' }}
                        </button>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-400">
                        @if($post->published_at)
                        <span>{{ $post->published_at->format('M j, Y') }}</span>
                        <span>•</span>
                        @endif
                        <span>{{ number_format($post->views_count) }} views</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-3">
                <button wire:click="toggleLike"
                        class="flex items-center space-x-2 px-4 py-2 rounded-lg border-2 {{ auth()->user()?->hasLiked($post) ? 'bg-red-50 border-red-400 text-red-600 shadow-md dark:bg-red-900/30 dark:border-red-500 dark:text-red-400' : 'bg-white border-gray-300 text-gray-700 hover:bg-red-50 hover:border-red-400 hover:shadow-md dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-red-900/30 dark:hover:border-red-500' }} transition-all duration-200">
                    <svg class="w-5 h-5" fill="{{ auth()->user()?->hasLiked($post) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span>{{ number_format($post->likes_count) }}</span>
                </button>

                <button wire:click="toggleBookmark"
                        class="flex items-center space-x-2 px-4 py-2 rounded-lg border-2 {{ auth()->user()?->hasBookmarked($post) ? 'bg-blue-50 border-blue-400 text-blue-600 shadow-md dark:bg-blue-900/30 dark:border-blue-500 dark:text-blue-400' : 'bg-white border-gray-300 text-gray-700 hover:bg-blue-50 hover:border-blue-400 hover:shadow-md dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-blue-900/30 dark:hover:border-blue-500' }} transition-all duration-200">
                    <svg class="w-5 h-5" fill="{{ auth()->user()?->hasBookmarked($post) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </button>

                <div class="relative" x-data="shareMenu()" @keydown.escape="open = false">
                    <button @click="open = !open"
                            class="flex items-center px-4 py-2 space-x-2 text-gray-700 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 hover:shadow-md dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:border-slate-500 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 z-10 w-56 py-2 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-slate-800 dark:border-slate-700">
                        <button @click="copyLink()" class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy Link
                        </button>
                        <button @click="shareTwitter()" class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2s9 5 20 5a9.5 9.5 0 00-9-5.5c4.75 2.25 7-7 7-7"/></svg>
                            Share on Twitter
                        </button>
                        <button @click="shareLinkedIn()" class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                            Share on LinkedIn
                        </button>
                        <button @click="shareFacebook()" class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="currentColor" viewBox="0 0 24 24"><path d="M19 12a7 7 0 11-14 0 7 7 0 0114 0zM9 15a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                            Share on Facebook
                        </button>
                        <button @click="shareEmail()" class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Share via Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Featured Image -->
    @if($post->featured_image)
    <div class="mb-8">
        <img src="{{ $post->featured_image }}"
             alt="{{ $post->title }}"
             class="object-cover w-full h-96 rounded-2xl">
        @if($post->image_attribution && isset($post->image_attribution['source']) && $post->image_attribution['source'] === 'unsplash')
        <div class="mt-2 text-sm text-gray-700 dark:text-gray-400">
            Photo by <a href="{{ $post->image_attribution['photographer_url'] }}?utm_source=nextgenbeing&utm_medium=referral" target="_blank" rel="noopener noreferrer" class="font-medium text-gray-900 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">{{ $post->image_attribution['photographer_name'] }}</a> on <a href="https://unsplash.com?utm_source=nextgenbeing&utm_medium=referral" target="_blank" rel="noopener noreferrer" class="font-medium text-gray-900 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Unsplash</a>
        </div>
        @endif
    </div>
    @endif

    <!-- Reader Toolbar with Font Size, Line Height, Reading Modes -->
    <x-post.reader-toolbar :post="$post" />

    <!-- Audio Player -->
    <x-post.audio-player :post="$post" />

    <!-- Content -->
    <div class="mb-12 prose prose-lg max-w-none dark:prose-invert">
        @if($post->shouldShowPaywall(auth()->user()))
            <!-- Preview Content (First 30%) -->
            <div class="relative">
                {!! str($post->getPreviewContent())->markdown() !!}

                <!-- Fade Overlay -->
                <div class="absolute bottom-0 left-0 right-0 h-64 bg-gradient-to-t from-white via-white/95 to-transparent dark:from-slate-900 dark:via-slate-900/95 pointer-events-none"></div>
            </div>

            <!-- Premium Paywall -->
            <div class="relative -mt-32 mb-12">
                <div class="relative z-10 max-w-2xl p-8 mx-auto bg-white border-2 border-blue-500 shadow-2xl dark:bg-slate-800 dark:border-blue-400 rounded-2xl">
                    <div class="mb-6 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 mb-4 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                            Unlock Premium Content
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            You've read <span class="font-bold text-blue-600 dark:text-blue-400">{{ $post->preview_percentage ?? 30 }}%</span> of this article
                        </p>
                    </div>

                    <!-- What You'll Get -->
                    <div class="p-6 mb-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                        <h4 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h12a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            What's in the full article
                        </h4>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Complete step-by-step implementation guide</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Working code examples you can copy-paste</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Advanced techniques and pro tips</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Common mistakes to avoid</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="flex-shrink-0 w-5 h-5 mt-0.5 mr-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300">Real-world examples and metrics</span>
                            </li>
                        </ul>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="space-y-3">
                        @auth
                            <a href="{{ route('subscription.plans') }}"
                               class="block w-full py-4 text-lg font-bold text-center text-white transition-all transform bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:shadow-xl hover:scale-105">
                                Upgrade to Premium
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="block w-full py-4 text-lg font-bold text-center text-white transition-all transform bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:shadow-xl hover:scale-105">
                                Sign In to Continue Reading
                            </a>
                            <p class="text-sm text-center text-gray-600 dark:text-gray-400">
                                Don't have an account? <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400">Start your free trial</a>
                            </p>
                        @endauth
                    </div>

                    <!-- Social Proof -->
                    <div class="pt-6 mt-6 text-center border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center mb-2 space-x-1">
                            @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            @endfor
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Join <span class="font-bold">10,000+ developers</span> who love our premium content
                        </p>
                    </div>
                </div>
            </div>
        @else
            <!-- Full Content for Free or Premium Subscribers -->
            {!! str($post->content)->markdown() !!}

            <!-- Google AdSense - In-Content Ad (After Article) -->
            @if(!auth()->check() || !auth()->user()->subscribed())
            <div class="my-12 p-4 bg-gray-50 dark:bg-slate-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-slate-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 text-center font-medium">Advertisement</p>
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.google.adsense.client_id') }}"
                        crossorigin="anonymous"></script>
                <!-- Display Ad -->
                <ins class="adsbygoogle"
                     style="display:block; text-align:center;"
                     data-ad-layout="in-article"
                     data-ad-format="fluid"
                     data-ad-client="{{ config('services.google.adsense.client_id') }}"
                     data-ad-slot="{{ config('services.google.adsense.slot_display') }}"></ins>
                <script>
                    // Only show ad if user consented or consent check not applied
                    if (window.adConsentGiven !== false) {
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    }
                </script>
            </div>
            @endif
        @endif
    </div>

    <!-- Google AdSense - Display Ad (Before Newsletter) - Respects Consent -->
    @if(!auth()->check() || !auth()->user()->subscribed())
    <div class="my-12 p-4 bg-gray-50 dark:bg-slate-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-slate-700">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 text-center font-medium">Advertisement</p>
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.google.adsense.client_id') }}"
                crossorigin="anonymous"></script>
        <!-- Responsive Display Ad -->
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-format="auto"
             data-ad-client="{{ config('services.google.adsense.client_id') }}"
             data-ad-slot="{{ config('services.google.adsense.slot_sidebar') }}"></ins>
        <script>
            // Only show ad if user consented or consent check not applied
            if (window.adConsentGiven !== false) {
                (adsbygoogle = window.adsbygoogle || []).push({});
            }
        </script>
    </div>
    @endif

    <!-- Newsletter CTA -->
    <div class="my-12 p-8 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
        <div class="max-w-2xl mx-auto text-center">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Never Miss an Article
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Get our best content delivered to your inbox weekly. No spam, unsubscribe anytime.
            </p>
            @livewire('newsletter-subscribe', ['compact' => true])
        </div>
    </div>

    <!-- Tags -->
    @if($post->tags->count() > 0)
    <div class="flex items-center mb-8 space-x-2">
        <span class="font-medium text-gray-700">Tags:</span>
        @foreach($post->tags as $tag)
        <a href="{{ route('posts.index', ['tag' => $tag->slug]) }}"
           class="px-3 py-1 text-sm text-gray-700 transition-colors bg-gray-100 rounded-full hover:bg-gray-200">
            {{ $tag->name }}
        </a>
        @endforeach
    </div>
    @endif

    <!-- Comments Section -->
    @if($post->allow_comments)
    <section class="pt-8 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Comments ({{ $comments->count() }})
            </h2>
            @auth
            <button wire:click="$set('showCommentForm', true)"
                    class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                Add Comment
            </button>
            @endauth
        </div>

        <!-- Comment Form -->
        @if($showCommentForm)
        <div class="p-6 mb-8 rounded-lg bg-gray-50">
            @if($replyingTo)
            <div class="p-3 mb-4 bg-white border-l-4 border-blue-500 rounded">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-900">Replying to {{ $replyingTo->user->name }}</span>
                    <button wire:click="cancelReply" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-gray-600 line-clamp-2">{{ $replyingTo->content }}</p>
            </div>
            @endif

            <form wire:submit="submitComment">
                <div class="mb-4">
                    <textarea wire:model="commentContent"
                              rows="4"
                              placeholder="Write your comment..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    @error('commentContent')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Your comment will be reviewed before being published.</p>
                    <div class="flex space-x-3">
                        <button type="button"
                                wire:click="$set('showCommentForm', false)"
                                class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                            Post Comment
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        <!-- Comments List -->
        <div class="space-y-6">
            @foreach($comments as $comment)
            <div class="flex space-x-4">
                <img src="{{ $comment->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name) }}"
                     alt="{{ $comment->user->name }}"
                     class="flex-shrink-0 w-10 h-10 rounded-full">
                <div class="flex-1">
                    <div class="p-4 rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900">{{ $comment->user->name }}</h4>
                            <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700">{{ $comment->content }}</p>
                    </div>

                    <div class="flex items-center mt-2 space-x-4">
                        <button wire:click="toggleCommentLike({{ $comment->id }})"
                                class="text-sm transition-colors {{ auth()->user()?->hasLiked($comment) ? 'text-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                            <svg class="inline w-4 h-4 mr-1" fill="{{ auth()->user()?->hasLiked($comment) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            {{ $comment->likes_count }}
                        </button>
                        @auth
                        <button wire:click="replyTo({{ $comment->id }})"
                                class="text-sm text-gray-500 transition-colors hover:text-gray-700">
                            Reply
                        </button>
                        @endauth
                    </div>

                    <!-- Replies -->
                    @if($comment->approvedReplies->count() > 0)
                    <div class="mt-4 space-y-4">
                        @foreach($comment->approvedReplies as $reply)
                        <div class="flex space-x-3">
                            <img src="{{ $reply->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($reply->user->name) }}"
                                 alt="{{ $reply->user->name }}"
                                 class="flex-shrink-0 w-8 h-8 rounded-full">
                            <div class="flex-1">
                                <div class="p-3 bg-white border border-gray-200 rounded-lg">
                                    <div class="flex items-center justify-between mb-1">
                                        <h5 class="text-sm font-medium text-gray-900">{{ $reply->user->name }}</h5>
                                        <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $reply->content }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @guest
        <div class="py-8 mt-8 text-center border-t border-gray-200">
            <p class="mb-4 text-gray-600">Please log in to leave a comment.</p>
            <a href="{{ route('login') }}"
               class="inline-flex items-center px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">
                Log In
            </a>
        </div>
        @endguest
    </section>
    @endif

    <!-- Related Posts -->
    @if($relatedPosts->count() > 0)
    <section class="pt-8 mt-12 border-t border-gray-200 dark:border-gray-700">
        <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Related Articles</h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach($relatedPosts as $relatedPost)
            <article class="group p-5 bg-gray-50 dark:bg-slate-900 rounded-xl transition-all duration-300 hover:-translate-y-2 hover:bg-white" style="border: 3px solid #616161; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.1);">
                <div class="mb-4 aspect-w-16 aspect-h-9">
                    @if($relatedPost->featured_image)
                    <img src="{{ $relatedPost->featured_image }}"
                         alt="{{ $relatedPost->title }}"
                         class="object-cover w-full h-32 transition-opacity rounded-lg group-hover:opacity-90">
                    @else
                    <div class="w-full h-32 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600"></div>
                    @endif
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 line-clamp-2">
                    <a href="{{ route('posts.show', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>
                </h3>
                @if($relatedPost->published_at)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $relatedPost->published_at->format('M j, Y') }}</p>
                @endif
            </article>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Recommended For You -->
    @if(auth()->check() && $recommendedPosts->count() > 0)
    <section class="pt-8 mt-12 border-t border-gray-200 dark:border-gray-700">
        <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">✨ Recommended For You</h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach($recommendedPosts as $recommendedPost)
            <article class="group p-5 bg-gray-50 dark:bg-slate-900 rounded-xl transition-all duration-300 hover:-translate-y-2 hover:bg-white" style="border: 3px solid #616161; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -2px rgba(0, 0, 0, 0.1);">
                <div class="mb-4 aspect-w-16 aspect-h-9">
                    @if($recommendedPost->featured_image)
                    <img src="{{ $recommendedPost->featured_image }}"
                         alt="{{ $recommendedPost->title }}"
                         class="object-cover w-full h-32 transition-opacity rounded-lg group-hover:opacity-90">
                    @else
                    <div class="w-full h-32 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600"></div>
                    @endif
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 line-clamp-2">
                    <a href="{{ route('posts.show', $recommendedPost->slug) }}">{{ $recommendedPost->title }}</a>
                </h3>
                @if($recommendedPost->published_at)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $recommendedPost->published_at->format('M j, Y') }}</p>
                @endif
            </article>
            @endforeach
        </div>
    </section>
    @endif

</div>

<script>
function shareMenu() {
    return {
        open: false,

        copyLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                this.showNotification('Link copied to clipboard!');
                this.open = false;
            }).catch(() => {
                this.showNotification('Failed to copy link', 'error');
            });
        },

        shareTwitter() {
            const title = document.querySelector('h1').textContent;
            const url = window.location.href;
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}&via=nextgenbeing`;
            window.open(twitterUrl, 'twitter-share', 'width=550,height=420');
            this.open = false;
        },

        shareLinkedIn() {
            const url = window.location.href;
            const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
            window.open(linkedinUrl, 'linkedin-share', 'width=550,height=420');
            this.open = false;
        },

        shareFacebook() {
            const url = window.location.href;
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            window.open(facebookUrl, 'facebook-share', 'width=550,height=420');
            this.open = false;
        },

        shareEmail() {
            const title = document.querySelector('h1').textContent;
            const url = window.location.href;
            const subject = encodeURIComponent(`Check out: ${title}`);
            const body = encodeURIComponent(`I found an interesting article: ${title}\n\nRead it here: ${url}`);
            window.location.href = `mailto:?subject=${subject}&body=${body}`;
            this.open = false;
        },

        showNotification(message, type = 'success') {
            // Create a simple toast notification
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white z-50 transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 2000);
        }
    };
}

function readingProgress() {
    return {
        progress: 0,

        init() {
            window.addEventListener('scroll', () => this.updateProgress());
        },

        updateProgress() {
            const windowHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = window.scrollY;
            this.progress = windowHeight > 0 ? (scrolled / windowHeight) * 100 : 0;
        }
    };
}
</script>
