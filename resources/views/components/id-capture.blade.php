{{-- 
    ID Capture Component with AI-Powered Real-Time Verification
    Replaces the old component with intelligent ID verification
    
    Features:
    - Real-time OCR and image analysis
    - Quality checks (blur, resolution, lighting)
    - Three-tier verification: Auto-verify / Pending / Rejected
    - Camera capture and file upload support
--}}

@php
$bookingId = $booking->id ?? 'new';
$currentStatus = $booking->discount_verification_status ?? 'not_required';
$existingImage = $booking->discount_id_image ?? null;
$idNumber = $booking->discount_id_number ?? '';
$discountType = $discountType ?? 'senior';
@endphp

<div class="id-capture-component bg-white rounded-lg shadow-md p-6 border border-gray-200" 
     id="id-capture-{{ $bookingId }}"
     data-id-verification-form>
    
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-[#8f5d54] bg-opacity-10 rounded-full flex items-center justify-center">
            <i class="bi bi-person-badge text-[#8f5d54] text-xl"></i>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ $discountType === 'senior' ? 'Senior Citizen' : 'PWD' }} ID Verification
            </h3>
            <p class="text-sm text-gray-500">Upload or capture your ID for instant verification</p>
        </div>
        
        {{-- Status Badge --}}
        @if($currentStatus === 'verified')
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                ✓ Verified
            </span>
        @elseif($currentStatus === 'pending')
            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                ⏳ Pending
            </span>
        @elseif($currentStatus === 'rejected')
            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                ✗ Rejected
            </span>
        @endif
    </div>

    {{-- AI Verification Status Display --}}
    <div id="status-message-{{ $bookingId }}" data-id-status class="mb-4 {{ $currentStatus === 'not_required' ? 'hidden' : '' }}">
        @if($currentStatus === 'verified')
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">✓ ID Verified</p>
                        <p class="text-xs text-green-600 mt-1">Your discount has been applied.</p>
                    </div>
                </div>
            </div>
        @elseif($currentStatus === 'pending')
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">⏳ Pending Review</p>
                        <p class="text-xs text-yellow-600 mt-1">Your ID is being reviewed by our team.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ID Type Selection --}}
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
        <div class="flex space-x-4">
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="id_type" value="senior" 
                       id="id-type-{{ $bookingId }}"
                       data-id-type-input
                       {{ $discountType === 'senior' ? 'checked' : '' }}
                       class="w-4 h-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]">
                <span class="ml-2 text-sm text-gray-700">Senior Citizen ID</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="id_type" value="pwd" 
                       data-id-type-input
                       {{ $discountType === 'pwd' ? 'checked' : '' }}
                       class="w-4 h-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]">
                <span class="ml-2 text-sm text-gray-700">PWD ID</span>
            </label>
        </div>
    </div>

    {{-- ID Number Input --}}
    <div class="mb-4">
        <label for="id-number-{{ $bookingId }}" class="block text-sm font-medium text-gray-700 mb-2">
            ID Number <span class="text-red-500">*</span>
        </label>
        <input 
            type="text" 
            id="id-number-{{ $bookingId }}" 
            name="id_number"
            data-id-number-input
            value="{{ $idNumber }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none transition-colors"
            placeholder="{{ $discountType === 'senior' ? 'OSC-123456' : 'PWD-123456' }}"
            required
        >
        <p class="text-xs text-gray-500 mt-1">
            Format: {{ $discountType === 'senior' ? 'OSC-XXXXXX or SC-XXXXXX' : 'PWD-XXXXXX, IP-XXXXXX, or MC-XXXXXX' }}
        </p>
    </div>

    {{-- Image Preview Area --}}
    <div id="preview-container-{{ $bookingId }}" data-preview-container class="mb-4 {{ $existingImage ? '' : 'hidden' }}">
        <label class="block text-sm font-medium text-gray-700 mb-2">ID Preview</label>
        <div class="relative">
            <img 
                id="id-preview-{{ $bookingId }}" 
                src="{{ $existingImage ? asset('storage/' . $existingImage) : '' }}" 
                alt="ID Preview" 
                class="w-full max-h-64 object-contain rounded-lg border border-gray-200 {{ $existingImage ? '' : 'hidden' }}"
            >
            @if($existingImage)
                <div class="absolute top-2 right-2">
                    <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">Current</span>
                </div>
            @endif
            <button 
                type="button"
                id="btn-remove-image-{{ $bookingId }}"
                class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors shadow-md {{ $existingImage ? '' : 'hidden' }}"
                title="Remove image"
            >
                <i class="bi bi-trash text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Capture/Upload Options --}}
    @if(!in_array($currentStatus, ['verified']))
    <div class="grid grid-cols-2 gap-3 mb-4">
        {{-- Camera Capture Button --}}
        <button 
            type="button"
            id="btn-capture-{{ $bookingId }}"
            data-id-camera-btn
            class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-[#8f5d54] rounded-lg hover:bg-[#8f5d54] hover:bg-opacity-5 transition-colors group"
        >
            <i class="bi bi-camera text-2xl text-[#8f5d54] group-hover:scale-110 transition-transform mb-2"></i>
            <span class="text-sm font-medium text-[#8f5d54]">Capture with Camera</span>
            <span class="text-xs text-gray-500 mt-1">Take a photo</span>
        </button>

        {{-- File Upload Button --}}
        <label class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-[#8f5d54] hover:bg-[#8f5d54] hover:bg-opacity-5 transition-colors group cursor-pointer">
            <i class="bi bi-upload text-2xl text-gray-400 group-hover:text-[#8f5d54] transition-colors mb-2"></i>
            <span class="text-sm font-medium text-gray-600 group-hover:text-[#8f5d54]">Upload File</span>
            <span class="text-xs text-gray-500 mt-1">Select from device</span>
            <input 
                type="file" 
                id="id-file-input-{{ $bookingId }}" 
                data-id-image-input
                accept="image/jpeg,image/png,image/jpg"
                class="hidden"
            >
        </label>
    </div>

    {{-- Hidden Camera Input (for mobile) --}}
    <input 
        type="file" 
        id="id-camera-input-{{ $bookingId }}" 
        data-camera-input
        accept="image/*" 
        capture="environment"
        class="hidden"
    >
    @endif

    {{-- Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
        <div class="flex items-start gap-2">
            <i class="bi bi-info-circle text-blue-500 mt-0.5"></i>
            <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">Tips for best results:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li>Ensure good lighting</li>
                    <li>Keep the ID flat and clearly visible</li>
                    <li>All corners of the ID should be visible</li>
                    <li>Text should be readable</li>
                    <li>Our AI will verify your ID instantly!</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Alternative: Present at Check-in --}}
    @if(!in_array($currentStatus, ['verified', 'pending']))
    <div class="border-t border-gray-200 pt-4 mb-4">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="present-at-checkin-{{ $bookingId }}" 
                       name="verification_method" 
                       type="radio" 
                       value="present_at_checkin"
                       class="w-4 h-4 text-[#8f5d54] border-gray-300 focus:ring-[#8f5d54]">
            </div>
            <div class="ml-3 text-sm">
                <label for="present-at-checkin-{{ $bookingId }}" class="font-medium text-gray-700 cursor-pointer">
                    Present ID at Check-in Instead
                </label>
                <p class="text-gray-500 text-xs">
                    You can present your physical ID when you arrive. Note: If you don't bring your ID, 
                    you'll need to pay the discount difference (20% + VAT).
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Submit Button --}}
    @if(!in_array($currentStatus, ['verified']))
    <button 
        type="button"
        id="btn-submit-id-{{ $bookingId }}"
        data-submit-btn
        class="w-full bg-[#8f5d54] text-white py-3 px-4 rounded-lg font-medium hover:bg-[#7a4a42] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        disabled
    >
        <i class="bi bi-check-circle"></i>
        <span id="submit-text-{{ $bookingId }}">Upload ID to Verify</span>
    </button>
    
    {{-- Strict Verification Notice --}}
    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
        <p class="text-xs text-amber-800 flex items-start gap-2">
            <i class="bi bi-shield-check text-amber-600 mt-0.5"></i>
            <span>
                <strong>Strict Verification:</strong> Our AI system verifies that your image is a valid Senior/PWD ID. 
                Random images or unclear photos will be rejected. You must upload a clear photo of your actual ID card to proceed.
            </span>
        </p>
    </div>
    @endif


    {{-- Security Note --}}
    <div class="mt-4 p-3 bg-gray-50 rounded text-xs text-gray-500">
        <p class="flex items-center">
            <i class="bi bi-shield-check text-green-500 mr-2"></i>
            Your ID is securely encrypted and only used for verification. 
            It will be deleted after your visit.
        </p>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the new AI-powered ID Capture
    const idCapture = new IdCapture({
        previewElementId: 'id-preview-{{ $bookingId }}',
        captureButtonId: 'btn-capture-{{ $bookingId }}',
        uploadButtonId: 'btn-upload-{{ $bookingId }}',
        fileInputId: 'id-file-input-{{ $bookingId }}',
        cameraInputId: 'id-camera-input-{{ $bookingId }}',
        idNumberInputId: 'id-number-{{ $bookingId }}',
        idTypeInputId: 'id-type-{{ $bookingId }}',
        submitButtonId: 'btn-submit-id-{{ $bookingId }}',
        statusContainerId: 'status-message-{{ $bookingId }}',
        apiEndpoint: '/api/id-verification',
        bookingId: '{{ $bookingId }}',
        discountType: '{{ $discountType }}',
        onVerified: function(result) {
            console.log('ID Verified:', result);
            // Enable discount application
            document.dispatchEvent(new CustomEvent('idVerified', { 
                detail: { bookingId: '{{ $bookingId }}', result: result } 
            }));
        },
        onPending: function(result) {
            console.log('ID Pending:', result);
            document.dispatchEvent(new CustomEvent('idPending', { 
                detail: { bookingId: '{{ $bookingId }}', result: result } 
            }));
        },
        onRejected: function(result) {
            console.log('ID Rejected:', result);
            document.dispatchEvent(new CustomEvent('idRejected', { 
                detail: { bookingId: '{{ $bookingId }}', result: result } 
            }));
        },
        onError: function(error) {
            console.error('Verification Error:', error);
        }
    });

    // Handle remove image button
    const removeBtn = document.getElementById('btn-remove-image-{{ $bookingId }}');
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            idCapture.reset();
            document.getElementById('preview-container-{{ $bookingId }}').classList.add('hidden');
            this.classList.add('hidden');
        });
    }

    // Show preview container when image is captured
    const originalShowPreview = idCapture.showPreview;
    idCapture.showPreview = function(imageSrc) {
        originalShowPreview.call(this, imageSrc);
        document.getElementById('preview-container-{{ $bookingId }}').classList.remove('hidden');
        const removeBtn = document.getElementById('btn-remove-image-{{ $bookingId }}');
        if (removeBtn) removeBtn.classList.remove('hidden');
    };
});
</script>
@endpush
