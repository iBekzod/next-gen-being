@props(['href' => null, 'type' => 'button', 'variant' => 'primary', 'size' => 'md', 'rounded' => true, 'icon' => null, 'disabled' => false, 'loading' => false])

@php
    $baseClasses = 'inline-flex items-center gap-2 font-medium transition-all duration-200 cursor-pointer';

    $variantClasses = match($variant) {
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow-md',
        'secondary' => 'bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-600',
        'success' => 'bg-green-600 hover:bg-green-700 text-white shadow-sm hover:shadow-md',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-sm hover:shadow-md',
        'warning' => 'bg-amber-600 hover:bg-amber-700 text-white shadow-sm hover:shadow-md',
        'ghost' => 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700',
        'outline' => 'border-2 border-gray-300 dark:border-slate-600 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-slate-800',
        default => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow-md',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2 text-sm',
    };

    $roundedClass = $rounded ? 'rounded-lg' : '';
    $disabledClass = $disabled || $loading ? 'opacity-50 cursor-not-allowed' : '';

    $element = $href ? 'a' : 'button';
@endphp

@if($element === 'a')
<a href="{{ $href }}"
   {{ $attributes->merge(['class' => "$baseClasses $variantClasses $sizeClasses $roundedClass $disabledClass"]) }}>
@else
<{{ $element }} type="{{ $type }}"
   {{ $disabled || $loading ? 'disabled' : '' }}
   {{ $attributes->merge(['class' => "$baseClasses $variantClasses $sizeClasses $roundedClass $disabledClass"]) }}>
@endif

    @if($loading)
    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    @elseif($icon)
    <span class="text-base">{{ $icon }}</span>
    @endif

    {{ $slot }}

@if($element === 'a')
</a>
@else
</{{ $element }}>
@endif
