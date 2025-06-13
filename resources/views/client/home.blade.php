<!DOCTYPE html>
<html lang="en" class="antialiased text-gray-900 bg-white dark:bg-gray-950 dark:text-gray-100">
<head>
    <meta charset="UTF-8">
    <title>NextGenBeing — Blog</title>
    @vite('resources/css/app.css')
</head>
<body class="flex flex-col min-h-screen">

    <main class="w-full max-w-5xl px-4 py-10 mx-auto">
        <h1 class="mb-8 text-4xl font-extrabold tracking-tight text-center">
            Explore the Tech That Evolves You
        </h1>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            @foreach ($blogs as $blog)
                <div class="transition bg-white rounded-lg shadow group dark:bg-gray-900 hover:shadow-lg">
                    @if ($blog->thumbnail)
                        <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}"
                             class="object-cover w-full h-48 rounded-t-lg">
                    @endif
                    <div class="p-5">
                        <h2 class="mb-2 text-xl font-semibold transition group-hover:text-blue-500">
                            {{ $blog->title }}
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $blog->created_at->format('M d, Y') }}
                        </p>
                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 line-clamp-3">
                            {{ $blog->excerpt }}
                        </p>
                        <a href="{{ route('blog.show', $blog->slug) }}"
                           class="inline-block mt-4 text-sm text-blue-500 hover:underline">Read more →</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $blogs->links() }}
        </div>
    </main>
</body>
</html>
