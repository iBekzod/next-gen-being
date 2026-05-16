@extends('layouts.app')

@section('title', $path->name . ' · Learning Path')
@section('description', \Illuminate\Support\Str::limit($path->description ?? $path->goal, 160))

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Course',
    'name' => $path->name,
    'description' => $path->description,
    'provider' => ['@type' => 'Organization', 'name' => 'NextGenBeing', 'sameAs' => url('/')],
    'educationalLevel' => $path->skill_level,
    'timeRequired' => 'PT' . $path->estimated_duration_hours . 'H',
    'hasCourseInstance' => collect($path->items)->map(fn($item) => [
        '@type' => 'CourseInstance',
        'courseMode' => 'online',
        'name' => $item->title,
    ])->values(),
], JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-5xl">
        <a href="{{ route('learning-paths.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-blue-200 hover:text-white">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All paths
        </a>
        <div class="mt-6">
            <span class="px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full bg-blue-500/20 text-blue-200">{{ $path->skill_level }}</span>
            <h1 class="mt-4 text-4xl font-bold tracking-tight">{{ $path->name }}</h1>
            @if($path->description)
            <p class="mt-4 text-lg text-slate-300">{{ $path->description }}</p>
            @endif
            <dl class="mt-6 flex flex-wrap gap-6 text-sm">
                <div>
                    <dt class="text-slate-400 uppercase text-xs tracking-wide">Parts</dt>
                    <dd class="font-semibold text-white">{{ $path->items->count() }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 uppercase text-xs tracking-wide">Estimated time</dt>
                    <dd class="font-semibold text-white">~{{ $path->estimated_duration_hours }} hours</dd>
                </div>
                <div>
                    <dt class="text-slate-400 uppercase text-xs tracking-wide">Curated by</dt>
                    <dd class="font-semibold text-white">{{ $path->user->name }}</dd>
                </div>
            </dl>
            @if($path->goal)
            <div class="mt-6 p-4 rounded-lg bg-white/5 border border-white/10">
                <p class="text-sm text-slate-300"><strong class="text-white">Goal:</strong> {{ $path->goal }}</p>
            </div>
            @endif
        </div>
    </div>
</section>

<div class="bg-gray-50 dark:bg-slate-900 py-12">
    <div class="px-6 mx-auto max-w-5xl space-y-3">
        @foreach($path->items as $item)
        <a href="{{ route('posts.show', $item->post->slug) }}"
           class="block p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-bold text-sm">
                    {{ $item->order }}
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $item->title }}</h3>
                    @if($item->description)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $item->description }}</p>
                    @endif
                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                        @if($item->post->category)
                        <span>{{ $item->post->category->name }}</span>
                        <span>·</span>
                        @endif
                        <span>{{ $item->estimated_duration_minutes }} min read</span>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
