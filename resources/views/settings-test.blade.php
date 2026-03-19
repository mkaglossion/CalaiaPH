{{-- resources/views/settings-test.blade.php --}}
<x-app-layout>
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">⚙️ Admin Settings Test</h1>

                {{-- VAT Rate Test --}}
                <div class="mb-8 p-6 border-2 border-blue-200 rounded-lg bg-blue-50">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🧮 VAT Rate Configuration</h2>
                    
                    <div class="space-y-3 mb-4">
                        <p class="text-gray-700"><strong>Admin Setting:</strong> <span id="admin-vat-rate" class="text-blue-600">Loading...</span></p>
                        <p class="text-gray-700"><strong>API Response:</strong> <span id="api-vat-rate" class="text-blue-600">—</span></p>
                        <p class="text-gray-700"><strong>Cache Key:</strong> admin.settings['vat_rate']</p>
                    </div>

                    <div class="bg-white p-4 rounded border border-blue-200 text-sm text-gray-600 font-mono mb-4">
                        <strong>JSON Response:</strong>
                        <pre id="vat-json" class="mt-2">Fetching...</pre>
                    </div>

                    <button onclick="testVatRate()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        🔄 Fetch VAT Rate
                    </button>
                    <a href="{{ route('admin.settings.system') }}" class="ml-2 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition inline-block">
                        ⚙️ Go to Settings
                    </a>
                </div>

                {{-- Maintenance Mode Test --}}
                <div class="mb-8 p-6 border-2 border-red-200 rounded-lg bg-red-50">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🔧 Maintenance Mode</h2>
                    
                    <div class="space-y-3 mb-4">
                        <p class="text-gray-700"><strong>Status:</strong> <span id="maintenance-status" class="text-red-600">Loading...</span></p>
                        <p class="text-gray-700"><strong>When Enabled:</strong> All public pages show maintenance message</p>
                        <p class="text-gray-700"><strong>Admin Access:</strong> Still available at /admin/settings/system</p>
                    </div>

                    <div class="bg-white p-4 rounded border border-red-200 text-sm text-gray-600 font-mono mb-4">
                        <strong>Maintenance Status:</strong>
                        <pre id="maintenance-json" class="mt-2">Fetching...</pre>
                    </div>

                    <button onclick="testMaintenanceMode()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        🔄 Fetch Status
                    </button>
                    <a href="{{ route('admin.settings.system') }}" class="ml-2 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition inline-block">
                        ⚙️ Go to Settings
                    </a>
                </div>

                {{-- Booking Test --}}
                <div class="p-6 border-2 border-green-200 rounded-lg bg-green-50">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">🏠 Booking Pages (See Dynamic VAT)</h2>
                    
                    <div class="space-y-2 mb-4">
                        <p class="text-gray-700">Visit booking pages to see the dynamic VAT rate in action:</p>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            <li><a href="{{ route('booking.calendar') }}" class="text-blue-600 hover:underline">📅 Staycation Booking</a> - Shows dynamic VAT % in price calculation</li>
                            <li><a href="{{ route('daytour') }}" class="text-blue-600 hover:underline">🎯 Day Tour Booking</a> - Shows dynamic VAT % in price calculation</li>
                        </ul>
                    </div>

                    <p class="text-sm text-gray-600">
                        💡 <strong>Test:</strong> Change VAT rate in settings, then refresh booking pages to see the new rate applied instantly
                    </p>
                </div>

                {{-- Info --}}
                <div class="mt-8 p-4 bg-gray-100 rounded-lg text-sm text-gray-700">
                    <p class="mb-2"><strong>ℹ️ How Settings Work:</strong></p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>✅ Settings stored in cache (365 days) for performance</li>
                        <li>✅ API endpoint: <code class="bg-white px-2 py-1 rounded">/api/vat-rate</code> - Public access</li>
                        <li>✅ Maintenance mode: Middleware checks on every request</li>
                        <li>✅ Admin routes stay accessible during maintenance</li>
                        <li>✅ Cache auto-refreshes when settings updated</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function testVatRate() {
            try {
                const response = await fetch('/api/vat-rate');
                const data = await response.json();
                
                document.getElementById('api-vat-rate').textContent = `${data.rate_percentage}% (${data.label})`;
                document.getElementById('vat-json').textContent = JSON.stringify(data, null, 2);
                
                // Also check cache
                document.getElementById('admin-vat-rate').textContent = `${data.rate_percentage}% ✅`;
            } catch (error) {
                document.getElementById('vat-json').textContent = `Error: ${error.message}`;
            }
        }

        async function testMaintenanceMode() {
            try {
                const settings = await fetch('/api/admin-settings').then(r => r.json()).catch(() => null);
                
                if (!settings) {
                    // Fallback: try to detect maintenance mode by checking cache
                    const maintenanceHTML = await fetch('/').then(r => {
                        if (r.status === 503) return 'ACTIVE (Got 503 response)';
                        return 'INACTIVE (Got 200 response)';
                    });
                    
                    document.getElementById('maintenance-status').textContent = maintenanceHTML;
                    document.getElementById('maintenance-json').textContent = 'Maintenance mode: ' + maintenanceHTML;
                } else {
                    document.getElementById('maintenance-status').textContent = settings.system_maintenance ? '🔴 ACTIVE' : '🟢 INACTIVE';
                    document.getElementById('maintenance-json').textContent = JSON.stringify({
                        system_maintenance: settings.system_maintenance,
                        maintenance_message: settings.maintenance_message
                    }, null, 2);
                }
            } catch (error) {
                document.getElementById('maintenance-json').textContent = `Error: ${error.message}`;
            }
        }

        // Test on page load
        document.addEventListener('DOMContentLoaded', () => {
            testVatRate();
            testMaintenanceMode();
        });
    </script>
</x-app-layout>
