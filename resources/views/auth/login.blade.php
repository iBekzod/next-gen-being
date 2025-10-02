@extends('layouts.app')

@section('title', 'Sign in - ' . setting('site_name'))
@section('description', 'Access your NextGenBeing dashboard and saved intelligence drops')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto text-center max-w-3xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Welcome back</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Sign in to your workspace</h1>
        <p class="mt-4 text-base text-slate-300">Continue where you left off--publish faster, refine your stacks, and keep your saved signals in sync.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-16 mx-auto max-w-3xl">
        <div class="max-w-md mx-auto p-8 bg-white rounded-2xl shadow-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-slate-700 dark:text-slate-200">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                           class="w-full px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700">
                    @error('email')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-slate-700 dark:text-slate-200">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700">
                    @error('password')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300">
                        <input class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300" href="{{ route('password.request') }}">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 text-sm font-semibold text-white transition rounded-xl bg-blue-500 hover:bg-blue-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500">
                    Sign in
                </button>
            </form>

            <p class="mt-8 text-sm text-center text-slate-500 dark:text-slate-400">
                New to NextGenBeing?
                <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Create an account</a>
            </p>
        </div>
    </div>
</section>
@endsection

