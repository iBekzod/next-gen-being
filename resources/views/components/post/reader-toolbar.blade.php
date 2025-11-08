@props(['post'])

<div x-data="readerToolbar()"
     x-init="init()"
     class="sticky top-20 z-40 mb-6 bg-white dark:bg-slate-800 border-b border-gray-200 dark:border-slate-700 shadow-sm">

    <!-- Toolbar Container -->
    <div class="max-w-4xl px-4 py-3 mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">

            <!-- Left: Font Controls -->
            <div class="flex items-center gap-3">
                <!-- Font Size -->
                <div class="flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300 mr-2">Size:</span>
                    <button @click="decreaseFontSize()"
                            :class="{ 'opacity-50 cursor-not-allowed': fontSize <= 14 }"
                            :disabled="fontSize <= 14"
                            title="Decrease font size"
                            class="p-1 hover:bg-gray-200 dark:hover:bg-slate-600 rounded transition-colors">
                        <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </button>
                    <span x-text="fontSize + 'px'" class="text-xs font-medium text-gray-700 dark:text-gray-200 w-8 text-center"></span>
                    <button @click="increaseFontSize()"
                            :class="{ 'opacity-50 cursor-not-allowed': fontSize >= 24 }"
                            :disabled="fontSize >= 24"
                            title="Increase font size"
                            class="p-1 hover:bg-gray-200 dark:hover:bg-slate-600 rounded transition-colors">
                        <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>

                <!-- Line Height -->
                <div class="flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300 mr-2">Height:</span>
                    <select x-model="lineHeight"
                            @change="applyLineHeight()"
                            class="text-xs bg-transparent text-gray-700 dark:text-gray-200 font-medium focus:outline-none">
                        <option value="1.5">Compact</option>
                        <option value="1.75">Normal</option>
                        <option value="2" selected>Comfortable</option>
                        <option value="2.25">Wide</option>
                    </select>
                </div>
            </div>

            <!-- Right: Reading Modes & More -->
            <div class="flex items-center gap-2">
                <!-- Reading Mode: Focus/Distraction-Free -->
                <button @click="toggleFocusMode()"
                        :class="isFocusMode ? 'bg-blue-100 dark:bg-blue-900 border-blue-400 dark:border-blue-500 text-blue-700 dark:text-blue-300' : 'bg-gray-50 dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-slate-500'"
                        title="Toggle focus mode (hide sidebar)"
                        class="px-3 py-2 rounded-lg border text-xs font-medium transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7.07-7.07a10 10 0 11-14.14 0M9 12l2 2 4-4"/>
                    </svg>
                    <span class="hidden sm:inline">Focus</span>
                </button>

                <!-- Reading Mode: Eye-Friendly (Sepia) -->
                <button @click="toggleEyeFriendlyMode()"
                        :class="isEyeFriendlyMode ? 'bg-amber-100 dark:bg-amber-900 border-amber-400 dark:border-amber-600 text-amber-700 dark:text-amber-300' : 'bg-gray-50 dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-slate-500'"
                        title="Toggle eye-friendly mode"
                        class="px-3 py-2 rounded-lg border text-xs font-medium transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                    <span class="hidden sm:inline">Eye Care</span>
                </button>

                <!-- More Options -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="px-3 py-2 rounded-lg border bg-gray-50 dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-slate-500 transition-colors"
                            title="More options">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg shadow-lg z-50">

                        <button @click="printArticle(); open = false"
                                class="w-full px-4 py-2 text-sm text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4H9a2 2 0 00-2 2v2a2 2 0 002 2h10a2 2 0 002-2v-2a2 2 0 00-2-2h-2m-4-4V5a2 2 0 012-2h6a2 2 0 012 2v8"/>
                            </svg>
                            Print Article
                        </button>

                        <button @click="resetPreferences(); open = false"
                                class="w-full px-4 py-2 text-sm text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center gap-2 border-t border-gray-200 dark:border-slate-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reading Info Bar -->
        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-4">
            <span>📖 {{ $post->read_time ?? '5' }} min read</span>
            <span>📝 {{ number_format(strlen($post->content) / 5) }} words</span>
            <span>👁 Focus mode: <span x-text="isFocusMode ? 'ON' : 'OFF'"></span></span>
            <span>✨ Eye care: <span x-text="isEyeFriendlyMode ? 'ON' : 'OFF'"></span></span>
        </div>
    </div>
</div>

<style>
    /* Article Content Customization */
    .reader-article {
        transition: all 0.3s ease;
    }

    .reader-article.focus-mode {
        max-width: 65ch;
        margin: 0 auto;
    }

    .reader-article.eye-friendly {
        background-color: #f4ecd8;
        color: #3d3d3d;
    }

    .reader-article.eye-friendly a {
        color: #0066cc;
    }

    @media print {
        .reader-toolbar,
        .post-actions,
        .audio-player,
        .prose-comments,
        .sidebar {
            display: none !important;
        }

        .reader-article {
            max-width: 100% !important;
            color: #000 !important;
            background-color: #fff !important;
        }

        .reader-article h1,
        .reader-article h2,
        .reader-article h3 {
            page-break-after: avoid;
        }

        .reader-article p {
            orphans: 3;
            widows: 3;
        }
    }
</style>

<script>
function readerToolbar() {
    return {
        fontSize: 18,
        lineHeight: '2',
        isFocusMode: false,
        isEyeFriendlyMode: false,
        articleElement: null,

        init() {
            // Find the article content element
            this.articleElement = document.querySelector('.prose');

            if (this.articleElement) {
                this.articleElement.classList.add('reader-article');
            }

            // Load saved preferences
            this.loadPreferences();

            // Apply initial settings
            this.applyFontSize();
            this.applyLineHeight();
            this.applyReadingModes();
        },

        increaseFontSize() {
            if (this.fontSize < 24) {
                this.fontSize += 2;
                this.applyFontSize();
                this.savePreferences();
            }
        },

        decreaseFontSize() {
            if (this.fontSize > 14) {
                this.fontSize -= 2;
                this.applyFontSize();
                this.savePreferences();
            }
        },

        applyFontSize() {
            if (this.articleElement) {
                this.articleElement.style.fontSize = this.fontSize + 'px';
            }
        },

        applyLineHeight() {
            if (this.articleElement) {
                this.articleElement.style.lineHeight = this.lineHeight;
            }
            this.savePreferences();
        },

        toggleFocusMode() {
            this.isFocusMode = !this.isFocusMode;
            this.applyReadingModes();
            this.savePreferences();
        },

        toggleEyeFriendlyMode() {
            this.isEyeFriendlyMode = !this.isEyeFriendlyMode;
            this.applyReadingModes();
            this.savePreferences();
        },

        applyReadingModes() {
            if (!this.articleElement) return;

            // Focus Mode
            if (this.isFocusMode) {
                this.articleElement.classList.add('focus-mode');
                this.articleElement.style.maxWidth = '65ch';
                this.articleElement.style.margin = '0 auto';
            } else {
                this.articleElement.classList.remove('focus-mode');
                this.articleElement.style.maxWidth = '';
                this.articleElement.style.margin = '';
            }

            // Eye Friendly Mode
            if (this.isEyeFriendlyMode) {
                this.articleElement.classList.add('eye-friendly');
                document.body.style.backgroundColor = '#f4ecd8';
            } else {
                this.articleElement.classList.remove('eye-friendly');
                document.body.style.backgroundColor = '';
            }
        },

        resetPreferences() {
            // Confirm with user
            if (!confirm('Reset all reading settings to default?')) return;

            this.fontSize = 18;
            this.lineHeight = '2';
            this.isFocusMode = false;
            this.isEyeFriendlyMode = false;

            this.applyFontSize();
            this.applyLineHeight();
            this.applyReadingModes();
            this.clearPreferences();
        },

        printArticle() {
            // Prepare for printing
            const originalDisplay = this.articleElement.style.maxWidth;
            this.articleElement.style.maxWidth = '100%';

            window.print();

            // Restore after printing
            if (this.isFocusMode) {
                this.articleElement.style.maxWidth = '65ch';
            } else {
                this.articleElement.style.maxWidth = originalDisplay;
            }
        },

        savePreferences() {
            const preferences = {
                fontSize: this.fontSize,
                lineHeight: this.lineHeight,
                isFocusMode: this.isFocusMode,
                isEyeFriendlyMode: this.isEyeFriendlyMode,
                timestamp: Date.now()
            };
            localStorage.setItem('reader_preferences', JSON.stringify(preferences));
        },

        loadPreferences() {
            const saved = localStorage.getItem('reader_preferences');
            if (saved) {
                try {
                    const preferences = JSON.parse(saved);
                    this.fontSize = preferences.fontSize || 18;
                    this.lineHeight = preferences.lineHeight || '2';
                    this.isFocusMode = preferences.isFocusMode || false;
                    this.isEyeFriendlyMode = preferences.isEyeFriendlyMode || false;
                } catch (e) {
                    console.error('Failed to load reader preferences:', e);
                }
            }
        },

        clearPreferences() {
            localStorage.removeItem('reader_preferences');
        }
    };
}
</script>
