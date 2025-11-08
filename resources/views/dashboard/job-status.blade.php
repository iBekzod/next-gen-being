@extends('layouts.app')

@section('title', 'Job Status Monitor - Dashboard')
@section('description', 'Monitor background jobs, track progress, and view processing status')

@section('content')
<section class="bg-slate-950 text-white">
    <div class="px-6 py-16 mx-auto max-w-7xl">
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold tracking-wide uppercase rounded-full bg-indigo-500/20 text-indigo-200">⚙️ Job Monitor</span>
        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">Job Status Monitor</h1>
        <p class="mt-4 text-base text-slate-300">Track background job processing, monitor progress, and view completion status.</p>
    </div>
</section>

<section class="bg-gray-50 dark:bg-slate-900">
    <div class="px-6 py-12 mx-auto max-w-7xl space-y-12">
        <!-- Job Statistics -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-6">
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Jobs</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalJobs }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                <p class="mt-2 text-3xl font-bold text-gray-600 dark:text-gray-400">{{ $pendingJobs }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Processing</p>
                <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $processingJobs }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Completed</p>
                <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $completedJobs }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Failed</p>
                <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">{{ $failedJobs }}</p>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate</p>
                <p class="mt-2 text-3xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($successRate, 1) }}%</p>
            </div>
        </div>

        <!-- Failed Jobs Alert -->
        @if($failedJobsList->isNotEmpty())
        <div class="rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">{{ $failedJobsList->count() }} Failed Job{{ $failedJobsList->count() !== 1 ? 's' : '' }}</h3>
                    <p class="mt-2 text-sm text-red-700 dark:text-red-400">
                        Some background jobs failed. Review the details below to troubleshoot.
                    </p>
                    <ul class="mt-3 space-y-2">
                        @foreach($failedJobsList as $job)
                        <li class="flex items-center gap-2 text-sm text-red-700 dark:text-red-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-600 dark:bg-red-400"></span>
                            <span class="font-medium">{{ ucfirst($job->type) }}</span> - {{ $job->error_message }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Processing Jobs with Progress -->
        @if($processingJobsList->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Currently Processing</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-slate-700">
                @foreach($processingJobsList as $job)
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($job->type) }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Job ID: {{ $job->job_id }} • Started {{ $job->started_at?->diffForHumans() }}
                            </p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                            Processing
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $job->progress_message ?? 'Processing...' }}
                            </p>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $job->progress }}%</span>
                        </div>
                        <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500" style="width: {{ $job->progress }}%"></div>
                        </div>
                    </div>

                    @if($job->metadata)
                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                        <dl class="grid grid-cols-2 gap-2">
                            @foreach($job->metadata as $key => $value)
                            <div>
                                <dt class="font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($key) }}:</dt>
                                <dd class="text-gray-600 dark:text-gray-400">{{ $value }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Job Types Distribution -->
        @if($jobsByType->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Jobs by Type</h2>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-slate-700">
                @foreach($jobsByType as $type)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($type->type) }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $type->completed_count }} of {{ $type->count }} completed
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $type->count }}</p>
                        <p class="text-sm text-green-600 dark:text-green-400">
                            {{ $type->count > 0 ? number_format(($type->completed_count / $type->count) * 100, 0) : 0 }}% done
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Jobs Table -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Jobs</h2>
            </div>

            @if($recentJobs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Type</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Progress</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Duration</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Created</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($recentJobs as $job)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ ucfirst($job->type) }}</td>
                            <td class="px-6 py-4">
                                @if($job->isPending())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    ⏳ Pending
                                </span>
                                @elseif($job->isProcessing())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                    ⚙️ Processing
                                </span>
                                @elseif($job->isCompleted())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                    ✓ Completed
                                </span>
                                @elseif($job->isFailed())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                    ✗ Failed
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500" style="width: {{ $job->progress }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ $job->progress }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                {{ $job->getFormattedDuration() ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-sm">
                                {{ $job->created_at->format('M d, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($job->isFailed() && $job->error_message)
                                <button type="button" onclick="toggleJobDetails(event, 'job-{{ $job->id }}')" class="text-red-600 dark:text-red-400 hover:underline text-sm font-medium">
                                    Error
                                </button>
                                @elseif($job->metadata)
                                <button type="button" onclick="toggleJobDetails(event, 'job-{{ $job->id }}')" class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium">
                                    View
                                </button>
                                @else
                                <span class="text-gray-500">—</span>
                                @endif
                            </td>
                        </tr>
                        @if($job->isFailed() && $job->error_message)
                        <tr class="hidden bg-red-50 dark:bg-red-950/30" id="job-{{ $job->id }}">
                            <td colspan="6" class="px-6 py-4">
                                <p class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">Error Message:</p>
                                <p class="font-mono text-sm text-red-700 dark:text-red-400 break-words">{{ $job->error_message }}</p>
                            </td>
                        </tr>
                        @elseif($job->metadata)
                        <tr class="hidden bg-gray-50 dark:bg-slate-700/50" id="job-{{ $job->id }}">
                            <td colspan="6" class="px-6 py-4">
                                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                                    @foreach($job->metadata as $key => $value)
                                    <div>
                                        <dt class="font-semibold text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                        <dd class="text-gray-600 dark:text-gray-400">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                                    </div>
                                    @endforeach
                                </dl>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($recentJobs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                {{ $recentJobs->links() }}
            </div>
            @endif
            @else
            <div class="px-6 py-12 text-center text-gray-600 dark:text-gray-400">
                No jobs to display
            </div>
            @endif
        </div>
    </div>
</section>

<script>
function toggleJobDetails(event, jobId) {
    event.preventDefault();
    const element = document.getElementById(jobId);
    element.classList.toggle('hidden');
}
</script>
@endsection
