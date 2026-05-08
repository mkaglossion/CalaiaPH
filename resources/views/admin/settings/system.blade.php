@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-semibold text-gray-900">System Settings</h1>
        <p class="text-gray-500 mt-1">Configure system-wide settings and features</p>
    </div>

    <div class="space-y-6">
        <!-- Right Column - Forms (full width for responsive layout) -->
        <div class="space-y-6">
            <!-- General Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" id="general">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">General Settings</h2>

                <form method="POST" action="{{ route('admin.settings.system.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">Application Name</label>
                        <input type="text" id="app_name" name="app_name" value="{{ $settings['app_name'] ?? 'Calaia PH' }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('app_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="app_timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <select id="app_timezone" name="app_timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="Asia/Manila" {{ ($settings['app_timezone'] ?? 'Asia/Manila') === 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (Philippine Time)</option>
                                <option value="UTC" {{ ($settings['app_timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Asia/Tokyo" {{ ($settings['app_timezone'] ?? '') === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                                <option value="America/New_York" {{ ($settings['app_timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                            </select>
                            @error('app_timezone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="app_currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                            <select id="app_currency" name="app_currency" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="PHP" {{ ($settings['app_currency'] ?? 'PHP') === 'PHP' ? 'selected' : '' }}>PHP (₱) - Philippine Peso</option>
                                <option value="USD" {{ ($settings['app_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                <option value="EUR" {{ ($settings['app_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€) - Euro</option>
                            </select>
                            @error('app_currency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="business_hours_start" class="block text-sm font-medium text-gray-700 mb-1">Business Hours Start</label>
                            <input type="time" id="business_hours_start" name="business_hours_start" value="{{ $settings['business_hours_start'] ?? '06:00' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('business_hours_start') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="business_hours_end" class="block text-sm font-medium text-gray-700 mb-1">Business Hours End</label>
                            <input type="time" id="business_hours_end" name="business_hours_end" value="{{ $settings['business_hours_end'] ?? '22:00' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('business_hours_end') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="vat_rate" class="block text-sm font-medium text-gray-700 mb-1">VAT Rate (%)</label>
                        <input type="number" id="vat_rate" name="vat_rate" value="{{ $settings['vat_rate'] ?? '0' }}" step="0.01" min="0" max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('vat_rate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Maintenance Mode -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" id="maintenance">
                @php
                    $isMaintenanceActive = $settings['system_maintenance'] ?? false;
                @endphp
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Maintenance Mode</h2>
                        @if($isMaintenanceActive)
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-block w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                <p class="text-sm text-red-600 font-medium">ACTIVE - Customer site is down</p>
                            </div>
                        @else
                            <div class="flex items-center gap-2 mt-2">
                                <span class="inline-block w-2 h-2 bg-green-500 rounded-full"></span>
                                <p class="text-sm text-green-600 font-medium">Inactive - Site is operational</p>
                            </div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.settings.system.update') }}" class="inline" id="quick-toggle">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'Calaia PH' }}">
                        <input type="hidden" name="app_timezone" value="{{ $settings['app_timezone'] ?? 'Asia/Manila' }}">
                        <input type="hidden" name="app_currency" value="{{ $settings['app_currency'] ?? 'PHP' }}">
                        <input type="hidden" name="business_hours_start" value="{{ $settings['business_hours_start'] ?? '06:00' }}">
                        <input type="hidden" name="business_hours_end" value="{{ $settings['business_hours_end'] ?? '22:00' }}">
                        <input type="hidden" name="enable_new_bookings" value="{{ $settings['enable_new_bookings'] ?? '1' }}">
                        <input type="hidden" name="vat_rate" value="{{ $settings['vat_rate'] ?? '0' }}">
                        <input type="hidden" name="maintenance_message" value="{{ $settings['maintenance_message'] ?? '' }}">
                        <input type="hidden" name="system_maintenance" value="{{ $isMaintenanceActive ? '0' : '1' }}">
                        <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg transition {{ $isMaintenanceActive ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}">
                            <i data-lucide="{{ $isMaintenanceActive ? 'power' : 'alert-circle' }}" class="w-4 h-4 mr-2"></i>
                            {{ $isMaintenanceActive ? 'Deactivate Now' : 'Activate Now' }}
                        </button>
                    </form>
                </div>

                <form method="POST" action="{{ route('admin.settings.system.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Pass all other settings so they're not lost --}}
                    <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'Calaia PH' }}">
                    <input type="hidden" name="app_timezone" value="{{ $settings['app_timezone'] ?? 'Asia/Manila' }}">
                    <input type="hidden" name="app_currency" value="{{ $settings['app_currency'] ?? 'PHP' }}">
                    <input type="hidden" name="business_hours_start" value="{{ $settings['business_hours_start'] ?? '06:00' }}">
                    <input type="hidden" name="business_hours_end" value="{{ $settings['business_hours_end'] ?? '22:00' }}">
                    <input type="hidden" name="enable_new_bookings" value="{{ $settings['enable_new_bookings'] ?? '1' }}">
                    <input type="hidden" name="vat_rate" value="{{ $settings['vat_rate'] ?? '12' }}">

                    <label class="flex items-center gap-3 cursor-pointer p-4 border-2 {{ $isMaintenanceActive ? 'border-red-200 bg-red-50' : 'border-gray-200' }} rounded-lg transition">
                        <input type="hidden" name="system_maintenance" value="0">
                        <input type="checkbox" name="system_maintenance" value="1"
                            {{ $isMaintenanceActive ? 'checked' : '' }}
                            class="w-5 h-5 rounded border-gray-300 text-red-600">
                        <div>
                            <p class="font-medium text-gray-900">Enable Maintenance Mode</p>
                            <p class="text-sm text-gray-500">Disable all customer-facing features and show a maintenance message</p>
                        </div>
                    </label>

                    <div>
                        <label for="maintenance_message" class="block text-sm font-medium text-gray-700 mb-1">Maintenance Message</label>
                        <textarea id="maintenance_message" name="maintenance_message" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="We are currently undergoing maintenance. We'll be back soon!">{{ $settings['maintenance_message'] ?? '' }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will be shown to customers when maintenance mode is active.</p>
                        @error('maintenance_message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Save Changes
                        </button>
                        <a href="#" onclick="document.getElementById('quick-toggle').submit(); return false;" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                            Quick Toggle
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    </script>
@endif
@endsection
