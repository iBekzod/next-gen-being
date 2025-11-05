<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Earning Type</h3>
            <p class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->type === 'follower_milestone') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                    @elseif($record->type === 'premium_content') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                    @elseif($record->type === 'engagement_bonus') bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400
                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400
                    @endif">
                    {{ match($record->type) {
                        'follower_milestone' => 'Follower Milestone',
                        'premium_content' => 'Premium Content',
                        'engagement_bonus' => 'Engagement Bonus',
                        'manual_adjustment' => 'Manual Adjustment',
                        default => ucwords(str_replace('_', ' ', $record->type))
                    } }}
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Amount</h3>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                ${{ number_format($record->amount, 2) }}
            </p>
        </div>
    </div>

    @if($record->type === 'follower_milestone' && $record->milestone_value)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Milestone Achieved</h3>
        <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
            🎉 {{ number_format($record->milestone_value) }} Followers
        </p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Congratulations on reaching this follower milestone!
        </p>
    </div>
    @endif

    @if($record->post)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Related Post</h3>
        <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $record->post->title }}</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ \Str::limit($record->post->excerpt, 100) }}</p>
            <a href="{{ route('posts.show', $record->post->slug) }}" target="_blank" class="mt-2 inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:underline">
                View Post →
            </a>
        </div>
    </div>
    @endif

    @if($record->description)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</h3>
        <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $record->description }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</h3>
            <p class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                    @elseif($record->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                    @else bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                    @endif">
                    @if($record->status === 'pending')
                        ⏳ Pending
                    @elseif($record->status === 'paid')
                        ✅ Paid
                    @else
                        ❌ Cancelled
                    @endif
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Earned On</h3>
            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $record->created_at->format('M d, Y') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->created_at->diffForHumans() }}</p>
        </div>

        @if($record->paid_at)
        <div>
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Paid On</h3>
            <p class="mt-1 text-gray-900 dark:text-gray-100">{{ $record->paid_at->format('M d, Y') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $record->paid_at->diffForHumans() }}</p>
        </div>
        @endif
    </div>

    @if($record->status === 'pending')
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Payment Pending</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>This earning is pending payment. Earnings are typically processed within 30 days and must reach the minimum payout threshold of $50.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($record->payment_method)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</h3>
        <p class="mt-1 text-gray-900 dark:text-gray-100">{{ ucwords(str_replace('_', ' ', $record->payment_method)) }}</p>
    </div>
    @endif

    @if($record->transaction_id)
    <div>
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Transaction ID</h3>
        <p class="mt-1 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $record->transaction_id }}</p>
    </div>
    @endif
</div>
