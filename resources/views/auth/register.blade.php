@extends('layouts.app')

@section('title', 'Create account - ' . setting('site_name'))
@section('description', 'Register to save intelligence drops and manage your NextGenBeing workspace')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto text-center max-w-3xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-blue-500/20 text-blue-200">Create your workspace</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Join NextGenBeing</h1>
        <p class="mt-4 text-base text-slate-300">Get access to premium briefings, bookmark your playbooks, and keep your intelligence stack in sync.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-16 mx-auto max-w-3xl">
        <div class="max-w-md mx-auto p-8 bg-white rounded-2xl shadow-xl border border-slate-200 dark:bg-slate-900 dark:border-slate-700">
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus
                           class="w-full px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700">
                    @error('name')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-slate-700 dark:text-slate-200">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="w-full px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700">
                    @error('email')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-slate-700 dark:text-slate-200">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700">
                    @error('password')
                        <p class="text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password-confirm" class="text-sm font-medium text-slate-700 dark:text-slate-200">Confirm password</label>
                    <input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:bg-slate-800 dark:text-white dark:border-slate-700">
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 text-sm font-semibold text-white transition rounded-xl bg-blue-500 hover:bg-blue-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500">
                    Create account
                </button>
            </form>

            @include('auth.partials.social-login-buttons', ['title' => 'Or sign up with'])

            <p class="mt-8 text-sm text-center text-slate-500 dark:text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">Sign in</a>
            </p>
        </div>
    </div>
</section>
@endsection
