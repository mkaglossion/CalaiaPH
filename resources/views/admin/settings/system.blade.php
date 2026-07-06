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
        @php
            $logoPreview = !empty($settings['logo_url'] ?? null)
                ? \App\Helpers\CloudinaryHelper::optimize($settings['logo_url'], 420, false, false, 'good')
                : null;
            $faviconPreview = !empty($settings['favicon_url'] ?? null)
                ? \App\Helpers\CloudinaryHelper::thumbnail($settings['favicon_url'], 96)
                : null;
        @endphp

        <!-- Right Column - Forms (full width for responsive layout) -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 md:p-8" id="branding">
                <div class="max-w-3xl">
                    <h2 class="text-2xl font-semibold text-slate-900">Branding & Footer</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Manage your site branding and footer content that appears on the public site.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.settings.system.update') }}" class="mt-8 space-y-8" enctype="multipart/form-data" id="branding-form">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
                        <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                    <i data-lucide="image" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Logo Upload</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Used in the site header, footer logo area, and maintenance screen.
                                    </p>
                                </div>
                            </div>

                            <div class="preview-shell preview-shell--logo mt-5 rounded-2xl border border-dashed border-slate-300 p-4">
                                @if($logoPreview)
                                    <img
                                        src="{{ $logoPreview }}"
                                        alt="Current logo preview"
                                        class="preview-image preview-image-logo"
                                    >
                                @else
                                    <div class="flex h-full min-h-[9rem] items-center justify-center text-slate-400">
                                        <div class="text-center">
                                            <i data-lucide="image" class="mx-auto mb-2 h-8 w-8"></i>
                                            <p class="text-sm">No logo uploaded yet</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <label class="inline-flex items-center gap-2 rounded-xl border border-blue-300 bg-white px-4 py-3 text-sm font-medium text-blue-700 transition hover:bg-blue-50">
                                    <input type="file" name="logo_file" accept="image/*" class="hidden" onchange="window.submitSystemSettingUpload(this)">
                                    <i data-lucide="upload" class="h-4 w-4"></i>
                                    <span>Choose image</span>
                                </label>

                                @if(!empty($settings['logo_url'] ?? null))
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300" onchange="window.submitSystemSettingUpload(this)">
                                        <span>Remove current logo</span>
                                    </label>
                                @endif
                            </div>

                            <p class="mt-4 text-xs text-slate-500">
                                Recommended: Transparent PNG, max 2MB
                            </p>
                            @error('logo_file') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                        </section>

                        <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                    <i data-lucide="star" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Browser Tab Icon (Favicon)</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Used as the browser tab icon across public and admin pages.
                                    </p>
                                </div>
                            </div>

                            <div class="preview-shell preview-shell--icon mt-5 rounded-2xl border border-dashed border-slate-300 p-4">
                                @if($faviconPreview)
                                    <img
                                        src="{{ $faviconPreview }}"
                                        alt="Current favicon preview"
                                        class="preview-image preview-image-favicon"
                                    >
                                @else
                                    <div class="flex h-full min-h-[8rem] items-center justify-center text-slate-400">
                                        <div class="text-center">
                                            <i data-lucide="globe" class="mx-auto mb-2 h-8 w-8"></i>
                                            <p class="text-sm">No favicon uploaded yet</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <label class="inline-flex items-center gap-2 rounded-xl border border-blue-300 bg-white px-4 py-3 text-sm font-medium text-blue-700 transition hover:bg-blue-50">
                                    <input type="file" name="favicon_file" accept="image/*,.ico" class="hidden" onchange="window.submitSystemSettingUpload(this)">
                                    <i data-lucide="upload" class="h-4 w-4"></i>
                                    <span>Choose image</span>
                                </label>

                                @if(!empty($settings['favicon_url'] ?? null))
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                        <input type="checkbox" name="remove_favicon" value="1" class="rounded border-slate-300" onchange="window.submitSystemSettingUpload(this)">
                                        <span>Remove current favicon</span>
                                    </label>
                                @endif
                            </div>

                            <p class="mt-4 text-xs text-slate-500">
                                Recommended: Square PNG or ICO, 512x512px, max 1MB
                            </p>
                            @error('favicon_file') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                        </section>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch mt-6">
                        <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                                    <i data-lucide="type" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Footer Content</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Edit the title and description shown in the public footer.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-5">
                                <div>
                                    <label for="footer_title" class="block text-sm font-medium text-slate-700">Footer Title</label>
                                    <div class="relative mt-2">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                            <i data-lucide="type" class="h-4 w-4"></i>
                                        </span>
                                        <input
                                            type="text"
                                            id="footer_title"
                                            name="footer_title"
                                            value="{{ $settings['footer_title'] ?? 'CALAIA FARM' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            maxlength="255"
                                        >
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500">This title appears in the left footer column.</p>
                                    @error('footer_title') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex-1">
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="footer_description" class="block text-sm font-medium text-slate-700">Footer Description</label>
                                        <span id="footer-description-count" class="text-xs text-slate-500"></span>
                                    </div>
                                    <div class="relative mt-2">
                                        <span class="pointer-events-none absolute left-3 top-3 text-slate-400">
                                            <i data-lucide="align-left" class="h-4 w-4"></i>
                                        </span>
                                        <textarea
                                            id="footer_description"
                                            name="footer_description"
                                            rows="6"
                                            class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            maxlength="300"
                                        >{{ $settings['footer_description'] ?? '' }}</textarea>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500">
                                        This description appears in the left footer column. Keep it short, clear, and engaging.
                                    </p>
                                    @error('footer_description') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </section>

                        <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                    <i data-lucide="eye" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Footer Preview</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        This is how it will appear on the public site.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 border-t border-slate-200 pt-6 flex-1">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 h-full">
                                    <div class="flex h-full flex-col gap-5 lg:flex-row lg:items-center">
                                        <div class="flex-shrink-0">
                                            @if($logoPreview)
                                                <img src="{{ $logoPreview }}" alt="Footer logo preview" class="h-20 w-auto max-w-[11rem] object-contain">
                                            @else
                                                <div class="flex h-20 w-40 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white text-sm font-medium text-slate-400">
                                                    Logo preview
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <h4 class="text-xl font-semibold tracking-wide text-slate-900">
                                                {{ $settings['footer_title'] ?? 'CALAIA FARM' }}
                                            </h4>
                                            <p class="mt-3 max-w-md text-sm leading-6 text-slate-600 whitespace-pre-line">
                                                {{ $settings['footer_description'] ?? '' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            <i data-lucide="save" class="mr-2 h-4 w-4"></i>
                            Save Branding
                        </button>
                        <button type="button" onclick="window.location.reload()" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <i data-lucide="rotate-ccw" class="mr-2 h-4 w-4"></i>
                            Reset Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- General Settings -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 md:p-8" id="general">
                <div class="max-w-3xl">
                    <h2 class="text-2xl font-semibold text-slate-900">General Settings</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Configure the core system values used across the public site and admin panel.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.settings.system.update') }}" class="mt-8 space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
                        <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                    <i data-lucide="building-2" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Core Identity</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Keep the application name, currency, and timezone consistent.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-5 flex-1">
                                <div>
                                    <label for="app_name" class="block text-sm font-medium text-slate-700">Application Name</label>
                                    <div class="relative mt-2">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                            <i data-lucide="type" class="h-4 w-4"></i>
                                        </span>
                                        <input type="text" id="app_name" name="app_name" value="{{ $settings['app_name'] ?? 'Calaia PH' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            required>
                                    </div>
                                    @error('app_name') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="app_timezone" class="block text-sm font-medium text-slate-700">Timezone</label>
                                        <select id="app_timezone" name="app_timezone" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                                            <option value="Asia/Manila" {{ ($settings['app_timezone'] ?? 'Asia/Manila') === 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (Philippine Time)</option>
                                            <option value="UTC" {{ ($settings['app_timezone'] ?? '') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                            <option value="Asia/Tokyo" {{ ($settings['app_timezone'] ?? '') === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                                            <option value="America/New_York" {{ ($settings['app_timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                        </select>
                                        @error('app_timezone') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="app_currency" class="block text-sm font-medium text-slate-700">Currency</label>
                                        <select id="app_currency" name="app_currency" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                                            <option value="PHP" {{ ($settings['app_currency'] ?? 'PHP') === 'PHP' ? 'selected' : '' }}>PHP (&#8369;) - Philippine Peso</option>
                                            <option value="USD" {{ ($settings['app_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD ($) - US Dollar</option>
                                            <option value="EUR" {{ ($settings['app_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (&#8364;) - Euro</option>
                                        </select>
                                        @error('app_currency') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                                    <i data-lucide="clock-3" class="h-5 w-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Operating Rules</h3>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Set business hours and VAT for admin calculations and public forms.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-5 flex-1">
                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="business_hours_start" class="block text-sm font-medium text-slate-700">Business Hours Start</label>
                                        <input type="time" id="business_hours_start" name="business_hours_start" value="{{ $settings['business_hours_start'] ?? '06:00' }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            required>
                                        @error('business_hours_start') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="business_hours_end" class="block text-sm font-medium text-slate-700">Business Hours End</label>
                                        <input type="time" id="business_hours_end" name="business_hours_end" value="{{ $settings['business_hours_end'] ?? '22:00' }}"
                                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            required>
                                        @error('business_hours_end') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="vat_rate" class="block text-sm font-medium text-slate-700">VAT Rate (%)</label>
                                    <div class="relative mt-2">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                            <i data-lucide="percent" class="h-4 w-4"></i>
                                        </span>
                                        <input type="number" id="vat_rate" name="vat_rate" value="{{ $settings['vat_rate'] ?? '0' }}" step="0.01" min="0" max="100"
                                            class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                            required>
                                    </div>
                                    @error('vat_rate') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                                </div>

                                <p class="text-xs text-slate-500">
                                    These values are shared across bookings, payments, and display formatting.
                                </p>
                            </div>
                        </section>
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            <i data-lucide="save" class="mr-2 h-4 w-4"></i>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Maintenance Mode -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 md:p-8" id="maintenance">
                @php
                    $isMaintenanceActive = $settings['system_maintenance'] ?? false;
                @endphp

                <div class="max-w-3xl">
                    <h2 class="text-2xl font-semibold text-slate-900">Maintenance Mode</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Control site availability and the public maintenance message.
                    </p>
                </div>

                <div class="mt-8 grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
                    <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $isMaintenanceActive ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                                <i data-lucide="{{ $isMaintenanceActive ? 'alert-circle' : 'shield-check' }}" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Status & Toggle</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Quickly switch the public site on or off.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 flex-1 space-y-5">
                            <div class="rounded-2xl border {{ $isMaintenanceActive ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }} p-4">
                                <div class="flex items-center gap-3">
                                    <span class="inline-block h-2.5 w-2.5 rounded-full {{ $isMaintenanceActive ? 'bg-red-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                                    <div>
                                        <p class="text-sm font-semibold {{ $isMaintenanceActive ? 'text-red-700' : 'text-emerald-700' }}">
                                            {{ $isMaintenanceActive ? 'ACTIVE - Customer site is down' : 'Inactive - Site is operational' }}
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $isMaintenanceActive ? 'Deactivate to restore the public website.' : 'Activate when you need to pause the public site.' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.settings.system.update') }}" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'Calaia PH' }}">
                                <input type="hidden" name="app_timezone" value="{{ $settings['app_timezone'] ?? 'Asia/Manila' }}">
                                <input type="hidden" name="app_currency" value="{{ $settings['app_currency'] ?? 'PHP' }}">
                                <input type="hidden" name="business_hours_start" value="{{ $settings['business_hours_start'] ?? '06:00' }}">
                                <input type="hidden" name="business_hours_end" value="{{ $settings['business_hours_end'] ?? '22:00' }}">
                                <input type="hidden" name="enable_new_bookings" value="{{ $settings['enable_new_bookings'] ?? '1' }}">
                                <input type="hidden" name="vat_rate" value="{{ $settings['vat_rate'] ?? '12' }}">
                                <input type="hidden" name="footer_title" value="{{ $settings['footer_title'] ?? 'CALAIA FARM' }}">
                                <input type="hidden" name="footer_description" value="{{ $settings['footer_description'] ?? '' }}">
                                <input type="hidden" name="system_maintenance" value="{{ $isMaintenanceActive ? '0' : '1' }}">

                                <button type="submit" class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-sm transition {{ $isMaintenanceActive ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700' }}">
                                    <i data-lucide="{{ $isMaintenanceActive ? 'power' : 'alert-circle' }}" class="mr-2 h-4 w-4"></i>
                                    {{ $isMaintenanceActive ? 'Deactivate Now' : 'Activate Now' }}
                                </button>
                            </form>
                        </div>
                    </section>

                    <section class="branding-panel rounded-2xl border border-slate-200 bg-white p-5 shadow-sm h-full flex flex-col">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                                <i data-lucide="message-square-text" class="h-5 w-5"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Maintenance Message</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    This text is shown to visitors when maintenance is active.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.settings.system.update') }}" class="mt-5 flex-1 space-y-5">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'Calaia PH' }}">
                            <input type="hidden" name="app_timezone" value="{{ $settings['app_timezone'] ?? 'Asia/Manila' }}">
                            <input type="hidden" name="app_currency" value="{{ $settings['app_currency'] ?? 'PHP' }}">
                            <input type="hidden" name="business_hours_start" value="{{ $settings['business_hours_start'] ?? '06:00' }}">
                            <input type="hidden" name="business_hours_end" value="{{ $settings['business_hours_end'] ?? '22:00' }}">
                            <input type="hidden" name="enable_new_bookings" value="{{ $settings['enable_new_bookings'] ?? '1' }}">
                            <input type="hidden" name="vat_rate" value="{{ $settings['vat_rate'] ?? '12' }}">
                            <input type="hidden" name="system_maintenance" value="{{ $isMaintenanceActive ? '1' : '0' }}">
                            <input type="hidden" name="footer_title" value="{{ $settings['footer_title'] ?? 'CALAIA FARM' }}">
                            <input type="hidden" name="footer_description" value="{{ $settings['footer_description'] ?? '' }}">

                            <div>
                                <label for="maintenance_message" class="block text-sm font-medium text-slate-700">Maintenance Message</label>
                                <div class="relative mt-2">
                                    <span class="pointer-events-none absolute left-3 top-3 text-slate-400">
                                        <i data-lucide="type" class="h-4 w-4"></i>
                                    </span>
                                    <textarea id="maintenance_message" name="maintenance_message" rows="6"
                                        class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-slate-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                        placeholder="We are currently undergoing maintenance. We'll be back soon!">{{ $settings['maintenance_message'] ?? '' }}</textarea>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">
                                    This message appears on the public maintenance screen.
                                </p>
                                @error('maintenance_message') <span class="mt-2 block text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                    <i data-lucide="save" class="mr-2 h-4 w-4"></i>
                                    Save Message
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div
        data-admin-flash
        data-admin-flash-library="swal"
        data-admin-flash-title="Success!"
        data-admin-flash-type="success"
        data-admin-flash-message="{{ e(session('success')) }}"
    ></div>
@endif

@if(session('error'))
    <div
        data-admin-flash
        data-admin-flash-library="swal"
        data-admin-flash-title="Error!"
        data-admin-flash-type="error"
        data-admin-flash-message="{{ e(session('error')) }}"
    ></div>
@endif

<script>
    window.submitSystemSettingUpload = function (input) {
        const form = input && input.form;
        if (!form) {
            return;
        }

        if (input.type === 'file' && (!input.files || input.files.length === 0)) {
            return;
        }

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }

        form.submit();
    };

    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('footer_description');
        const counter = document.getElementById('footer-description-count');

        if (!textarea || !counter) {
            return;
        }

        const max = parseInt(textarea.getAttribute('maxlength') || '0', 10);
        const updateCounter = function () {
            counter.textContent = max ? `${textarea.value.length} / ${max}` : `${textarea.value.length}`;
        };

        updateCounter();
        textarea.addEventListener('input', updateCounter);
    });
</script>

<style>
    .preview-shell,
    .preview-checkerboard {
        position: relative;
        overflow: hidden;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-shell--logo {
        height: 11rem;
        padding: 1rem;
    }

    .preview-shell--icon {
        height: 11rem;
        padding: 1rem;
    }

    .preview-image {
        display: block;
        object-fit: contain;
        margin: 0 auto;
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
        filter: drop-shadow(0 1px 1px rgba(15, 23, 42, 0.06));
    }

    .preview-image-logo {
        max-width: min(100%, 28rem);
        max-height: 6rem;
    }

    .preview-image-favicon {
        max-width: 5rem;
        max-height: 5rem;
    }
</style>
@endsection
