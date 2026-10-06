@extends('layouts.app')

@section('html_lang', 'uz')
@section('title', "O'zbekcha maqolalar: AI vositalar, freelance va masofaviy ish - " . setting('site_name'))
@section('description', "AI vositalar, ovoz va video, freelance daromad va masofaviy ish bo'yicha o'zbek tilidagi amaliy qo'llanmalar: bosqichma-bosqich, real narxlar va O'zbekistondan ishlaydigan to'lov yo'llari bilan.")
@section('keywords', "AI vositalar o'zbekcha, sun'iy intellekt qo'llanma, freelance O'zbekiston, ElevenLabs o'zbekcha, AI bilan pul topish")
@section('canonical', route('uz.index'))

@push('head')
<link rel="alternate" hreflang="uz" href="{{ route('uz.index') }}">
<link rel="alternate" hreflang="en" href="{{ route('posts.index') }}">
<link rel="alternate" hreflang="x-default" href="{{ route('posts.index') }}">
@endpush

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => "O'zbekcha maqolalar",
    'inLanguage' => 'uz',
    'url' => route('uz.index'),
    'mainEntity' => [
        '@type' => 'ItemList',
        'itemListElement' => collect($posts->items())->values()->map(fn ($p, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => route('posts.show', $p->slug),
            'name' => $p->title,
        ])->all(),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-4 sm:px-6 lg:px-8 py-14 mx-auto max-w-5xl">
        <span class="inline-block px-3 py-1 mb-4 text-xs font-semibold tracking-wide uppercase rounded-full bg-white/10">O'zbek tilida</span>
        <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">O'zbekcha maqolalar</h1>
        <p class="mt-4 text-lg text-slate-300 max-w-2xl">AI vositalar, ovoz va video, freelance va masofaviy ish bo'yicha amaliy qo'llanmalar: real narxlar, bosqichma-bosqich ko'rsatmalar va O'zbekistondan turib ishlaydigan to'lov yo'llari bilan.</p>
    </div>
</section>

<section class="py-12 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-slate-900">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($posts as $post)
                <article class="bg-white dark:bg-slate-800 rounded-lg shadow hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
                    @if($post->featured_image)
                        <a href="{{ route('posts.show', $post->slug) }}">
                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover" loading="lazy">
                        </a>
                    @endif
                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex items-center gap-2 mb-3 text-xs text-gray-500 dark:text-gray-400">
                            @if($post->category)
                                <span class="px-2 py-1 font-semibold uppercase rounded bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-100">{{ $post->category->name }}</span>
                            @endif
                            @if($post->read_time)
                                <span>{{ $post->read_time }} daqiqa o'qish</span>
                            @endif
                        </div>
                        <h2 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">
                            <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $post->title }}</a>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3 flex-1">{{ $post->excerpt }}</p>
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-700 text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ optional($post->published_at)->format('d.m.Y') }}</span>
                            <a href="{{ route('posts.show', $post->slug) }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">O'qish &rarr;</a>
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full text-center py-12 text-gray-600 dark:text-gray-400">Hozircha o'zbekcha maqola yo'q.</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $posts->links() }}</div>

        <p class="mt-10 text-sm text-gray-500 dark:text-gray-400">
            Ba'zi maqolalarda hamkorlik havolalari bor: ular orqali obuna bo'lsangiz, narx o'zgarmaydi, biz kichik komissiya olamiz.
            <a href="{{ route('affiliate.disclosure') }}#uz" class="underline">Batafsil</a>.
        </p>
    </div>
</section>
@endsection
