<div class="bg-white rounded-lg shadow-sm p-6 mt-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">UTM URL Generator</h3>
    <p class="text-sm text-gray-600 mb-4">Generate tracking URLs to measure the effectiveness of your social shares</p>

    <div class="space-y-4">
        <!-- Base URL -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Article URL</label>
            <input
                type="url"
                wire:model="baseUrl"
                placeholder="https://yourblog.com/article"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
        </div>

        <!-- UTM Parameters -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- UTM Source -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Source (e.g., twitter, facebook)</label>
                <input
                    type="text"
                    wire:model="utmSource"
                    placeholder="e.g., twitter"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>

            <!-- UTM Medium -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Medium</label>
                <select
                    wire:model="utmMedium"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="social">Social</option>
                    <option value="email">Email</option>
                    <option value="newsletter">Newsletter</option>
                    <option value="organic">Organic</option>
                    <option value="referral">Referral</option>
                </select>
            </div>

            <!-- UTM Campaign -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Campaign</label>
                <input
                    type="text"
                    wire:model="utmCampaign"
                    placeholder="e.g., launch, promotion"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>
        </div>

        <!-- Generate Button -->
        <button
            wire:click="generateUrl"
            class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium"
        >
            Generate UTM URL
        </button>

        <!-- Generated URL -->
        @if($generatedUrl)
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <p class="text-xs text-gray-600 mb-2">Generated URL:</p>
            <div class="flex items-center gap-2">
                <code class="flex-1 text-xs text-gray-900 break-all font-mono bg-white border border-gray-300 rounded px-3 py-2">
                    {{ $generatedUrl }}
                </code>
                <button
                    wire:click="copyToClipboard"
                    class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium transition whitespace-nowrap"
                >
                    Copy
                </button>
            </div>
            <p class="text-xs text-gray-600 mt-2">Use this URL when sharing on social media to track clicks and conversions</p>
        </div>
        @endif

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
            <p class="text-blue-900 font-medium mb-2">UTM Parameter Guide:</p>
            <ul class="text-blue-800 space-y-1 text-xs">
                <li><strong>Source:</strong> Where the traffic comes from (twitter, facebook, linkedin, etc.)</li>
                <li><strong>Medium:</strong> The type of link (social, email, referral, etc.)</li>
                <li><strong>Campaign:</strong> Specific campaign or promotion name</li>
            </ul>
        </div>
    </div>
</div>
