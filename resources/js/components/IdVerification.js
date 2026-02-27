/**
 * ID Verification Component
 * Real-time AI-powered ID verification for Senior/PWD discounts
 */

class IdVerification {
    constructor(options = {}) {
        this.options = {
            apiEndpoint: '/api/id-verification',
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
            maxRetries: 3
        };

        this.elements = {};
        this.init();
    }

    init() {
        this.bindElements();
        this.attachEventListeners();
    }

    bindElements() {
        // Find all ID verification forms on the page
        this.elements.forms = document.querySelectorAll('[data-id-verification-form]');
        this.elements.fileInputs = document.querySelectorAll('[data-id-image-input]');
        this.elements.cameraButtons = document.querySelectorAll('[data-id-camera-btn]');
        this.elements.previewContainers = document.querySelectorAll('[data-id-preview]');
        this.elements.statusContainers = document.querySelectorAll('[data-id-status]');
        this.elements.idNumberInputs = document.querySelectorAll('[data-id-number-input]');
        this.elements.idTypeInputs = document.querySelectorAll('[data-id-type-input]');
    }

    attachEventListeners() {
        // File input change
        this.elements.fileInputs.forEach(input => {
            input.addEventListener('change', (e) => this.handleFileSelect(e));
        });

        // Camera capture
        this.elements.cameraButtons.forEach(btn => {
            btn.addEventListener('click', (e) => this.openCameraModal(e));
        });

        // Real-time validation on ID number input
        this.elements.idNumberInputs.forEach(input => {
            input.addEventListener('blur', (e) => this.validateIdNumber(e.target.value));
        });
    }

    /**
     * Handle file selection
     */
    async handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file
        const validation = this.validateFile(file);
        if (!validation.valid) {
            this.showError(validation.message);
            return;
        }

        // Show preview
        await this.showPreview(file, event.target);

        // Auto-start verification if we have all required data
        const form = this.findParentForm(event.target);
        if (form && this.canVerify(form)) {
            await this.startVerification(form, file);
        }
    }

    /**
     * Validate file before upload
     */
    validateFile(file) {
        const maxSize = 2 * 1024 * 1024; // 2MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!allowedTypes.includes(file.type)) {
            return {
                valid: false,
                message: 'Please upload a valid image file (JPEG or PNG)'
            };
        }

        if (file.size > maxSize) {
            return {
                valid: false,
                message: 'File size must be less than 2MB'
            };
        }

        return { valid: true };
    }

    /**
     * Show image preview
     */
    showPreview(file, inputElement) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            const previewContainer = this.findPreviewContainer(inputElement);
            
            reader.onload = (e) => {
                if (previewContainer) {
                    previewContainer.innerHTML = `
                        <div class="relative">
                            <img src="${e.target.result}" class="max-w-full h-auto rounded-lg border-2 border-gray-300" alt="ID Preview">
                            <div class="absolute top-2 right-2">
                                <button type="button" class="bg-red-500 text-white rounded-full p-1 hover:bg-red-600" onclick="this.closest('.relative').remove()">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                }
                resolve(e.target.result);
            };
            
            reader.readAsDataURL(file);
        });
    }

    /**
     * Start the verification process
     */
    async startVerification(form, file) {
        if (this.state.isVerifying) {
            console.log('Verification already in progress');
            return;
        }

        this.state.isVerifying = true;
        this.showLoading(form, 'Verifying ID...');

        try {
            const idNumber = this.getIdNumber(form);
            const idType = this.getIdType(form);

            // First, do a quick quality check
            const quickCheck = await this.quickCheck(file);
            if (!quickCheck.valid) {
                this.showQualityWarning(form, quickCheck);
                // Continue with full verification anyway, but warn user
            }

            // Full verification
            const result = await this.verifyId(file, idNumber, idType);
            this.handleVerificationResult(result, form);

        } catch (error) {
            console.error('Verification error:', error);
            this.handleError(error, form);
        } finally {
            this.state.isVerifying = false;
        }
    }

    /**
     * Quick quality check before full verification
     */
    async quickCheck(file) {
        const formData = new FormData();
        formData.append('id_image', file);

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
    async verifyId(file, idNumber, idType) {
        const formData = new FormData();
        formData.append('id_image', file);
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
     */
    handleVerificationResult(result, form) {
        this.state.lastResult = result;

        const statusContainer = this.findStatusContainer(form);
        
        if (result.is_verified) {
            this.showVerifiedStatus(statusContainer, result);
            this.updateFormStatus(form, 'verified', result);
            
            if (this.options.onVerified) {
                this.options.onVerified(result);
            }
        } else if (result.requires_manual_review) {
            this.showPendingStatus(statusContainer, result);
            this.updateFormStatus(form, 'pending_review', result);
            
            if (this.options.onPending) {
                this.options.onPending(result);
            }
        } else {
            this.showRejectedStatus(statusContainer, result);
            this.updateFormStatus(form, 'rejected', result);
            
            if (this.options.onRejected) {
                this.options.onRejected(result);
            }
        }
    }

    /**
     * Show verified status UI
     */
    showVerifiedStatus(container, result) {
        if (!container) return;

        container.innerHTML = `
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium">
                            ✓ ID Verified Automatically
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            Confidence: ${result.confidence_score?.toFixed(1) || 'N/A'}%
                        </p>
                        ${result.warnings?.length ? `
                            <div class="mt-2 text-xs text-yellow-600">
                                <p>Notes:</p>
                                <ul class="list-disc list-inside">
                                    ${result.warnings.map(w => `<li>${w}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Show pending review status UI
     */
    showPendingStatus(container, result) {
        if (!container) return;

        container.innerHTML = `
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 font-medium">
                            ⏳ Pending Manual Review
                        </p>
                        <p class="text-xs text-yellow-600 mt-1">
                            ${result.message || 'Your ID will be reviewed by our team within 24 hours.'}
                        </p>
                        <p class="text-xs text-yellow-600 mt-1">
                            You can proceed with payment. If verification fails, you'll be contacted.
                        </p>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Show rejected status UI
     */
    showRejectedStatus(container, result) {
        if (!container) return;

        container.innerHTML = `
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium">
                            ✗ ID Verification Failed
                        </p>
                        <p class="text-xs text-red-600 mt-1">
                            ${result.message || 'Please upload a clearer image of your valid ID.'}
                        </p>
                        <button type="button" class="mt-2 text-xs text-red-600 underline hover:text-red-800" onclick="location.reload()">
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Show quality warning
     */
    showQualityWarning(form, check) {
        const container = this.findStatusContainer(form);
        if (!container) return;

        const issuesList = check.issues?.map(issue => `<li>${issue}</li>`).join('') || '';

        container.innerHTML = `
            <div class="bg-orange-50 border-l-4 border-orange-400 p-3 rounded mb-3">
                <p class="text-sm text-orange-700">
                    ⚠️ Image Quality Warning
                </p>
                <ul class="text-xs text-orange-600 mt-1 list-disc list-inside">
                    ${issuesList}
                </ul>
                <p class="text-xs text-orange-600 mt-1">
                    You can proceed, but verification may take longer.
                </p>
            </div>
        `;
    }

    /**
     * Show loading state
     */
    showLoading(form, message) {
        const container = this.findStatusContainer(form);
        if (!container) return;

        container.innerHTML = `
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <div class="flex items-center">
                    <svg class="animate-spin h-5 w-5 text-blue-500 mr-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-blue-700">${message}</span>
                </div>
            </div>
        `;
    }

    /**
     * Show error message
     */
    showError(message) {
        // Use toast or alert
        if (window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }

    /**
     * Handle errors
     */
    handleError(error, form) {
        console.error('ID Verification Error:', error);
        
        const container = this.findStatusContainer(form);
        if (container) {
            container.innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-sm text-red-700">Verification failed. Please try again.</p>
                    <p class="text-xs text-red-600 mt-1">${error.message}</p>
                </div>
            `;
        }

        if (this.options.onError) {
            this.options.onError(error);
        }
    }

    /**
     * Update form status fields
     */
    updateFormStatus(form, status, result) {
        // Add hidden fields for form submission
        const fields = {
            'verification_status': status,
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

        // Enable/disable submit button based on status
        const submitBtn = form.querySelector('button[type="submit"], [data-submit-btn]');
        if (submitBtn) {
            if (status === 'rejected') {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    /**
     * Validate ID number format
     */
    validateIdNumber(value) {
        // Senior ID: OSC-XXXXXX or SC-XXXXXX
        // PWD ID: PWD-XXXXXX or IP-XXXXXX or MC-XXXXXX
        const seniorPattern = /^(OSC|SC)-?\d{6,}$/i;
        const pwdPattern = /^(PWD|IP|MC)-?\d{6,}$/i;

        const isSenior = seniorPattern.test(value);
        const isPwd = pwdPattern.test(value);

        return {
            valid: isSenior || isPwd,
            type: isSenior ? 'senior' : (isPwd ? 'pwd' : null),
            message: isSenior || isPwd ? 'Valid ID format' : 'Invalid ID format. Expected: OSC-XXXXXX or PWD-XXXXXX'
        };
    }

    /**
     * Open camera modal for capture
     */
    openCameraModal(event) {
        // This would integrate with your existing IdCapture component
        // or open a modal with camera functionality
        const modal = document.getElementById('id-camera-modal');
        if (modal) {
            modal.classList.remove('hidden');
        } else {
            console.warn('Camera modal not found');
        }
    }

    /**
     * Helper: Find parent form
     */
    findParentForm(element) {
        return element.closest('form') || element.closest('[data-id-verification-form]');
    }

    /**
     * Helper: Find preview container
     */
    findPreviewContainer(element) {
        const form = this.findParentForm(element);
        return form?.querySelector('[data-id-preview]');
    }

    /**
     * Helper: Find status container
     */
    findStatusContainer(element) {
        const form = this.findParentForm(element);
        return form?.querySelector('[data-id-status]');
    }

    /**
     * Helper: Get ID number from form
     */
    getIdNumber(form) {
        const input = form.querySelector('[data-id-number-input]');
        return input?.value || '';
    }

    /**
     * Helper: Get ID type from form
     */
    getIdType(form) {
        const input = form.querySelector('[data-id-type-input]:checked') || 
                      form.querySelector('[data-id-type-input]');
        return input?.value || '';
    }

    /**
     * Helper: Check if we can verify
     */
    canVerify(form) {
        return this.getIdNumber(form) && this.getIdType(form);
    }

    /**
     * Helper: Get CSRF token
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || 
               document.querySelector('input[name="_token"]')?.value || '';
    }

    /**
     * Retry verification
     */
    async retry(form, file) {
        if (this.state.retryCount < this.state.maxRetries) {
            this.state.retryCount++;
            await this.startVerification(form, file);
        } else {
            this.showError('Maximum retry attempts reached. Please try again later.');
        }
    }

    /**
     * Reset verification state
     */
    reset() {
        this.state = {
            isVerifying: false,
            lastResult: null,
            retryCount: 0,
            maxRetries: 3
        };

        // Clear all status containers
        this.elements.statusContainers.forEach(container => {
            container.innerHTML = '';
        });
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if ID verification forms exist
    if (document.querySelectorAll('[data-id-verification-form]').length > 0) {
        window.idVerification = new IdVerification({
            onVerified: (result) => {
                console.log('ID Verified:', result);
                // Enable discount application
                document.dispatchEvent(new CustomEvent('idVerified', { detail: result }));
            },
            onPending: (result) => {
                console.log('ID Pending Review:', result);
                document.dispatchEvent(new CustomEvent('idPending', { detail: result }));
            },
            onRejected: (result) => {
                console.log('ID Rejected:', result);
                document.dispatchEvent(new CustomEvent('idRejected', { detail: result }));
            },
            onError: (error) => {
                console.error('Verification Error:', error);
            }
        });
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = IdVerification;
}
