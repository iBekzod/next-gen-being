<div class="space-y-6">
    <!-- Map Header -->
    <div class="flex items-center justify-between">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span>🗺️</span> Global Reader Map
        </h3>
        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-semibold">
            {{ $totalReaderLocations }} locations
        </span>
    </div>

    <!-- Map Container -->
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden shadow-lg">
        <div id="reader-map" class="w-full h-96 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-slate-700 dark:to-slate-800 relative">
            <div class="absolute inset-0 flex items-center justify-center">
                <p class="text-gray-600 dark:text-gray-400">🗺️ Loading map...</p>
            </div>
        </div>
    </div>

    <!-- Top Countries Stats -->
    @if(count($topCountries) > 0)
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Reading Countries</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
            @foreach($topCountries as $index => $country)
            <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg border border-blue-200 dark:border-blue-700">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-3xl">{{ $country['flag'] }}</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                        @switch($index)
                            @case(0) bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 @break
                            @case(1) bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @break
                            @case(2) bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 @break
                            @default bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 @break
                        @endswitch
                    ">
                        #{{ $index + 1 }}
                    </span>
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $country['country'] }}</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-2">{{ $country['readers'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    @if($totalReaderLocations > 0)
                        {{ round(($country['readers'] / array_sum(array_column($topCountries, 'readers'))) * 100) }}% of readers
                    @endif
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 dark:bg-slate-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-slate-600">
        <p class="text-gray-600 dark:text-gray-400">
            <span class="text-3xl">📍</span><br>
            No geographic data available yet. Readers will appear here!
        </p>
    </div>
    @endif

    <!-- Leaflet Map Script -->
    @script
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
    <script>
        document.addEventListener('livewire:navigated', function () {
            initMap();
        });

        function initMap() {
            const mapElement = document.getElementById('reader-map');
            if (!mapElement) return;

            // Clear previous map if exists
            if (window.readerMap) {
                window.readerMap.remove();
            }

            // Initialize map
            window.readerMap = L.map('reader-map').setView([20, 0], 2);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(window.readerMap);

            // Add markers from GeoJSON data
            const mapData = @json($mapData);

            if (mapData && mapData.features && mapData.features.length > 0) {
                mapData.features.forEach(feature => {
                    const coords = feature.geometry.coordinates;
                    const props = feature.properties;

                    const marker = L.circleMarker([coords[1], coords[0]], {
                        radius: Math.min(Math.log(props.readers) * 3, 20),
                        fillColor: '#3b82f6',
                        color: '#1e40af',
                        weight: 2,
                        opacity: 0.8,
                        fillOpacity: 0.6,
                    }).addTo(window.readerMap);

                    // Popup
                    const popupContent = `
                        <div class="text-sm">
                            <strong>${props.location}</strong><br>
                            <span class="text-xs">👥 ${props.readers} reader${props.readers !== 1 ? 's' : ''}</span>
                        </div>
                    `;

                    marker.bindPopup(popupContent);

                    // Highlight on hover
                    marker.on('mouseover', function() {
                        this.setStyle({ fillOpacity: 0.9 });
                    });
                    marker.on('mouseout', function() {
                        this.setStyle({ fillOpacity: 0.6 });
                    });
                });
            }
        }

        // Initialize on first load
        initMap();
    </script>
    @endscript
</div>
