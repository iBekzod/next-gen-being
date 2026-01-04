@props(['post'])

@php
    $currentLanguage = $post->base_language;
    $isTranslation = (bool) $post->base_post_id;
    $basePost = $isTranslation ? $post->basePost : $post;

    if (!$basePost) {
        return;
    }

    $translations = $basePost->translatedVersions()->get();
    $allVersions = collect([$basePost])->concat($translations);
@endphp

@if ($allVersions->count() > 1)
<div class="flex items-center justify-center space-x-2 mb-4">
    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
        Available Languages:
    </span>
    <div class="flex flex-wrap gap-2">
        @foreach ($allVersions as $version)
            @php
                $langCode = strtoupper($version->base_language);
                $langNames = [
                    'EN' => 'English',
                    'ES' => 'Español',
                    'FR' => 'Français',
                    'DE' => 'Deutsch',
                    'ZH' => '中文',
                    'PT' => 'Português',
                    'IT' => 'Italiano',
                    'JA' => '日本語',
                    'RU' => 'Русский',
                    'KO' => '한국어',
                ];
                $langName = $langNames[$langCode] ?? $langCode;
                $isCurrent = $version->id === $post->id;
            @endphp

            @if ($isCurrent)
                <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900 px-3 py-1.5 text-sm font-medium text-blue-800 dark:text-blue-200">
                    {{ $langName }}
                    <svg class="ml-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </span>
            @else
                <a href="{{ route('posts.show', $version) }}"
                   class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    {{ $langName }}
                </a>
            @endif
        @endforeach
    </div>
</div>
@endif
