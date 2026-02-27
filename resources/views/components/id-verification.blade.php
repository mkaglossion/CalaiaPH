{{--
    ID Verification Component
    AI-powered real-time ID verification for Senior/PWD discounts
    
    Usage:
    @include('components.id-verification', [
        'booking' => $booking,
        'discountType' => 'senior', // or 'pwd'
        'showCamera' => true
    ])
--}}

@php
$bookingId = $booking->id ?? null;
$currentStatus = $booking->discount_verification_status ?? 'not_required';
$existingImage = $booking->discount_id_image ?? null;
@endphp

<div class="id-verification-container bg-white rounded-lg shadow-md p-6" 
     data-id-verification-form
     data-booking-id="{{ $bookingId }}">
    
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">
            <svg class="w-5 h-5 inline-block mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
            </svg>
            ID Verification Required
        </h3>
        
        @if($currentStatus === 'verified')
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                ✓ Verified
            </span>
        @elseif($currentStatus === 'pending')
            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                ⏳ Pending Review
            </span>
        @elseif($currentStatus === 'rejected')
            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                ✗ Rejected
            </span>
        @endif
    </div>

    <p class="text-sm text-gray-600 mb-4">
        To claim your {{ $discountType === 'senior' ? 'Senior Citizen' : 'PWD' }} discount, 
        please upload a clear photo of your valid ID. Our AI system will verify it instantly.
    </p>

    {{-- ID Type Selection --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
        <div class="flex space-x-4">
            <label class="flex items-center">
                <input type="radio" name="id_type" value="senior" 
                       data-id-type-input
                       {{ $discountType === 'senior' ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Senior Citizen ID</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="id_type" value="pwd" 
                       data-id-type-input
                       {{ $discountType === 'pwd' ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">PWD ID</span>
            </label>
        </div>
    </div>

    {{-- ID Number Input --}}
    <div class="mb-4">
        <label for="id_number" class="block text-sm font-medium text-gray-700 mb-1">
            ID Number <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="id_number" 
               name="id_number" 
               data-id-number-input
               value="{{ $booking->discount_id_number ?? '' }}"
               placeholder="{{ $discountType === 'senior' ? 'OSC-123456' : 'PWD-123456' }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
               required>
        <p class="text-xs text-gray-500 mt-1">
            Format: {{ $discountType === 'senior' ? 'OSC-XXXXXX or SC-XXXXXX' : 'PWD-XXXXXX, IP-XXXXXX, or MC-XXXXXX' }}
        </p>
    </div>

    {{-- Status Display Area --}}
    <div data-id-status class="mb-4">
        @if($currentStatus === 'verified')
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium">✓ ID Verified</p>
                        <p class="text-xs text-green-600 mt-1">Your discount has been applied.</p>
                    </div>
                </div>
            </div>
        @elseif($currentStatus === 'pending')
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 font-medium">⏳ Pending Review</p>
                        <p class="text-xs text-yellow-600 mt-1">Your ID is being reviewed by our team.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Image Upload Section --}}
    @if(!in_array($currentStatus, ['verified']))
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Upload ID Image <span class="text-red-500">*</span>
        </label>
        
        {{-- Preview Area --}}
        <div data-id-preview class="mb-3">
            @if($existingImage)
                <div class="relative">
                    <img src="{{ asset('storage/' . $existingImage) }}" 
                         class="max-w-full h-auto rounded-lg border-2 border-gray-300" 
                         alt="Uploaded ID">
                    <div class="absolute top-2 right-2">
                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">Current</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Upload Options --}}
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- File Upload --}}
            <label class="flex-1 cursor-pointer">
                <input type="file" 
                       name="id_image" 
                       data-id-image-input
                       accept="image/jpeg,image/png,image/jpg"
                       class="hidden"
                       {{ $currentStatus === 'verified' ? 'disabled' : '' }}>
                <div class="flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <svg class="w-6 h-6 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-sm text-gray-600">Choose File</span>
                </div>
            </label>

            {{-- Camera Capture --}}
            @if($showCamera ?? true)
            <button type="button" 
                    data-id-camera-btn
                    class="flex-1 flex items-center justify-center px-4 py-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
                    {{ $currentStatus === 'verified' ? 'disabled' : '' }}>
                <svg class="w-6 h-6 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-sm text-gray-600">Use Camera</span>
            </button>
            @endif
        </div>

        <p class="text-xs text-gray-500 mt-2">
            Accepted formats: JPEG, PNG (Max 2MB). Make sure the ID is clearly visible and all text is readable.
        </p>
    </div>
    @endif

    {{-- Alternative Option --}}
    @if(!in_array($currentStatus, ['verified', 'pending']))
    <div class="border-t border-gray-200 pt-4 mt-4">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="present_at_checkin" 
                       name="verification_method" 
                       type="radio" 
                       value="present_at_checkin"
                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
            </div>
            <div class="ml-3 text-sm">
                <label for="present_at_checkin" class="font-medium text-gray-700">
                    Present ID at Check-in Instead
                </label>
                <p class="text-gray-500">
                    You can present your physical ID when you arrive. Note: If you don't bring your ID, 
                    you'll need to pay the discount difference (20% + VAT).
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Submit Button --}}
    @if(!in_array($currentStatus, ['verified']))
    <div class="mt-6">
        <button type="submit" 
                data-submit-btn
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
            @if($currentStatus === 'pending')
                Update ID Information
            @else
                Verify & Continue
            @endif
        </button>
    </div>
    @endif

    {{-- Security Note --}}
    <div class="mt-4 p-3 bg-gray-50 rounded text-xs text-gray-500">
        <p class="flex items-center">
            <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Your ID is securely encrypted and only used for verification purposes. 
            It will be deleted after your visit.
        </p>
    </div>
</div>

{{-- Include the JavaScript --}}
@push('scripts')
<script src="{{ asset('js/id-verification.js') }}"></script>
@endpush
