{{--
    Exit-intent lead-magnet popup.
    Offers the free "AI-Assisted Developer's Field Guide" in exchange for a
    newsletter subscribe. Shows once per visitor (7-day cooldown) on desktop
    mouse-leave, with a timed fallback for touch devices.

    The newsletter-subscribe Livewire component is rendered in the DOM (hidden
    via x-show, not x-if) so Livewire initialises it on page load and the form
    works the moment the modal appears.
--}}
<div
    x-data="exitIntentNewsletter()"
    x-init="init()"
    @keydown.escape.window="close()"
>
    <div
        x-show="show"
        style="display:none"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-label="Subscribe for the free Field Guide"
    >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

        <!-- Card -->
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="relative z-10 w-full max-w-md p-8 bg-white shadow-2xl dark:bg-slate-800 rounded-2xl"
        >
            <button
                type="button"
                @click="close()"
                aria-label="Close"
                class="absolute text-gray-400 top-4 right-4 hover:text-gray-600 dark:hover:text-gray-200"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="text-center">
                <span class="inline-flex items-center gap-1 px-3 py-1 mb-4 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900/40 dark:text-blue-300">
                    🎁 Free guide
                </span>
                <h3 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Before you go…</h3>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Grab <strong>The AI-Assisted Developer's Field Guide</strong> free — the workflow, prompts, and tools I use to ship faster with AI, straight to your inbox.
                </p>

                @livewire('newsletter-subscribe', ['compact' => true])

                <button type="button" @click="close()" class="mt-4 text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    No thanks, maybe later
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function exitIntentNewsletter() {
    return {
        show: false,
        key: 'ngb_exit_intent_seen',
        cooldownDays: 7,
        seen() {
            try {
                const ts = localStorage.getItem(this.key);
                return ts ? (Date.now() - parseInt(ts, 10)) < this.cooldownDays * 864e5 : false;
            } catch (e) { return false; }
        },
        mark() { try { localStorage.setItem(this.key, Date.now().toString()); } catch (e) {} },
        trigger() {
            if (this.show || this.seen()) return;
            this.show = true;
            this.mark();
        },
        close() { this.show = false; },
        init() {
            if (this.seen()) return;
            // Desktop exit intent: pointer leaves the viewport past the top edge.
            document.addEventListener('mouseout', (e) => {
                if (!e.relatedTarget && e.clientY <= 0) this.trigger();
            });
            // Touch / fallback: fire after sustained engagement.
            setTimeout(() => this.trigger(), 50000);
        }
    };
}
</script>
