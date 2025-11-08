<div class="space-y-10">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="relative flex-1">
            <svg class="absolute w-5 h-5 text-slate-400 left-4 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M18 11a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input
                type="search"
                wire:model.debounce.400ms="query"
                placeholder="Search articles, frameworks, or authors..."
                class="w-full py-4 pl-12 pr-12 text-sm bg-white border rounded-2xl border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-900/70 dark:border-slate-700 dark:text-slate-100"
            >
            @if($query)
                <button type="button" wire:click="$set('query','')"
                    class="absolute text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white right-4 top-1/2 -translate-y-1/2">
                    Clear
                </button>
            @endif
        </div>
        <div class="text-sm text-slate-500 dark:text-slate-400">
            <p class="font-medium text-slate-700 dark:text-slate-200">Search tips</p>
            <ul class="flex flex-wrap gap-3 mt-2 text-xs">
                <li class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Use quotes for exact matches</li>
                <li class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Filter by category or author</li>
            </ul>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200/60 dark:border-slate-700 pb-2">
        <button wire:click="$set('searchType', 'posts')"
            class="px-4 py-2 text-sm font-semibold rounded-full transition-colors border {{ $searchType === 'posts' ? 'border-blue-500 bg-blue-500 text-white shadow-sm' : 'border-transparent bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
            Articles ({{ $postsCount }})
        </button>
        <button wire:click="$set('searchType', 'authors')"
            class="px-4 py-2 text-sm font-semibold rounded-full transition-colors border {{ $searchType === 'authors' ? 'border-blue-500 bg-blue-500 text-white shadow-sm' : 'border-transparent bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700' }}">
            Authors ({{ $authorsCount }})
        </button>
    </div>

    <div class="space-y-6">
        @if($searchType === 'posts')
            @forelse($posts as $post)
                <article class="p-6 transition border rounded-2xl border-slate-200/70 hover:-translate-y-1 hover:shadow-xl dark:border-slate-700/60 bg-white dark:bg-slate-900/60">
                    <div class="flex flex-wrap items-center gap-3 mb-3 text-xs font-medium tracking-wide text-slate-500 uppercase">
                        @php
                            $colorMap = [
                                'red' => '#fee2e2',
                                'blue' => '#dbeafe',
                                'green' => '#dcfce7',
                                'purple' => '#f3e8ff',
                                'yellow' => '#fef3c7',
                                'pink' => '#fce7f3',
                                'indigo' => '#e0e7ff',
                                'teal' => '#ccfbf1',
                            ];
                            $color = $post->category->color ?? 'blue';
                            $bgColor = $colorMap[$color] ?? $colorMap['blue'];
                        @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-slate-800 rounded-full dark:text-slate-100"
                              style="background-color: {{ $bgColor }}; opacity: 0.8;">
                            {{ $post->category->name }}
                        </span>
                        <span>{{ $post->read_time }} min read</span>
                        <span>{{ $post->published_at->format('M j, Y') }}</span>
                    </div>
                    <h2 class="text-xl font-semibold leading-tight text-slate-900 dark:text-white">
                        <a href="{{ route('posts.show', $post->slug) }}" class="transition hover:text-blue-600 dark:hover:text-blue-400">{{ $post->title }}</a>
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300 line-clamp-3">{{ $post->excerpt }}</p>
                    <div class="flex flex-wrap items-center justify-between gap-4 mt-6 text-sm text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <img src="{{ $post->author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($post->author->name) }}" alt="{{ $post->author->name }}" class="w-8 h-8 rounded-full">
                            <div class="leading-tight">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $post->author->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $post->author->role ?? 'Contributor' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 text-xs">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l2.5 2.5"/></svg>
                                {{ $post->read_time }}m
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5S13.657 14 12 14s-3 .672-3 1.5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v1m0 12v1m7-7h1M4 12H3m15.364 5.364l.707.707M5.929 6.343l-.707-.707"/></svg>
                                {{ number_format($post->likes_count) }} likes
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-3-3H8a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v6a2 2 0 01-2 2h-3l-3 3z"/></svg>
                                {{ number_format($post->comments_count) }} comments
                            </span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="py-16 text-center border border-dashed rounded-3xl border-slate-300/70 dark:border-slate-700/60">
                    <svg class="w-12 h-12 mx-auto text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M18 11a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">No articles found</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Adjust the filters or try a different keyword.</p>
                </div>
            @endforelse

            @if($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div>
                    {{ $posts->onEachSide(1)->links() }}
                </div>
            @endif
        @endif

        @if($searchType === 'authors')
            @forelse($authors as $author)
                <div class="flex flex-col gap-4 p-6 transition border rounded-2xl border-slate-200/70 hover:-translate-y-1 hover:shadow-xl md:flex-row md:items-center dark:border-slate-700/60 bg-white dark:bg-slate-900/60">
                    <img src="{{ $author->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($author->name) }}" alt="{{ $author->name }}" class="w-20 h-20 rounded-full">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $author->name }}</h3>
                            <span class="text-sm text-slate-500 dark:text-slate-400">Joined {{ $author->created_at->format('M Y') }}</span>
                        </div>
                        @if($author->bio)
                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($author->bio, 150) }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-slate-500 dark:text-slate-400">
                            <span>{{ $author->posts_count }} articles</span>
                            @if($author->website)
                                <a href="{{ $author->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Website
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5h6m0 0v6m0-6L10 14"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h6m-6 0v-6"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center border border-dashed rounded-3xl border-slate-300/70 dark:border-slate-700/60">
                    <svg class="w-12 h-12 mx-auto text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">No authors found</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Try a different search or adjust the filters.</p>
                </div>
            @endforelse

            @if($authors instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div>
                    {{ $authors->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
