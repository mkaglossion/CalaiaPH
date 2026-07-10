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
@endphp

<div class="space-y-4" data-guest-waiver-workflow>
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

    <div class="space-y-2.5">
        @error('guests')
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ $message }}
            </div>
        @enderror

        @foreach($guestBlueprints as $blueprint)
            @php
                $index = (int) ($blueprint['index'] ?? 0);
                $guestType = $blueprint['guest_type'] ?? 'adult';
                $isChild = $guestType === 'child';
                $fieldPrefix = 'guests.' . $index . '.';
                $fullName = old($fieldPrefix . 'full_name', '');
                $birthDate = old($fieldPrefix . 'birth_date', '');
                $gender = old($fieldPrefix . 'gender', '');
                $contactNumber = old($fieldPrefix . 'contact_number', '');
                $emergencyName = old($fieldPrefix . 'emergency_contact_name', '');
                $emergencyNumber = old($fieldPrefix . 'emergency_contact_number', '');
                $emergencyRelationship = old($fieldPrefix . 'emergency_contact_relationship', '');
                $guardianFullName = old($fieldPrefix . 'guardian_full_name', '');
                $guardianRelationship = old($fieldPrefix . 'guardian_relationship', '');
                $signatureData = old($fieldPrefix . 'signature_data', '');
            @endphp

            <details
                class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white transition open:border-[#b98d81] open:shadow-[0_10px_30px_rgba(81,54,48,0.08)]"
                data-staycation-signature-card
                data-guest-card-index="{{ $index }}"
                @if($loop->first || $errors->has($fieldPrefix . '*')) open @endif
            >
                <span class="absolute inset-y-0 left-0 w-1 {{ $isChild ? 'bg-amber-400' : 'bg-[#b77b6d]' }}" aria-hidden="true"></span>
                <summary class="flex cursor-pointer list-none items-center gap-3 py-3.5 pl-5 pr-4 marker:hidden sm:gap-4 sm:py-4 sm:pl-6 [&::-webkit-details-marker]:hidden">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $isChild ? 'bg-amber-50 text-amber-700' : 'bg-[#f7eeea] text-[#8f5d54]' }}">
                        @if($isChild)
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM4.5 17.25a5.5 5.5 0 0 1 11 0 .75.75 0 0 1-.75.75h-9.5a.75.75 0 0 1-.75-.75Z" /></svg>
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 8a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM3 18a7 7 0 1 1 14 0H3Z" /></svg>
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-slate-900">Guest {{ $index + 1 }}</span>
                            <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isChild ? 'bg-amber-100 text-amber-800' : 'bg-[#f3e4df] text-[#7b5047]' }}">{{ $isChild ? 'Child' : 'Adult' }}</span>
                            @if(!empty($blueprint['is_primary_contact']))
                                <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700">Primary contact</span>
                            @endif
                        </span>
                        <span class="mt-1 block truncate text-xs text-slate-500">{{ $isChild ? 'Child details and guardian e-signature' : 'Personal details and own e-signature' }}</span>
                    </span>
                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 sm:text-xs" data-guest-card-status>
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>Required
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180 group-open:text-[#8f5d54]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                    </svg>
                </summary>

                <div class="border-t border-slate-100 bg-[#fcfbfa] px-4 pb-5 pt-4 sm:px-6">
                    <div class="mb-4 flex items-start gap-2 rounded-lg {{ $isChild ? 'bg-amber-50 text-amber-900' : 'bg-slate-100 text-slate-600' }} px-3 py-2.5 text-xs leading-5">
                        @if($isChild)
                            Enter the child's details. A parent or legal guardian must complete and sign this waiver on the child's behalf.
                        @else
                            Enter this adult guest's own details and signature. All adult guests require a separate signed waiver.
                        @endif
                    </div>

                <input type="hidden" name="guests[{{ $index }}][guest_type]" value="{{ $guestType }}">
                <input type="hidden" name="guests[{{ $index }}][is_primary_contact]" value="{{ !empty($blueprint['is_primary_contact']) ? 1 : 0 }}">
                <input type="hidden" name="guests[{{ $index }}][signature_data]" value="{{ $signatureData }}" data-signature-input>

                <div class="mt-5 space-y-6">
                    <section aria-labelledby="guest_{{ $index }}_personal_heading">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-[#b77b6d]"></span>
                            <h5 id="guest_{{ $index }}_personal_heading" class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Personal details</h5>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="guest_{{ $index }}_full_name" class="mb-2 block text-sm font-medium text-slate-700">Full legal name <span class="text-red-500">*</span></label>
                                <input type="text" id="guest_{{ $index }}_full_name" name="guests[{{ $index }}][full_name]" value="{{ $fullName }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]" placeholder="First name and last name" autocomplete="section-guest-{{ $index }} name" required>
                                @error($fieldPrefix . 'full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="guest_{{ $index }}_contact_number" class="mb-2 block text-sm font-medium text-slate-700">
                                    Mobile number
                                    @unless($isChild)<span class="text-red-500">*</span>@else<span class="text-xs font-normal text-slate-400">(Optional for children)</span>@endunless
                                </label>
                                <input type="tel" id="guest_{{ $index }}_contact_number" name="guests[{{ $index }}][contact_number]" value="{{ $contactNumber }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]" placeholder="+63 9XX XXX XXXX" autocomplete="section-guest-{{ $index }} tel" {{ $isChild ? '' : 'required' }}>
                                @error($fieldPrefix . 'contact_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="guest_{{ $index }}_birth_date" class="mb-2 block text-sm font-medium text-slate-700">Date of birth <span class="text-red-500">*</span></label>
                                <input type="date" id="guest_{{ $index }}_birth_date" name="guests[{{ $index }}][birth_date]" value="{{ $birthDate }}" max="{{ now()->subDay()->toDateString() }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]" required>
                                @error($fieldPrefix . 'birth_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="guest_{{ $index }}_gender" class="mb-2 block text-sm font-medium text-slate-700">Gender <span class="text-red-500">*</span></label>
                                <select id="guest_{{ $index }}_gender" name="guests[{{ $index }}][gender]" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]" required>
                                    <option value="">Select gender</option>
                                    <option value="male" @selected($gender === 'male')>Male</option>
                                    <option value="female" @selected($gender === 'female')>Female</option>
                                    <option value="other" @selected($gender === 'other')>Other</option>
                                    <option value="prefer_not_to_say" @selected($gender === 'prefer_not_to_say')>Prefer not to say</option>
                                </select>
                                @error($fieldPrefix . 'gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-slate-200 bg-white p-4" aria-labelledby="guest_{{ $index }}_emergency_heading">
                        <div class="mb-3">
                            <h5 id="guest_{{ $index }}_emergency_heading" class="text-xs font-bold uppercase tracking-[0.14em] text-slate-700">Emergency contact</h5>
                            <p class="mt-1 text-xs text-slate-500">Someone we can contact in case of an emergency.</p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="guest_{{ $index }}_emergency_contact_name" class="mb-2 block text-sm font-medium text-slate-700">Contact name <span class="text-red-500">*</span></label>
                                <input type="text" id="guest_{{ $index }}_emergency_contact_name" name="guests[{{ $index }}][emergency_contact_name]" value="{{ $emergencyName }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]" placeholder="Emergency contact's full name" autocomplete="section-emergency-{{ $index }} name" required>
                                @error($fieldPrefix . 'emergency_contact_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="guest_{{ $index }}_emergency_contact_number" class="mb-2 block text-sm font-medium text-slate-700">Contact number <span class="text-red-500">*</span></label>
                                <input type="tel" id="guest_{{ $index }}_emergency_contact_number" name="guests[{{ $index }}][emergency_contact_number]" value="{{ $emergencyNumber }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]" placeholder="+63 9XX XXX XXXX" autocomplete="section-emergency-{{ $index }} tel" required>
                                @error($fieldPrefix . 'emergency_contact_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="guest_{{ $index }}_emergency_contact_relationship" class="mb-2 block text-sm font-medium text-slate-700">Relationship to guest <span class="text-red-500">*</span></label>
                                <input type="text" id="guest_{{ $index }}_emergency_contact_relationship" name="guests[{{ $index }}][emergency_contact_relationship]" value="{{ $emergencyRelationship }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]" placeholder="Parent, sibling, spouse, friend, etc." required>
                                @error($fieldPrefix . 'emergency_contact_relationship')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                    @if($isChild)
                        <section class="rounded-xl border border-amber-200 bg-amber-50/60 p-4" aria-labelledby="guest_{{ $index }}_guardian_heading">
                            <div class="mb-3">
                                <h5 id="guest_{{ $index }}_guardian_heading" class="text-xs font-bold uppercase tracking-[0.14em] text-amber-900">Parent or legal guardian</h5>
                                <p class="mt-1 text-xs text-amber-800/70">This person must provide the e-signature below.</p>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="guest_{{ $index }}_guardian_full_name" class="mb-2 block text-sm font-medium text-slate-700">Guardian full name <span class="text-red-500">*</span></label>
                                    <input type="text" id="guest_{{ $index }}_guardian_full_name" name="guests[{{ $index }}][guardian_full_name]" value="{{ $guardianFullName }}" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:ring-amber-500" placeholder="Parent or guardian's full name" required>
                                    @error($fieldPrefix . 'guardian_full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="guest_{{ $index }}_guardian_relationship" class="mb-2 block text-sm font-medium text-slate-700">Relationship to child <span class="text-red-500">*</span></label>
                                    <input type="text" id="guest_{{ $index }}_guardian_relationship" name="guests[{{ $index }}][guardian_relationship]" value="{{ $guardianRelationship }}" class="w-full rounded-lg border border-amber-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:ring-amber-500" placeholder="Mother, father, legal guardian, etc." required>
                                    @error($fieldPrefix . 'guardian_relationship')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </section>
                    @endif
                </div>

                <div class="mt-5 rounded-xl border border-[#ddd3cb] bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $isChild ? 'Guardian e-signature' : 'Guest e-signature' }} <span class="text-red-500">*</span></p>
                            <p class="text-xs text-slate-500">{{ $isChild ? 'The named guardian signs on behalf of this child.' : 'This adult guest must sign their own waiver.' }}</p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50"
                            data-signature-clear
                        >
                            Clear
                        </button>
                    </div>
                    <div class="mt-4 rounded-lg border border-slate-300 bg-[#fffefa] p-2 shadow-inner">
                        <canvas
                            class="h-36 w-full touch-none rounded-md bg-white outline-none focus:ring-2 focus:ring-[#b77b6d] focus:ring-offset-2 sm:h-40"
                            data-signature-canvas
                            tabindex="0"
                            aria-label="{{ $isChild ? 'Guardian signature pad for child guest ' . ($blueprint['type_number'] ?? 1) : 'Signature pad for adult guest ' . ($blueprint['type_number'] ?? 1) }}"
                        ></canvas>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-xs font-medium text-slate-500" data-signature-status>
                        <span class="h-2 w-2 rounded-full bg-slate-300" data-signature-status-dot></span>
                        <span data-signature-status-label>Signature required</span>
                    </div>
                    @error($fieldPrefix . 'signature_data')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                    @unless($loop->last)
                        <div class="mt-5 flex justify-end">
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-[#8f5d54] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#744a43] focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2" data-next-guest>
                                Continue to guest {{ $index + 2 }}
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    @endunless
                </div>
            </details>
        @endforeach
    </div>

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
