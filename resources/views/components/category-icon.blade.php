@props(['category' => null, 'icon' => null, 'class' => 'w-5 h-5'])

@php
    $rawIcon = trim((string) ($icon ?? ($category ? $category->icon : '')));
    $slug = (string) ($category->slug ?? '');

    // Check if rawIcon is an emoji
    $isEmoji = false;
    if ($rawIcon !== '') {
        // Regex matches common emoji Unicode ranges
        $isEmoji = (bool) preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $rawIcon) || mb_strlen($rawIcon) <= 2;
    }

    if ($isEmoji) {
        $iconName = null;
    } elseif ($rawIcon !== '') {
        $iconName = match ($rawIcon) {
            'device-phone-mobile', 'phone', 'mobile' => 'device-mobile',
            'chart-bar-square', 'chart' => 'chart-bar',
            'server-stack' => 'server',
            default => $rawIcon,
        };
    } else {
        // Fallback by slug
        $iconName = match ($slug) {
            'web-development' => 'code',
            'artificial-intelligence', 'ai-workflows' => 'cpu',
            'mobile-development' => 'device-mobile',
            'devops' => 'server',
            'data-science' => 'chart-bar',
            'career-industry' => 'briefcase',
            'operating-systems' => 'terminal',
            'growth-distribution' => 'trending-up',
            default => 'tag',
        };
    }
@endphp

@if($isEmoji)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center text-xl leading-none']) }}>{{ $rawIcon }}</span>
@else
    <x-ui-icon :name="$iconName" :class="$class" {{ $attributes }} />
@endif
