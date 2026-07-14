@props([
    'guestBlueprints' => [],
    'campWaiverContent' => [],
    'showHeader' => true,
    'showGuestCards' => true,
    'showTermsAndPolicy' => true,
    'showCampWaiver' => true,
    'skipGuestDetails' => false,
])

@php
    $guestBlueprints = is_array($guestBlueprints ?? null) ? $guestBlueprints : [];
    $campWaiverContent = is_array($campWaiverContent ?? null) ? $campWaiverContent : [];
    $campWaiverService = app(\App\Services\BookingConfirmationContentService::class);
    $campWaiverTitle = trim((string) data_get($campWaiverContent, 'camp_waiver.title', '')) !== ''
        ? data_get($campWaiverContent, 'camp_waiver.title')
        : 'Camp Waiver and Safety Guidelines';
    $campWaiverBody = trim((string) data_get($campWaiverContent, 'camp_waiver.body', '')) !== ''
        ? data_get($campWaiverContent, 'camp_waiver.body')
        : $campWaiverService->defaultCampWaiverBody();
    $adultGuestCount = collect($guestBlueprints)->where('guest_type', 'adult')->count();
    $childGuestCount = collect($guestBlueprints)->where('guest_type', 'child')->count();
    $totalGuestCount = count($guestBlueprints);
    $workflowSpacing = $showHeader || $showGuestCards ? 'space-y-4' : 'space-y-2';
@endphp

<div class="{{ $workflowSpacing }}" data-guest-waiver-workflow>
    @if($showHeader)
        <div class="overflow-hidden rounded-2xl border border-[#d9cec5] bg-[#fffdfb]">
            <div class="p-5 sm:p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#8f5d54]">Staycation checkout</p>
                        <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Guest details and waivers</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Every guest must have complete personal details and a valid e-signature before payment.
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-3 overflow-hidden rounded-xl border border-[#e3d8cf] bg-white text-center"
                        data-total-guests="{{ $totalGuestCount }}"
                        data-adult-guests="{{ $adultGuestCount }}"
                        data-child-guests="{{ $childGuestCount }}"
                    >
                        <div class="px-4 py-3">
                            <p class="text-xl font-bold text-slate-900">{{ $totalGuestCount }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Total</p>
                        </div>
                        <div class="border-x border-[#eee5de] px-4 py-3">
                            <p class="text-xl font-bold text-[#8f5d54]">{{ $adultGuestCount }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Adults</p>
                        </div>
                        <div class="px-4 py-3">
                            <p class="text-xl font-bold text-amber-700">{{ $childGuestCount }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Children</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex items-start gap-3 rounded-xl bg-[#f5ece7] px-4 py-3 text-sm text-[#704a43]">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v3.5c0 .414.336.75.75.75h2.5a.75.75 0 0 0 0-1.5h-1.75V6.75Zm0 6.5a.75.75 0 1 0-1.5 0 .75.75 0 0 0 1.5 0Z" clip-rule="evenodd" />
                    </svg>
                    <p><strong>Required for all {{ $totalGuestCount }} guests.</strong> Payment stays locked until every card and e-signature is complete.</p>
                </div>
            </div>

            <div class="border-t border-[#e7ddd5] bg-white px-5 py-3 sm:px-6">
                <div class="mb-2 flex items-center justify-between gap-3 text-xs font-semibold">
                    <span class="text-slate-600">Completion</span>
                    <span class="text-[#8f5d54]" data-guest-progress-label>0 of {{ $totalGuestCount }} ready</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $totalGuestCount }}" aria-valuenow="0" data-guest-progress>
                    <div class="h-full w-0 rounded-full bg-[#8f5d54] transition-all duration-300" data-guest-progress-bar></div>
                </div>
            </div>
        </div>
    @endif

    @if($showGuestCards)
        <div class="space-y-2.5">
            @error('guests')
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ $message }}
                </div>
            @enderror

            @foreach($guestBlueprints as $blueprint)
                <x-booking.staycation-guest-card
                    :blueprint="$blueprint"
                    :isFirst="$loop->first"
                    :hasNextGuest="! $loop->last"
                />
            @endforeach
        </div>
    @endif

    @if($showTermsAndPolicy)
        <div class="rounded-2xl border border-slate-200 bg-[#faf7f4] p-4 sm:p-6">
            <div class="flex items-start gap-3">
                <input
                    type="checkbox"
                    id="terms_and_policy_accepted"
                    name="terms_and_policy_accepted"
                    class="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 text-[#8f5d54] focus:ring-[#8f5d54]"
                    {{ old('terms_and_policy_accepted') ? 'checked' : '' }}
                    required
                >
                <label for="terms_and_policy_accepted" class="text-sm leading-6 text-slate-700">
                    <strong>I agree to the booking terms and cancellation policy.</strong><br>
                    I understand that my reservation is subject to availability, the selected cancellation policy, and the information I entered for each guest must be complete and accurate.
                </label>
            </div>
            @error('terms_and_policy_accepted')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    @if($showCampWaiver)
        <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
            <div class="flex items-start gap-3">
                <input
                    type="checkbox"
                    id="camp_waiver_accepted"
                    name="camp_waiver_accepted"
                    class="mt-1 h-4 w-4 shrink-0 rounded border-rose-300 text-rose-600 focus:ring-rose-600"
                    {{ old('camp_waiver_accepted') ? 'checked' : '' }}
                    required
                >
                <label for="camp_waiver_accepted" class="text-sm leading-6 text-slate-700">
                    <strong>I acknowledge and agree to the {{ $campWaiverTitle }}.</strong><br>
                    I have reviewed the waiver text below and confirm that the guest details and signatures above are true and complete.
                </label>
            </div>
            @error('camp_waiver_accepted')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <details class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <summary class="cursor-pointer text-sm font-semibold text-slate-900">Read full waiver text</summary>
                <div class="mt-3 max-h-72 overflow-y-auto whitespace-pre-line text-sm leading-6 text-slate-700">
                    {!! nl2br(e($campWaiverBody)) !!}
                </div>
            </details>
        </div>
    @endif

    @if($skipGuestDetails)
        <input type="hidden" name="skip_guest_details" value="1">
    @endif
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-guest-waiver-workflow]').forEach(function (workflow) {
                const cards = Array.from(workflow.querySelectorAll('[data-staycation-signature-card]'));
                const progress = workflow.querySelector('[data-guest-progress]');
                const progressBar = workflow.querySelector('[data-guest-progress-bar]');
                const progressLabel = workflow.querySelector('[data-guest-progress-label]');

                if (cards.length === 0) {
                    return;
                }

                const setCardStatus = function (card, complete) {
                    const status = card.querySelector('[data-guest-card-status]');
                    if (!status) return;

                    status.innerHTML = complete
                        ? '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Ready'
                        : '<span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Required';
                    status.className = complete
                        ? 'inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 sm:text-xs'
                        : 'inline-flex shrink-0 items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 sm:text-xs';
                };

                const isCardComplete = function (card) {
                    const requiredFields = Array.from(card.querySelectorAll('input[required], select[required], textarea[required]'));
                    const signatureInput = card.querySelector('[data-signature-input]');

                    return requiredFields.every(function (field) {
                        return String(field.value || '').trim() !== '' && field.checkValidity();
                    }) && Boolean(signatureInput && signatureInput.value);
                };

                const updateProgress = function () {
                    const completeCount = cards.filter(isCardComplete).length;
                    const percentage = Math.round((completeCount / cards.length) * 100);

                    cards.forEach(function (card) {
                        setCardStatus(card, isCardComplete(card));
                    });

                    if (progressBar) progressBar.style.width = percentage + '%';
                    if (progressLabel) progressLabel.textContent = completeCount + ' of ' + cards.length + ' ready';
                    if (progress) progress.setAttribute('aria-valuenow', String(completeCount));
                };

                const focusFirstMissingField = function (card) {
                    const missingField = Array.from(card.querySelectorAll('input[required], select[required], textarea[required]')).find(function (field) {
                        return String(field.value || '').trim() === '' || !field.checkValidity();
                    });

                    if (missingField) {
                        missingField.focus();
                        missingField.reportValidity();
                        return;
                    }

                    const canvas = card.querySelector('[data-signature-canvas]');
                    const signatureStatus = card.querySelector('[data-signature-status]');
                    const signatureLabel = card.querySelector('[data-signature-status-label]');
                    const signatureDot = card.querySelector('[data-signature-status-dot]');

                    if (signatureStatus) signatureStatus.className = 'mt-2 flex items-center gap-2 text-xs font-medium text-red-600';
                    if (signatureLabel) signatureLabel.textContent = 'Please add the required signature';
                    if (signatureDot) signatureDot.className = 'h-2 w-2 rounded-full bg-red-500';
                    if (canvas) {
                        canvas.focus();
                    }
                };

                cards.forEach(function (card, cardIndex) {
                    const canvas = card.querySelector('[data-signature-canvas]');
                    const input = card.querySelector('[data-signature-input]');
                    const clearButton = card.querySelector('[data-signature-clear]');
                    const nextButton = card.querySelector('[data-next-guest]');
                    const signatureStatus = card.querySelector('[data-signature-status]');
                    const signatureLabel = card.querySelector('[data-signature-status-label]');
                    const signatureDot = card.querySelector('[data-signature-status-dot]');

                    if (!canvas || !input || !clearButton) return;

                    canvas.width = 1200;
                    canvas.height = 320;
                    const context = canvas.getContext('2d');
                    let drawing = false;
                    let strokeHasInk = false;

                    const clearCanvas = function () {
                        context.setTransform(1, 0, 0, 1, 0, 0);
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                        context.lineWidth = 6;
                        context.lineCap = 'round';
                        context.lineJoin = 'round';
                        context.strokeStyle = '#111827';
                    };

                    const setSignatureStatus = function (signed) {
                        if (signatureStatus) signatureStatus.className = signed
                            ? 'mt-2 flex items-center gap-2 text-xs font-medium text-emerald-700'
                            : 'mt-2 flex items-center gap-2 text-xs font-medium text-slate-500';
                        if (signatureLabel) signatureLabel.textContent = signed ? 'Signature captured' : 'Signature required';
                        if (signatureDot) signatureDot.className = signed
                            ? 'h-2 w-2 rounded-full bg-emerald-500'
                            : 'h-2 w-2 rounded-full bg-slate-300';
                    };

                    const getPoint = function (event) {
                        const rect = canvas.getBoundingClientRect();
                        return {
                            x: (event.clientX - rect.left) * (canvas.width / rect.width),
                            y: (event.clientY - rect.top) * (canvas.height / rect.height),
                        };
                    };

                    const syncInput = function () {
                        input.value = canvas.toDataURL('image/png');
                        setSignatureStatus(true);
                        updateProgress();
                    };

                    clearCanvas();

                    if (input.value) {
                        const savedSignature = new Image();
                        savedSignature.onload = function () {
                            clearCanvas();
                            context.drawImage(savedSignature, 0, 0, canvas.width, canvas.height);
                        };
                        savedSignature.src = input.value;
                        setSignatureStatus(true);
                    }

                    canvas.addEventListener('pointerdown', function (event) {
                        event.preventDefault();
                        drawing = true;
                        strokeHasInk = false;
                        const point = getPoint(event);
                        context.beginPath();
                        context.moveTo(point.x, point.y);
                        canvas.setPointerCapture(event.pointerId);
                    });

                    canvas.addEventListener('pointermove', function (event) {
                        if (!drawing) return;
                        event.preventDefault();
                        const point = getPoint(event);
                        context.lineTo(point.x, point.y);
                        context.stroke();
                        strokeHasInk = true;
                    });

                    canvas.addEventListener('pointerup', function (event) {
                        if (!drawing) return;
                        drawing = false;
                        try {
                            canvas.releasePointerCapture(event.pointerId);
                        } catch (error) {
                            // Pointer capture may already be released by the browser.
                        }

                        if (strokeHasInk) syncInput();
                    });

                    canvas.addEventListener('pointercancel', function () {
                        drawing = false;
                        if (strokeHasInk) syncInput();
                    });

                    clearButton.addEventListener('click', function () {
                        clearCanvas();
                        input.value = '';
                        setSignatureStatus(false);
                        updateProgress();
                    });

                    card.querySelectorAll('input, select, textarea').forEach(function (field) {
                        if (field === input) return;
                        field.addEventListener('input', updateProgress);
                        field.addEventListener('change', updateProgress);
                    });

                    card.addEventListener('toggle', function () {
                        if (!card.open) return;
                        cards.forEach(function (otherCard) {
                            if (otherCard !== card) otherCard.open = false;
                        });
                    });

                    if (nextButton) {
                        nextButton.addEventListener('click', function () {
                            if (!isCardComplete(card)) {
                                focusFirstMissingField(card);
                                return;
                            }

                            const nextCard = cards[cardIndex + 1];
                            if (!nextCard) return;
                            card.open = false;
                            nextCard.open = true;
                            nextCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        });
                    }
                });

                const form = workflow.closest('form');
                if (form) {
                    form.addEventListener('submit', function (event) {
                        const firstIncompleteCard = cards.find(function (card) {
                            return !isCardComplete(card);
                        });

                        if (!firstIncompleteCard) return;
                        event.preventDefault();
                        firstIncompleteCard.open = true;
                        firstIncompleteCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        focusFirstMissingField(firstIncompleteCard);
                    });
                }

                updateProgress();
            });
        });
    </script>
@endonce
