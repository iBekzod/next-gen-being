<!DOCTYPE html>
<html lang="en" class="antialiased text-gray-900 bg-white dark:bg-gray-950 dark:text-gray-100">
<head>
    <meta charset="UTF-8">
    <title>{{ $blog->title }} | NextGenBeing</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen">

    <main class="w-full max-w-3xl px-4 py-12 mx-auto">

        <a href="{{ route('home') }}" class="inline-block mb-6 text-sm text-blue-500 hover:underline">
            ← Back to articles
        </a>

        <h1 class="mb-4 text-3xl font-bold leading-tight md:text-4xl">
            {{ $blog->title }}
        </h1>

        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            {{ $blog->created_at->format('F d, Y') }} • {{ ceil(str_word_count(strip_tags($blog->content)) / 200) }} min read
        </p>

        @if ($blog->thumbnail)
            <div class="mb-8">
                <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}"
                     class="w-full h-auto shadow rounded-xl">
            </div>
        @endif

        <div class="prose dark:prose-invert max-w-none prose-headings:text-gray-900 dark:prose-headings:text-white prose-img:rounded-lg prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-strong:font-semibold prose-code:text-pink-500">
            {!! $blog->content !!}
        </div>

    </main>
</body>
</html>
