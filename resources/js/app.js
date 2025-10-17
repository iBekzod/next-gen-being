
import './bootstrap';

// Livewire v3 includes Alpine.js - we'll configure it after Livewire loads
document.addEventListener('livewire:init', () => {
    // Access Livewire's Alpine instance
    const Alpine = window.Alpine || window.Livewire.Alpine;

    // Alpine.js store for global state
    Alpine.store('app', {
        loading: false,
        darkMode: (() => {
            const stored = localStorage.getItem('theme') ?? localStorage.getItem('darkMode');

            if (!stored) {
                return false;
            }

            return stored === 'dark' || stored === 'true';
        })(),

        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
            localStorage.removeItem('darkMode');

            document.documentElement.classList.toggle('dark', this.darkMode);
        },

        showNotification(message, type = 'info') {
            // Trigger notification event
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { message, type }
            }));
        }
    });

    // Reading Progress Indicator
    window.readingProgress = function() {
        return {
            progress: 0,
            init() {
                this.updateProgress();
                window.addEventListener('scroll', () => this.updateProgress());
                window.addEventListener('resize', () => this.updateProgress());
            },
            updateProgress() {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                this.progress = (winScroll / height) * 100;
            }
        };
    };

    // Social Sharing Functions with Tracking
    window.trackShare = async function(platform, postId = null) {
        // Get post ID from data attribute if not provided
        if (!postId) {
            const postElement = document.querySelector('[data-post-id]');
            postId = postElement ? postElement.getAttribute('data-post-id') : null;
        }

        if (!postId) {
            console.warn('Post ID not found for share tracking');
            return;
        }

        try {
            const response = await fetch('/api/social-share/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({
                    platform: platform,
                    post_id: postId,
                    url: window.location.href
                })
            });

            const data = await response.json();

            // Track in GA4 if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'share', {
                    method: platform,
                    content_type: 'article',
                    content_id: postId
                });
            }

            return data;
        } catch (error) {
            console.error('Share tracking failed:', error);
        }
    };

    window.copyLink = async function() {
        const url = window.location.href;
        try {
            await navigator.clipboard.writeText(url);
            await window.trackShare('copy');
            Alpine.store('app').showNotification('Link copied to clipboard!', 'success');
        } catch (error) {
            Alpine.store('app').showNotification('Failed to copy link', 'error');
        }
    };

    window.shareTwitter = async function() {
        await window.trackShare('twitter');

        const url = window.location.href;
        const title = document.querySelector('h1')?.textContent || document.title;
        const twitterUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
        window.open(twitterUrl, '_blank', 'width=550,height=420');
    };

    window.shareLinkedIn = async function() {
        await window.trackShare('linkedin');

        const url = window.location.href;
        const linkedInUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
        window.open(linkedInUrl, '_blank', 'width=550,height=420');
    };

    window.shareFacebook = async function() {
        await window.trackShare('facebook');

        const url = window.location.href;
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        window.open(facebookUrl, '_blank', 'width=550,height=420');
    };

    window.shareWhatsApp = async function() {
        await window.trackShare('whatsapp');

        const url = window.location.href;
        const title = document.querySelector('h1')?.textContent || document.title;
        const text = encodeURIComponent(`${title} ${url}`);
        const whatsappUrl = `https://wa.me/?text=${text}`;
        window.open(whatsappUrl, '_blank');
    };

    window.shareTelegram = async function() {
        await window.trackShare('telegram');

        const url = window.location.href;
        const title = document.querySelector('h1')?.textContent || document.title;
        const text = encodeURIComponent(title);
        const telegramUrl = `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${text}`;
        window.open(telegramUrl, '_blank');
    };

    window.shareEmail = async function() {
        await window.trackShare('email');

        const url = window.location.href;
        const title = document.querySelector('h1')?.textContent || document.title;
        const subject = encodeURIComponent(`Check out: ${title}`);
        const body = encodeURIComponent(`I thought you might be interested in this article:\n\n${title}\n\n${url}`);
        window.location.href = `mailto:?subject=${subject}&body=${body}`;
    };

    // Global utilities
    window.utils = {
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                Alpine.store('app').showNotification('Copied to clipboard!', 'success');
            }).catch(() => {
                Alpine.store('app').showNotification('Failed to copy', 'error');
            });
        },

        formatNumber(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            }
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toString();
        },

        timeAgo(date) {
            const now = new Date();
            const diff = Math.floor((now - new Date(date)) / 1000);

            if (diff < 60) return 'just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            if (diff < 2592000) return Math.floor(diff / 86400) + 'd ago';
            if (diff < 31536000) return Math.floor(diff / 2592000) + 'mo ago';
            return Math.floor(diff / 31536000) + 'y ago';
        },

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize dark mode on page load
    if (Alpine.store('app').darkMode) {
        document.documentElement.classList.add('dark');
    }
});

// Global notification system
window.addEventListener('show-notification', (event) => {
    const { message, type } = event.detail;

    const container = document.getElementById('notification-container') ||
                     (() => {
                         const div = document.createElement('div');
                         div.id = 'notification-container';
                         div.className = 'fixed top-4 right-4 z-50 space-y-2';
                         document.body.appendChild(div);
                         return div;
                     })();

    const notification = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };

    notification.className = `${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full opacity-0`;
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium">${message}</p>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;

    container.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full', 'opacity-0');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
});

// Auto-resize textareas
document.addEventListener('input', function(e) {
    if (e.target.matches('textarea[data-auto-resize]')) {
        e.target.style.height = 'auto';
        e.target.style.height = e.target.scrollHeight + 'px';
    }
});

// Image lazy loading
document.addEventListener('DOMContentLoaded', function() {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
});

// Smooth scroll for anchor links
document.addEventListener('click', function(e) {
    if (e.target.matches('a[href^="#"]')) {
        e.preventDefault();
        const target = document.querySelector(e.target.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});
