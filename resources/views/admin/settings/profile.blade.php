@extends('layouts.admin')

@section('title', 'Profile Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-semibold text-gray-900">Profile Settings</h1>
        <p class="text-gray-500 mt-1">Manage your admin account and preferences</p>
    </div>

    <div class="space-y-6">
            <!-- Profile Information Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h2>

                <form method="POST" action="{{ route('admin.settings.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ $user->first_name }}" 
                                autocomplete="given-name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ $user->last_name }}" 
                                autocomplete="family-name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="{{ $user->email }}" 
                            autocomplete="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                        <input type="text" id="mobile_number" name="mobile_number" value="{{ $user->mobile_number }}" 
                            autocomplete="tel"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="+63 xxx xxxx xxx">
                        @error('mobile_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" id="password">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Change Password</h2>

                <form method="POST" action="{{ route('admin.settings.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" id="current_password" name="current_password" 
                            autocomplete="current-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('current_password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" id="new_password" name="password" 
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required minlength="8">
                        <p class="text-xs text-gray-500 mt-1">At least 8 characters long</p>
                        @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                            autocomplete="new-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                        @error('password_confirmation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i data-lucide="lock" class="w-4 h-4 mr-2"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preferences -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Notification Preferences</h2>

                <form method="POST" action="{{ route('admin.settings.preferences.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="email_low_inventory_alerts" name="email_low_inventory_alerts" value="1" 
                                {{ $preferences->email_low_inventory_alerts ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            <div>
                                <p class="font-medium text-gray-900">Low Inventory Alerts</p>
                                <p class="text-sm text-gray-500">Get notified when inventory is low</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="email_pending_payments" name="email_pending_payments" value="1" 
                                {{ $preferences->email_pending_payments ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            <div>
                                <p class="font-medium text-gray-900">Pending Payments</p>
                                <p class="text-sm text-gray-500">Get notified about pending payment statuses</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="email_bookings" name="email_bookings" value="1" 
                                {{ $preferences->email_bookings ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            <div>
                                <p class="font-medium text-gray-900">New Bookings</p>
                                <p class="text-sm text-gray-500">Get notified for new booking confirmations</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" id="email_user_signups" name="email_user_signups" value="1" 
                                {{ $preferences->email_user_signups ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-blue-600">
                            <div>
                                <p class="font-medium text-gray-900">New User Signups</p>
                                <p class="text-sm text-gray-500">Get notified when new users register</p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Save Preferences
                        </button>
                    </div>
                </form>
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
