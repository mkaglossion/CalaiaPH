/**
 * ID Capture Component with AI-Powered Real-Time Verification
 * Replaces the old IdCapture with intelligent ID verification
 * 
 * Features:
 * - Real-time OCR and image analysis
 * - Quality checks (blur, resolution, lighting)
 * - Three-tier verification: Auto-verify / Pending / Rejected
 * - Camera capture and file upload support
 */

class IdCapture {
    constructor(options = {}) {
        this.options = {
            previewElementId: 'id-preview',
            captureButtonId: 'btn-capture',
            uploadButtonId: 'btn-upload',
            fileInputId: 'id-file-input',
            cameraInputId: 'id-camera-input',
            idNumberInputId: 'id-number',
            idTypeInputId: 'id-type',
            submitButtonId: 'btn-submit-id',
            statusContainerId: 'status-message',
            apiEndpoint: '/api/id-verification',
            bookingId: null,
            discountType: 'senior',
            onVerified: null,
            onPending: null,
            onRejected: null,
            onError: null,
            ...options
        };

        this.state = {
            isVerifying: false,
            lastResult: null,
            retryCount: 0,
            maxRetries: 3,
            capturedImage: null,
            uploadMethod: null
        };

        this.elements = {};
        this.init();
    }

    init() {
        this.bindElements();
        this.bindEvents();
    }

    bindElements() {
        this.elements.preview = document.getElementById(this.options.previewElementId);
        this.elements.captureBtn = document.getElementById(this.options.captureButtonId);
        this.elements.uploadBtn = document.getElementById(this.options.uploadButtonId);
        this.elements.fileInput = document.getElementById(this.options.fileInputId);
        this.elements.cameraInput = document.getElementById(this.options.cameraInputId);
        this.elements.idNumber = document.getElementById(this.options.idNumberInputId);
        this.elements.idType = document.getElementById(this.options.idTypeInputId);
        this.elements.submitBtn = document.getElementById(this.options.submitButtonId);
        this.elements.statusContainer = document.getElementById(this.options.statusContainerId);
    }

    bindEvents() {
        // Camera capture button
        if (this.elements.captureBtn) {
            this.elements.captureBtn.addEventListener('click', () => this.openCamera());
        }

        // Upload button
        if (this.elements.uploadBtn) {
            this.elements.uploadBtn.addEventListener('click', () => this.openFilePicker());
        }

        // File input change
        if (this.elements.fileInput) {
            this.elements.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }

        // Camera input change (mobile camera)
        if (this.elements.cameraInput) {
            this.elements.cameraInput.addEventListener('change', (e) => this.handleCameraCapture(e));
        }

        // Submit button
        if (this.elements.submitBtn) {
            this.elements.submitBtn.addEventListener('click', () => this.submit());
        }

        // Real-time ID number validation
        if (this.elements.idNumber) {
            this.elements.idNumber.addEventListener('blur', () => this.validateIdNumber());
        }
    }

    /**
     * Validate ID number format
     */
    validateIdNumber() {
        const value = this.elements.idNumber?.value?.trim() || '';
        if (!value) return { valid: false, message: 'ID number is required' };

        // Senior ID: OSC-XXXXXX or SC-XXXXXX
        // PWD ID: PWD-XXXXXX or IP-XXXXXX or MC-XXXXXX
        const seniorPattern = /^(OSC|SC)-?\d{6,}$/i;
        const pwdPattern = /^(PWD|IP|MC)-?\d{6,}$/i;

        const isSenior = seniorPattern.test(value);
        const isPwd = pwdPattern.test(value);

        const result = {
            valid: isSenior || isPwd,
            type: isSenior ? 'senior' : (isPwd ? 'pwd' : null),
            message: isSenior || isPwd ? 'Valid ID format' : 'Invalid format. Use: OSC-XXXXXX or PWD-XXXXXX'
        };

        // Show validation feedback
        if (this.elements.idNumber) {
            if (result.valid) {
                this.elements.idNumber.classList.remove('border-red-500');
                this.elements.idNumber.classList.add('border-green-500');
            } else {
                this.elements.idNumber.classList.remove('border-green-500');
                this.elements.idNumber.classList.add('border-red-500');
            }
        }

        return result;
    }

    /**
     * Open camera for capturing ID
     */
    openCamera() {
        // Check if mobile device - use native camera
        if (this.isMobileDevice()) {
            this.triggerNativeCamera();
            return;
        }

        // Desktop - use getUserMedia
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            this.startCameraStream();
        } else {
            this.openFilePicker();
        }
    }

    isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    triggerNativeCamera() {
        if (this.elements.cameraInput) {
            this.elements.cameraInput.click();
        }
    }

    async startCameraStream() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                } 
            });
            this.showCameraModal(stream);
        } catch (err) {
            console.error('Camera access denied:', err);
            this.openFilePicker();
        }
    }

    showCameraModal(stream) {
        let modal = document.getElementById('camera-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'camera-modal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-lg p-4 max-w-lg w-full mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Capture ID</h3>
                        <button id="close-camera" class="text-gray-500 hover:text-gray-700">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="relative">
                        <video id="camera-video" class="w-full rounded-lg" autoplay playsinline></video>
                        <canvas id="camera-canvas" class="hidden"></canvas>
                    </div>
                    <div class="mt-4 flex justify-center">
                        <button id="btn-snap" class="bg-[#8f5d54] text-white px-6 py-2 rounded-lg hover:bg-[#7a4a42]">
                            <i class="bi bi-camera"></i> Take Photo
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        const video = document.getElementById('camera-video');
        const canvas = document.getElementById('camera-canvas');
        const snapButton = document.getElementById('btn-snap');
        const closeButton = document.getElementById('close-camera');

        video.srcObject = stream;
        video.play();

        snapButton.onclick = () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            this.state.capturedImage = canvas.toDataURL('image/jpeg', 0.9);
            this.state.uploadMethod = 'camera';
            
            this.showPreview(this.state.capturedImage);
            
            stream.getTracks().forEach(track => track.stop());
            modal.remove();
            
            // Auto-start verification if we have ID number
            if (this.elements.idNumber?.value) {
                this.startVerification();
            }
        };

        closeButton.onclick = () => {
            stream.getTracks().forEach(track => track.stop());
            modal.remove();
        };
    }

    openFilePicker() {
        if (this.elements.fileInput) {
            this.elements.fileInput.click();
        }
    }

    /**
     * Handle file selection with real-time verification
     */
    async handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file
        const validation = this.validateFile(file);
        if (!validation.valid) {
            this.showStatus('error', validation.message);
            return;
        }

        // Read and preview
        const reader = new FileReader();
        reader.onload = async (e) => {
            this.state.capturedImage = e.target.result;
            this.state.uploadMethod = 'upload';
            this.showPreview(this.state.capturedImage);
            
            // Auto-start verification if we have ID number
            if (this.elements.idNumber?.value) {
                await this.startVerification();
            }
        };
        reader.readAsDataURL(file);
    }

    handleCameraCapture(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = async (e) => {
            this.state.capturedImage = e.target.result;
            this.state.uploadMethod = 'camera';
            this.showPreview(this.state.capturedImage);
            
            if (this.elements.idNumber?.value) {
                await this.startVerification();
            }
        };
        reader.readAsDataURL(file);
    }

    validateFile(file) {
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!allowedTypes.includes(file.type)) {
            return { valid: false, message: 'Please upload a valid image file (JPEG or PNG)' };
        }

        if (file.size > maxSize) {
            return { valid: false, message: 'File size must be less than 2MB' };
        }

        return { valid: true };
    }

    showPreview(imageSrc) {
        if (this.elements.preview) {
            this.elements.preview.src = imageSrc;
            this.elements.preview.classList.remove('hidden');
            this.elements.preview.closest('[data-preview-container]')?.classList.remove('hidden');
        }

        if (this.elements.submitBtn) {
            this.elements.submitBtn.disabled = false;
        }
    }

    /**
     * Start AI-powered verification
     */
    async startVerification() {
        if (this.state.isVerifying) return;

        const idValidation = this.validateIdNumber();
        if (!idValidation.valid) {
            this.showStatus('error', 'Please enter a valid ID number first');
            return;
        }

        this.state.isVerifying = true;
        this.showLoading('Verifying ID with AI...');

        try {
            const idNumber = this.elements.idNumber.value.trim();
            const idType = this.elements.idType?.value || this.options.discountType;

            // First, quick quality check
            const quickCheck = await this.quickCheck(this.state.capturedImage);
            if (!quickCheck.valid) {
                this.showQualityWarning(quickCheck);
            }

            // Full verification
            const result = await this.verifyId(this.state.capturedImage, idNumber, idType);
            this.handleVerificationResult(result);

        } catch (error) {
            console.error('Verification error:', error);
            this.showStatus('error', 'Verification failed. Please try again.');
            if (this.options.onError) this.options.onError(error);
        } finally {
            this.state.isVerifying = false;
        }
    }

    /**
     * Quick quality check
     */
    async quickCheck(imageData) {
        const formData = new FormData();
        const blob = this.dataURLtoBlob(imageData);
        formData.append('id_image', blob, 'id.jpg');

        const response = await fetch(`${this.options.apiEndpoint}/quick-check`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        });

        return await response.json();
    }

    /**
     * Full ID verification
     */
    async verifyId(imageData, idNumber, idType) {
        const formData = new FormData();
        const blob = this.dataURLtoBlob(imageData);
        formData.append('id_image', blob, 'id.jpg');
        formData.append('id_number', idNumber);
        formData.append('id_type', idType);

        const response = await fetch(`${this.options.apiEndpoint}/verify`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Handle verification result
     * STRICT: Block submission on rejected status
     */
    handleVerificationResult(result) {
        this.state.lastResult = result;

        if (result.is_verified) {
            this.showVerifiedStatus(result);
            this.enableSubmit(true);
            if (this.options.onVerified) this.options.onVerified(result);
        } else if (result.requires_manual_review) {
            this.showPendingStatus(result);
            this.enableSubmit(true); // Allow proceed but with warning
            if (this.options.onPending) this.options.onPending(result);
        } else {
            this.showRejectedStatus(result);
            this.enableSubmit(false); // BLOCK: Cannot proceed
            if (this.options.onRejected) this.options.onRejected(result);
        }

        // Update form status
        this.updateFormStatus(result);
    }

    /**
     * Enable or disable submit button
     */
    enableSubmit(enabled) {
        if (this.elements.submitBtn) {
            this.elements.submitBtn.disabled = !enabled;
            if (enabled) {
                this.elements.submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                this.elements.submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Verify & Continue';
            } else {
                this.elements.submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                this.elements.submitBtn.innerHTML = '<i class="bi bi-x-circle"></i> Verification Failed - Try Again';
            }
        }
    }


    showVerifiedStatus(result) {
        const html = `
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">✓ ID Verified Automatically</p>
                        <p class="text-xs text-green-600 mt-1">Confidence: ${result.confidence_score?.toFixed(1) || 'N/A'}%</p>
                        ${result.warnings?.length ? `
                            <div class="mt-2 text-xs text-yellow-600">
                                <p>Notes:</p>
                                <ul class="list-disc list-inside">${result.warnings.map(w => `<li>${w}</li>`).join('')}</ul>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        this.setStatusHtml(html);
    }

    showPendingStatus(result) {
        const html = `
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">⏳ Pending Manual Review</p>
                        <p class="text-xs text-yellow-600 mt-1">${result.message || 'Your ID will be reviewed within 24 hours.'}</p>
                        <p class="text-xs text-yellow-600 mt-1">You can proceed with payment.</p>
                    </div>
                </div>
            </div>
        `;
        this.setStatusHtml(html);
    }

    showRejectedStatus(result) {
        const reasons = result.warnings?.join(', ') || 'Invalid or unclear ID image';
        const html = `
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">✗ Verification Failed - Cannot Proceed</p>
                        <p class="text-xs text-red-600 mt-1">${result.message || 'The uploaded image does not appear to be a valid ID.'}</p>
                        <p class="text-xs text-red-500 mt-2 font-medium">Reason: ${reasons}</p>
                        <div class="mt-3 flex gap-2">
                            <button type="button" onclick="window.idCaptureRetry()" 
                                    class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">
                                Try Different Image
                            </button>
                            <button type="button" onclick="window.idCapturePresentAtCheckin()" 
                                    class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                                Present ID at Check-in Instead
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        this.setStatusHtml(html);
        
        // Store reference for retry
        window.idCaptureRetry = () => {
            this.reset();
            this.showStatus('info', 'Please upload a clear photo of your actual Senior/PWD ID card');
        };
        
        window.idCapturePresentAtCheckin = () => {
            const checkbox = document.querySelector('[name="verification_method"][value="present_at_checkin"]');
            if (checkbox) {
                checkbox.checked = true;
                this.showStatus('info', 'You have selected to present your ID at check-in. Please bring your physical ID or you will pay the discount difference.');
                this.enableSubmit(true);
            }
        };
    }


    showQualityWarning(check) {
        const issues = check.issues?.map(i => `<li>${i}</li>`).join('') || '';
        const html = `
            <div class="bg-orange-50 border-l-4 border-orange-400 p-3 rounded mb-3">
                <p class="text-sm font-medium text-orange-800">⚠️ Image Quality Warning</p>
                <ul class="text-xs text-orange-600 mt-1 list-disc list-inside">${issues}</ul>
                <p class="text-xs text-orange-600 mt-1">You can proceed, but verification may take longer.</p>
            </div>
        `;
        this.setStatusHtml(html, true);
    }

    showLoading(message) {
        const html = `
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex items-center">
                    <svg class="animate-spin h-5 w-5 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-blue-700">${message}</span>
                </div>
            </div>
        `;
        this.setStatusHtml(html);
    }

    showStatus(type, message) {
        const colors = {
            error: 'bg-red-50 border-red-500 text-red-700',
            success: 'bg-green-50 border-green-500 text-green-700',
            warning: 'bg-yellow-50 border-yellow-500 text-yellow-700'
        };
        
        const html = `
            <div class="${colors[type] || colors.error} border-l-4 p-4 rounded-lg">
                <p class="text-sm font-medium">${message}</p>
            </div>
        `;
        this.setStatusHtml(html);
    }

    setStatusHtml(html, append = false) {
        if (this.elements.statusContainer) {
            if (append) {
                this.elements.statusContainer.insertAdjacentHTML('beforeend', html);
            } else {
                this.elements.statusContainer.innerHTML = html;
            }
            this.elements.statusContainer.classList.remove('hidden');
        }
    }

    updateFormStatus(result) {
        // Add hidden fields for form submission
        const form = this.elements.idNumber?.closest('form');
        if (!form) return;

        const fields = {
            'verification_status': result.status,
            'verification_confidence': result.confidence_score || 0,
            'verification_result': JSON.stringify(result)
        };

        Object.entries(fields).forEach(([name, value]) => {
            let input = form.querySelector(`[name="${name}"]`);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                form.appendChild(input);
            }
            input.value = value;
        });

        // Enable/disable submit based on status
        if (this.elements.submitBtn) {
            if (result.status === 'rejected') {
                this.elements.submitBtn.disabled = true;
                this.elements.submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                this.elements.submitBtn.disabled = false;
                this.elements.submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    /**
     * Submit ID - STRICT: Only proceed if verified or pending
     */
    async submit() {
        // Check current verification status
        const result = this.state.lastResult;
        
        if (!result) {
            this.showStatus('error', 'Please upload an ID image first');
            return;
        }

        // STRICT: Block if rejected
        if (result.status === 'rejected') {
            this.showStatus('error', 'Cannot proceed: ID verification failed. Please upload a valid ID or select "Present at Check-in" option.');
            return;
        }

        // Allow if verified
        if (result.is_verified) {
            if (this.options.onVerified) {
                this.options.onVerified(result);
            }
            return;
        }

        // Allow if pending review (with warning)
        if (result.requires_manual_review) {
            if (this.options.onPending) {
                this.options.onPending(result);
            }
            return;
        }

        // Default: start verification
        await this.startVerification();
    }


    dataURLtoBlob(dataURL) {
        const arr = dataURL.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    reset() {
        this.state = {
            isVerifying: false,
            lastResult: null,
            retryCount: 0,
            maxRetries: 3,
            capturedImage: null,
            uploadMethod: null
        };

        if (this.elements.preview) {
            this.elements.preview.src = '';
            this.elements.preview.classList.add('hidden');
        }
        
        if (this.elements.idNumber) {
            this.elements.idNumber.value = '';
            this.elements.idNumber.classList.remove('border-green-500', 'border-red-500');
        }
        
        if (this.elements.fileInput) this.elements.fileInput.value = '';
        if (this.elements.cameraInput) this.elements.cameraInput.value = '';
        if (this.elements.submitBtn) this.elements.submitBtn.disabled = true;
        if (this.elements.statusContainer) {
            this.elements.statusContainer.innerHTML = '';
            this.elements.statusContainer.classList.add('hidden');
        }
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IdCapture;
} else {
    window.IdCapture = IdCapture;
}
