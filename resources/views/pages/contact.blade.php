@extends('layouts.app')

@section('title', 'Contact Us - ' . setting('site_name'))
@section('description', 'Get in touch with the NextGenBeing team. We\'re here to help with questions, feedback, partnerships, and support.')
@section('canonical', route('contact'))
@section('robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Contact Us</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Have a question, feedback, or want to collaborate? We'd love to hear from you.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Contact Information -->
            <div class="lg:col-span-1">
                <div class="space-y-8">
                    <!-- Email -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Email</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                <a href="mailto:info@nextgenbeing.com" class="hover:text-blue-600 dark:hover:text-blue-400">info@nextgenbeing.com</a>
                            </p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Address</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ setting('company_address', '240 Bogishamol Street, Yunusabad District, Tashkent 100058, Uzbekistan') }}
                            </p>
                        </div>
                    </div>

                    <!-- Response Time -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Response Time</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                We typically respond within 24-48 hours during business days.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 sm:p-8">
                    @livewire('contact-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
