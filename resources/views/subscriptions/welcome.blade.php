@extends('layouts.app')

@section('title', 'Thank you - ' . setting('site_name', config('app.name', 'NextGen Being')))
@section('description', 'Your subscription is being set up')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto text-center max-w-3xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-emerald-500/20 text-emerald-200">Thank you</span>
        <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl">Your subscription is on its way.</h1>
        <p class="mt-4 text-base text-slate-300">We are linking it to your account now.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-3xl">
        <div class="p-8 text-center bg-white border border-slate-200 rounded-3xl shadow-xl dark:bg-slate-900/70 dark:border-slate-700">
            <h2 class="text-2xl font-semibold text-slate-900 dark:text-white">Next step: check your email</h2>
            <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">Use the same email you paid with. If you are new here, we send a link to set your password. If you already have an account, just sign in with it and your plan is already attached.</p>
            <div class="flex flex-col justify-center gap-3 mt-8 sm:flex-row">
                <a href="{{ route('login') }}" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Sign in</a>
                <a href="{{ route('password.request') }}" class="px-5 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 dark:bg-slate-800 dark:text-blue-300">Set or reset password</a>
            </div>
        </div>
    </div>
</section>
@endsection
