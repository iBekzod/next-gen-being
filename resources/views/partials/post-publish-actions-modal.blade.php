<!-- Post Publish Actions Modal -->
<div id="postPublishModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full border border-gray-200 dark:border-slate-700 animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="relative px-8 py-6 border-b border-gray-200 dark:border-slate-700">
            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition"
                    onclick="closePublishModal()">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-500/20 mb-4">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Post Published!</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Your post is now live. What would you like to do next?</p>
            </div>
        </div>

        <!-- Actions Grid -->
        <div class="p-8 space-y-3">
            <!-- Generate Video Action -->
            <button class="w-full p-4 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 transition group"
                    onclick="handleVideoGeneration(event)">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 group-hover:bg-blue-200 dark:group-hover:bg-blue-500/30 transition">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Generate Video</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Convert your post to an engaging video</p>
                    </div>
                </div>
            </button>

            <!-- Social Media Action -->
            <button class="w-full p-4 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-purple-500 dark:hover:border-purple-400 transition group"
                    onclick="handleSocialMediaPublish(event)">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 group-hover:bg-purple-200 dark:group-hover:bg-purple-500/30 transition">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Share on Social Media</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Post to your connected platforms</p>
                    </div>
                </div>
            </button>

            <!-- Analytics Action -->
            <button class="w-full p-4 rounded-lg border-2 border-gray-200 dark:border-slate-700 hover:border-amber-500 dark:hover:border-amber-400 transition group"
                    onclick="handleViewAnalytics(event)">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-amber-100 dark:bg-amber-500/20 group-hover:bg-amber-200 dark:group-hover:bg-amber-500/30 transition">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">View Analytics</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Check views, engagement and performance</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- Footer -->
        <div class="px-8 py-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50 rounded-b-2xl">
            <button class="w-full px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-600 rounded-lg transition"
                    onclick="closePublishModal()">
                Do this later
            </button>
        </div>
    </div>
</div>

<!-- Modal Styles -->
<style>
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-in {
        animation: slideUp 0.3s ease-out;
    }
</style>

<!-- Modal Scripts -->
<script>
function showPublishModal() {
    const modal = document.getElementById('postPublishModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closePublishModal() {
    const modal = document.getElementById('postPublishModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function handleVideoGeneration(e) {
    e.preventDefault();
    // Show loading state
    const postId = document.getElementById('post_id')?.value || document.querySelector('[name="post_id"]')?.value;
    const postTitle = document.getElementById('title')?.value || document.querySelector('[name="title"]')?.value;

    if (!postId) {
        alert('Post ID not found. Please refresh the page and try again.');
        return;
    }

    // Close modal
    closePublishModal();

    // Show notification
    showNotification('✨ Video generation started!', 'Your post is being converted to a video. You\'ll be notified when it\'s ready.');

    // Trigger API call to start video generation
    fetch(`/api/videos/generate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            post_id: postId,
            post_title: postTitle
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('✨ Video generation in progress', 'Check your dashboard for updates.');
        }
    })
    .catch(error => console.error('Video generation error:', error));
}

function handleSocialMediaPublish(e) {
    e.preventDefault();
    const postId = document.getElementById('post_id')?.value || document.querySelector('[name="post_id"]')?.value;

    if (!postId) {
        alert('Post ID not found. Please refresh the page and try again.');
        return;
    }

    closePublishModal();
    showNotification('📱 Preparing to share', 'Select which platforms you\'d like to share on.');

    // In a real implementation, this would open a platform selection modal
    // For now, we'll just log it
    console.log('Social media publish for post:', postId);
}

function handleViewAnalytics(e) {
    e.preventDefault();
    const postId = document.getElementById('post_id')?.value || document.querySelector('[name="post_id"]')?.value;

    if (!postId) {
        alert('Post ID not found. Please refresh the page and try again.');
        return;
    }

    closePublishModal();
    // Redirect to analytics view (you'll need to implement the analytics route)
    window.location.href = `/dashboard/analytics?post=${postId}`;
}

function showNotification(title, message) {
    // Create a simple notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-white dark:bg-slate-800 rounded-lg shadow-lg p-4 max-w-sm border border-gray-200 dark:border-slate-700 z-40 animate-in';
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">${title}</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${message}</p>
            </div>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}
</script>
