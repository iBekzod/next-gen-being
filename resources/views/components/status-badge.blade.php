@props(['status' => 'pending', 'icon' => null])

@php
    $statusConfig = match($status) {
        'pending' => [
            'bg' => 'bg-amber-100 dark:bg-amber-900/30',
            'text' => 'text-amber-800 dark:text-amber-300',
            'icon' => '⏳',
            'label' => 'Pending',
        ],
        'processing' => [
            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'text' => 'text-blue-800 dark:text-blue-300',
            'icon' => '⚙️',
            'label' => 'Processing',
        ],
        'completed' => [
            'bg' => 'bg-green-100 dark:bg-green-900/30',
            'text' => 'text-green-800 dark:text-green-300',
            'icon' => '✓',
            'label' => 'Completed',
        ],
        'failed' => [
            'bg' => 'bg-red-100 dark:bg-red-900/30',
            'text' => 'text-red-800 dark:text-red-300',
            'icon' => '✗',
            'label' => 'Failed',
        ],
        'draft' => [
            'bg' => 'bg-gray-100 dark:bg-slate-700',
            'text' => 'text-gray-800 dark:text-gray-300',
            'icon' => '📝',
            'label' => 'Draft',
        ],
        'published' => [
            'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
            'text' => 'text-emerald-800 dark:text-emerald-300',
            'icon' => '📢',
            'label' => 'Published',
        ],
        'active' => [
            'bg' => 'bg-green-100 dark:bg-green-900/30',
            'text' => 'text-green-800 dark:text-green-300',
            'icon' => '●',
            'label' => 'Active',
        ],
        'inactive' => [
            'bg' => 'bg-gray-100 dark:bg-slate-700',
            'text' => 'text-gray-800 dark:text-gray-300',
            'icon' => '●',
            'label' => 'Inactive',
        ],
        default => [
            'bg' => 'bg-gray-100 dark:bg-slate-700',
            'text' => 'text-gray-800 dark:text-gray-300',
            'icon' => '●',
            'label' => ucfirst($status),
        ],
    };

    $displayIcon = $icon ?? $statusConfig['icon'];
@endphp

<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
    <span class="text-sm">{{ $displayIcon }}</span>
    {{ $statusConfig['label'] }}
</span>
