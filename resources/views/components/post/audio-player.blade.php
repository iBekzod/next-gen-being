@props(['post'])

<div x-data="audioPlayer({{ $post->id }})"
     x-init="init()"
     class="sticky top-20 z-40 mb-8 overflow-hidden transition-all duration-300 border-2 shadow-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 border-blue-500/30 rounded-xl"
     :class="{
         'shadow-2xl': isPlaying,
         'max-h-16': !isExpanded,
         'max-h-96': isExpanded
     }">

    <!-- Compact Header (Always Visible) -->
    <div class="flex items-center justify-between gap-2 p-3"
         :class="{ 'border-b border-blue-500/20': isExpanded }">

        <!-- Play/Pause Button -->
        <button @click="togglePlay()"
                class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-white transition-all transform bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full shadow hover:shadow-lg hover:scale-105">
            <svg x-show="!isPlaying" class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
            </svg>
            <svg x-show="isPlaying" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </button>

        <!-- Title & Progress (Compact) -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900 truncate dark:text-white">Listen to Article</h3>
                <span class="text-xs text-gray-600 dark:text-gray-400" x-show="isLoading">Loading...</span>
            </div>
            <!-- Mini Progress Bar -->
            <div class="relative h-1 mt-1 bg-gray-200 rounded-full dark:bg-gray-700">
                <div class="absolute h-full transition-all duration-150 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full"
                     :style="`width: ${progress}%`"></div>
            </div>
        </div>

        <!-- Time & Expand Button -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                <span x-text="formatTime(currentPosition)">0:00</span> / <span x-text="formatTime(totalDuration)">0:00</span>
            </span>
            <button @click="isExpanded = !isExpanded"
                    class="flex items-center justify-center w-8 h-8 text-gray-600 transition-all rounded-full hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-slate-700">
                <svg x-show="!isExpanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                <svg x-show="isExpanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Expanded Controls (Hidden by default) -->
    <div x-show="isExpanded"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="p-4 space-y-3">

        <!-- Voice Selection -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Voice</label>
                <select x-model="selectedVoiceIndex"
                        @change="changeVoice()"
                        class="w-full px-2 py-1.5 text-xs bg-white border border-gray-300 rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <template x-for="(voice, index) in voices" :key="index">
                        <option :value="index" x-text="`${voice.name} (${voice.lang})`"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Speed</label>
                <select x-model="playbackRate"
                        @change="changeSpeed()"
                        class="w-full px-2 py-1.5 text-xs bg-white border border-gray-300 rounded-lg dark:bg-slate-700 dark:border-slate-600 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="0.5">0.5x</option>
                    <option value="0.75">0.75x</option>
                    <option value="1" selected>1x</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                    <option value="1.75">1.75x</option>
                    <option value="2">2x</option>
                </select>
            </div>
        </div>

        <!-- Full Progress Bar -->
        <div>
            <div class="relative h-2 bg-gray-200 rounded-full cursor-pointer dark:bg-gray-700 group"
                 @click="seek($event)">
                <div class="absolute h-full transition-all duration-150 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full"
                     :style="`width: ${progress}%`"></div>
                <div class="absolute w-4 h-4 transition-all duration-150 transform -translate-x-1/2 -translate-y-1/2 bg-white border-2 border-blue-500 rounded-full shadow-lg opacity-0 top-1/2 group-hover:opacity-100"
                     :style="`left: ${progress}%`"></div>
            </div>
            <div class="flex justify-between mt-1 text-xs text-gray-600 dark:text-gray-400">
                <span x-text="formatTime(currentPosition)">0:00</span>
                <span x-text="formatTime(totalDuration)">0:00</span>
            </div>
        </div>

        <!-- Control Buttons -->
        <div class="flex items-center justify-center gap-3">
            <button @click="skipBackward(10)"
                    class="flex items-center justify-center w-9 h-9 text-gray-700 transition-all bg-white border border-gray-300 rounded-full hover:bg-gray-50 hover:border-blue-500 hover:shadow dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:hover:bg-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                </svg>
            </button>

            <button @click="skipForward(10)"
                    class="flex items-center justify-center w-9 h-9 text-gray-700 transition-all bg-white border border-gray-300 rounded-full hover:bg-gray-50 hover:border-blue-500 hover:shadow dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:hover:bg-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zM19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z"/>
                </svg>
            </button>

            <button @click="stop()"
                    class="flex items-center justify-center w-9 h-9 text-gray-700 transition-all bg-white border border-gray-300 rounded-full hover:bg-red-50 hover:border-red-500 hover:text-red-600 dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:hover:bg-slate-600">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        <!-- Advanced Controls (Pitch & Volume) -->
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-blue-500/20">
            <div>
                <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Pitch</label>
                <input type="range"
                       x-model="pitch"
                       @input="changePitch()"
                       min="0.5"
                       max="2"
                       step="0.1"
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Low</span>
                    <span x-text="pitch"></span>
                    <span>High</span>
                </div>
            </div>

            <div>
                <label class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">Volume</label>
                <input type="range"
                       x-model="volume"
                       @input="changeVolume()"
                       min="0"
                       max="1"
                       step="0.1"
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>0%</span>
                    <span x-text="Math.round(volume * 100) + '%'"></span>
                    <span>100%</span>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="text-xs text-center text-gray-600 dark:text-gray-400">
            <span x-show="isPaused && currentPosition > 0">⏸ Paused</span>
            <span x-show="isPlaying">▶️ Now playing...</span>
            <span x-show="!isPlaying && !isPaused && currentPosition === 0">Ready to play</span>
            <span x-show="currentPosition >= totalDuration && totalDuration > 0">✓ Finished</span>
        </div>
    </div>
</div>

<script>
function audioPlayer(postId) {
    return {
        postId: postId,
        synth: null,
        utterance: null,
        voices: [],
        selectedVoiceIndex: 0,
        isPlaying: false,
        isPaused: false,
        isLoading: false,
        isExpanded: false,  // Start collapsed
        currentPosition: 0,
        totalDuration: 0,
        progress: 0,
        playbackRate: 1,
        pitch: 1,
        volume: 1,
        text: '',
        chunks: [],
        currentChunk: 0,
        interval: null,

        init() {
            // Check if speech synthesis is supported
            if (!('speechSynthesis' in window)) {
                alert('Sorry, your browser doesn\'t support text-to-speech. Please try Chrome, Edge, or Safari.');
                return;
            }

            this.synth = window.speechSynthesis;

            // Load available voices
            this.loadVoices();

            // Voices might load asynchronously
            if (speechSynthesis.onvoiceschanged !== undefined) {
                speechSynthesis.onvoiceschanged = () => this.loadVoices();
            }

            // Extract text content from the article
            this.extractText();

            // Load saved preferences
            this.loadPreferences();

            // Update progress every 100ms when playing
            this.interval = setInterval(() => {
                if (this.isPlaying) {
                    this.updateProgress();
                }
            }, 100);
        },

        loadVoices() {
            this.voices = this.synth.getVoices();

            // Prefer English voices if available
            const preferredVoice = this.voices.findIndex(voice =>
                voice.lang.startsWith('en') && !voice.name.includes('Google')
            );

            if (preferredVoice !== -1) {
                this.selectedVoiceIndex = preferredVoice;
            }
        },

        extractText() {
            // Get the article content
            const article = document.querySelector('.prose');
            if (!article) return;

            // Clone the article to avoid modifying the original
            const clone = article.cloneNode(true);

            // Remove code blocks and non-text elements
            clone.querySelectorAll('pre, code, img, svg, .paywall, button, nav').forEach(el => el.remove());

            // Get text content
            this.text = clone.textContent.trim();

            // Split into chunks (Web Speech API has limits)
            this.chunks = this.splitIntoChunks(this.text, 200); // ~200 words per chunk
            this.totalDuration = this.estimateDuration(this.text);
        },

        splitIntoChunks(text, maxWords) {
            const words = text.split(/\s+/);
            const chunks = [];

            for (let i = 0; i < words.length; i += maxWords) {
                chunks.push(words.slice(i, i + maxWords).join(' '));
            }

            return chunks;
        },

        estimateDuration(text) {
            // Average speaking rate: 150-160 words per minute
            const words = text.split(/\s+/).length;
            const baseRate = 150;
            // Adjust for playback rate
            return (words / (baseRate * this.playbackRate)) * 60; // seconds
        },

        togglePlay() {
            if (this.isPlaying) {
                this.pause();
            } else {
                this.play();
            }
        },

        play() {
            if (this.isPaused) {
                this.synth.resume();
                this.isPlaying = true;
                this.isPaused = false;
            } else {
                this.playChunk(this.currentChunk);
            }
        },

        playChunk(chunkIndex) {
            if (chunkIndex >= this.chunks.length) {
                this.stop();
                return;
            }

            this.currentChunk = chunkIndex;
            this.utterance = new SpeechSynthesisUtterance(this.chunks[chunkIndex]);

            // Apply settings
            this.utterance.rate = this.playbackRate;
            this.utterance.pitch = this.pitch;
            this.utterance.volume = this.volume;

            // Set selected voice
            if (this.voices.length > 0 && this.selectedVoiceIndex < this.voices.length) {
                this.utterance.voice = this.voices[this.selectedVoiceIndex];
            }

            // When chunk ends, play next chunk
            this.utterance.onend = () => {
                if (this.currentChunk < this.chunks.length - 1) {
                    this.playChunk(this.currentChunk + 1);
                } else {
                    this.stop();
                }
            };

            this.utterance.onerror = (event) => {
                console.error('Speech synthesis error:', event);
                this.stop();
            };

            this.synth.speak(this.utterance);
            this.isPlaying = true;
            this.isPaused = false;
        },

        pause() {
            if (this.synth.speaking && !this.synth.paused) {
                this.synth.pause();
                this.isPlaying = false;
                this.isPaused = true;
                this.savePreferences();
            }
        },

        stop() {
            this.synth.cancel();
            this.isPlaying = false;
            this.isPaused = false;
            this.currentChunk = 0;
            this.currentPosition = 0;
            this.progress = 0;
            this.savePreferences();
        },

        skipBackward(seconds) {
            // Approximate by going back chunks
            const wordsToSkip = Math.floor((seconds / 60) * 150);
            const chunksToSkip = Math.ceil(wordsToSkip / 200);

            if (this.currentChunk > 0) {
                const wasPlaying = this.isPlaying;
                this.stop();
                this.currentChunk = Math.max(0, this.currentChunk - chunksToSkip);
                if (wasPlaying) {
                    this.play();
                }
            }
        },

        skipForward(seconds) {
            // Approximate by going forward chunks
            const wordsToSkip = Math.floor((seconds / 60) * 150);
            const chunksToSkip = Math.ceil(wordsToSkip / 200);

            if (this.currentChunk < this.chunks.length - 1) {
                const wasPlaying = this.isPlaying;
                this.stop();
                this.currentChunk = Math.min(this.chunks.length - 1, this.currentChunk + chunksToSkip);
                if (wasPlaying) {
                    this.play();
                }
            }
        },

        seek(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const percent = (event.clientX - rect.left) / rect.width;
            const targetChunk = Math.floor(percent * this.chunks.length);

            const wasPlaying = this.isPlaying;
            this.stop();
            this.currentChunk = targetChunk;

            if (wasPlaying) {
                this.play();
            }
        },

        changeSpeed() {
            if (this.utterance) {
                this.utterance.rate = this.playbackRate;
            }
            // Update estimated duration
            this.totalDuration = this.estimateDuration(this.text);
            this.savePreferences();
        },

        changePitch() {
            if (this.utterance) {
                this.utterance.pitch = this.pitch;
            }
            this.savePreferences();
        },

        changeVolume() {
            if (this.utterance) {
                this.utterance.volume = this.volume;
            }
            this.savePreferences();
        },

        changeVoice() {
            // If playing, restart with new voice
            if (this.isPlaying) {
                const wasPlaying = true;
                const currentChunk = this.currentChunk;
                this.stop();
                this.currentChunk = currentChunk;
                if (wasPlaying) {
                    this.play();
                }
            }
            this.savePreferences();
        },

        updateProgress() {
            // Estimate position based on current chunk
            const chunkProgress = this.currentChunk / this.chunks.length;
            this.progress = chunkProgress * 100;
            this.currentPosition = chunkProgress * this.totalDuration;
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        savePreferences() {
            const data = {
                postId: this.postId,
                currentChunk: this.currentChunk,
                selectedVoiceIndex: this.selectedVoiceIndex,
                playbackRate: this.playbackRate,
                pitch: this.pitch,
                volume: this.volume,
                timestamp: Date.now()
            };
            localStorage.setItem(`audio_player_${this.postId}`, JSON.stringify(data));
        },

        loadPreferences() {
            const saved = localStorage.getItem(`audio_player_${this.postId}`);
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    // Only restore if saved within last 7 days
                    if (Date.now() - data.timestamp < 7 * 24 * 60 * 60 * 1000) {
                        this.currentChunk = data.currentChunk || 0;
                        this.selectedVoiceIndex = data.selectedVoiceIndex || 0;
                        this.playbackRate = data.playbackRate || 1;
                        this.pitch = data.pitch || 1;
                        this.volume = data.volume || 1;
                        this.currentPosition = (this.currentChunk / this.chunks.length) * this.totalDuration;
                        this.progress = (this.currentChunk / this.chunks.length) * 100;
                    }
                } catch (e) {
                    console.error('Failed to load saved preferences:', e);
                }
            }
        }
    };
}
</script>
