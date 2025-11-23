<div class="inline-flex gap-2">
    <button 
        wire:click="$set('showModal', true)"
        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition"
    >
        💰 Send Tip
    </button>

    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold mb-4">Send a Tip to {{ $recipient->name }}</h3>

            <form wire:submit="submitTip" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Amount ($)</label>
                    <input 
                        type="number" 
                        wire:model="tipAmount" 
                        min="1" 
                        max="1000" 
                        class="w-full border rounded px-3 py-2"
                        @if($isProcessing) disabled @endif
                    >
                    @error('tipAmount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Message (optional)</label>
                    <textarea 
                        wire:model="message" 
                        maxlength="500"
                        rows="3"
                        class="w-full border rounded px-3 py-2"
                        @if($isProcessing) disabled @endif
                    ></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input 
                        type="checkbox" 
                        wire:model="isAnonymous" 
                        id="anonymous"
                        @if($isProcessing) disabled @endif
                    >
                    <label for="anonymous" class="text-sm">Send anonymously</label>
                </div>

                <div class="flex gap-2">
                    <button 
                        type="submit"
                        @if($isProcessing) disabled @endif
                        class="flex-1 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
                    >
                        @if($isProcessing) Processing... @else Send Tip @endif
                    </button>
                    <button 
                        type="button"
                        wire:click="$set('showModal', false)"
                        @if($isProcessing) disabled @endif
                        class="flex-1 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
