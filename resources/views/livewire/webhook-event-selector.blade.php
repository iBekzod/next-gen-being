<div>
    <label class="block text-sm font-medium text-gray-700 mb-3">Events to Subscribe</label>

    @if(count($availableEvents) > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-gray-50">
        @foreach($availableEvents as $category => $events)
        <div class="col-span-full">
            <h4 class="text-sm font-semibold text-gray-900 mb-2 capitalize">{{ $category }}</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 ml-2">
                @if(is_array($events))
                    @foreach($events as $event)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:click="toggleEvent('{{ $event }}')"
                            @if(in_array($event, $selectedEvents)) checked @endif
                            class="rounded border-gray-300"
                        />
                        <span class="text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $event)) }}</span>
                    </label>
                    @endforeach
                @else
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:click="toggleEvent('{{ $events }}')"
                        @if(in_array($events, $selectedEvents)) checked @endif
                        class="rounded border-gray-300"
                    />
                    <span class="text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $events)) }}</span>
                </label>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-500">Loading available events...</p>
    @endif

    <!-- Selected Summary -->
    @if(count($selectedEvents) > 0)
    <div class="mt-3 p-3 bg-blue-50 rounded-lg">
        <p class="text-xs text-blue-900 font-medium">{{ count($selectedEvents) }} event(s) selected</p>
    </div>
    @endif
</div>
