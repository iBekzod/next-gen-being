<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NextGenBeing – Explore the Tech That Evolves You</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-950 text-white font-sans flex flex-col items-center justify-center min-h-screen p-6">
    <h1 class="text-4xl md:text-5xl font-bold mb-4 text-center">NextGenBeing</h1>
    <p class="text-lg md:text-xl text-gray-400 mb-8 text-center">Faceless reviews of tools that upgrade how you think, work, and live.</p>

    @if(session('success'))
        <div class="bg-green-600 text-white px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('landing.subscribe') }}" method="POST" class="w-full max-w-md flex flex-col gap-4">
        @csrf
        <input type="email" name="email" placeholder="Your email..." class="px-4 py-3 rounded bg-gray-800 border border-gray-700 focus:outline-none" required>
        @error('email')
            <span class="text-red-400 text-sm">{{ $message }}</span>
        @enderror
        <button type="submit" class="bg-blue-600 hover:bg-blue-500 px-4 py-3 rounded font-semibold transition">
            Subscribe
        </button>
    </form>

    <footer class="mt-16 text-sm text-gray-500 text-center">
        © {{ date('Y') }} NextGenBeing. All rights reserved.
    </footer>
</body>
</html>

