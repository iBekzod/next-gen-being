<!-- Cookie Consent Banner -->
<div id="cookie-consent" class="hidden fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 md:p-6 z-40 shadow-2xl">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex-1">
            <h3 class="font-bold mb-2">Cookie & Ad Consent</h3>
            <p class="text-sm text-gray-300">
                We use cookies and personalized ads to enhance your experience. By clicking "Accept All", you consent to our use of cookies and ads personalization in accordance with our Privacy Policy.
            </p>
        </div>
        <div class="flex gap-3 whitespace-nowrap">
            <button id="consent-reject" class="px-4 py-2 rounded bg-gray-700 hover:bg-gray-600 text-sm font-medium transition">
                Reject
            </button>
            <button id="consent-accept" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-sm font-medium transition">
                Accept All
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const consentBanner = document.getElementById('cookie-consent');
    const acceptBtn = document.getElementById('consent-accept');
    const rejectBtn = document.getElementById('consent-reject');
    const consentKey = 'nextgenbeing_ad_consent';

    // Check if user is in EEA/UK/Switzerland (simple geolocation check)
    function isUserInEEA() {
        // This is a basic check - in production, use a more reliable method
        // You could use IP geolocation or timezone detection
        const eea_countries = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'CH'];

        // Try to get country from meta tag if set by server
        const countryMeta = document.querySelector('meta[name="user-country"]');
        if (countryMeta) {
            const userCountry = countryMeta.getAttribute('content');
            return eea_countries.includes(userCountry);
        }

        // If no meta tag, show banner by default for safety
        return true;
    }

    // Check if consent already given
    function hasConsent() {
        return localStorage.getItem(consentKey) !== null;
    }

    // Get consent value
    function getConsent() {
        return localStorage.getItem(consentKey) === 'true';
    }

    // Save consent
    function saveConsent(accepted) {
        localStorage.setItem(consentKey, accepted ? 'true' : 'false');
        localStorage.setItem(consentKey + '_date', new Date().toISOString());
        consentBanner.classList.add('hidden');
        updateAdConsent(accepted);
    }

    // Update ad display based on consent
    function updateAdConsent(accepted) {
        window.adConsentGiven = accepted;

        if (accepted) {
            // Show ads (don't re-initialize, just show)
            document.querySelectorAll('.adsbygoogle').forEach(ad => {
                ad.style.display = 'block';
            });
        } else {
            // Hide ads without trying to re-initialize
            document.querySelectorAll('.adsbygoogle').forEach(ad => {
                ad.style.display = 'none';
            });
        }
    }

    // Show banner if needed
    if (isUserInEEA() && !hasConsent()) {
        consentBanner.classList.remove('hidden');
    } else if (hasConsent()) {
        // Apply saved consent
        updateAdConsent(getConsent());
    }

    // Handle accept button
    acceptBtn.addEventListener('click', function() {
        saveConsent(true);
    });

    // Handle reject button
    rejectBtn.addEventListener('click', function() {
        saveConsent(false);
    });
});
</script>
