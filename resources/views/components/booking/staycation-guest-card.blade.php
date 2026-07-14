@props([
    'blueprint' => [],
    'isFirst' => false,
    'hasNextGuest' => false,
])

@php
    $blueprint = is_array($blueprint ?? null) ? $blueprint : [];
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
    $personalInfoHint = $isChild
        ? "Enter the child's details. A parent or legal guardian must complete and sign this waiver on the child's behalf."
        : "Enter this adult guest's own details and signature. All adult guests require a separate signed waiver.";
    $fieldClass = 'w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-[#8f5d54] focus:ring-[#8f5d54]';
    $childFieldClass = 'w-full rounded-lg border border-amber-200 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:ring-amber-500';
    $sectionAccent = $isChild ? 'bg-amber-400' : 'bg-[#b77b6d]';
    $iconContainerClass = $isChild ? 'bg-amber-50 text-amber-700' : 'bg-[#f7eeea] text-[#8f5d54]';
    $badgeClass = $isChild
        ? 'bg-amber-100 text-amber-800'
        : 'bg-[#f3e4df] text-[#7b5047]';
    $contextClass = $isChild
        ? 'bg-amber-50 text-amber-900'
        : 'bg-slate-100 text-slate-600';
    $signatureTitle = $isChild ? 'Guardian e-signature' : 'Guest e-signature';
    $signatureDescription = $isChild
        ? 'The named guardian signs on behalf of this child.'
        : 'This adult guest must sign their own waiver.';
    $canvasAriaLabel = $isChild
        ? 'Guardian signature pad for child guest ' . ($blueprint['type_number'] ?? 1)
        : 'Signature pad for adult guest ' . ($blueprint['type_number'] ?? 1);
@endphp

<details
    class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white transition open:border-[#b98d81] open:shadow-[0_10px_30px_rgba(81,54,48,0.08)]"
    data-staycation-signature-card
    data-guest-card-index="{{ $index }}"
    @if($isFirst || $errors->has($fieldPrefix . '*')) open @endif
>
    <span class="absolute inset-y-0 left-0 w-1 {{ $sectionAccent }}" aria-hidden="true"></span>
    <summary class="flex cursor-pointer list-none items-center gap-3 py-3.5 pl-5 pr-4 marker:hidden sm:gap-4 sm:py-4 sm:pl-6 [&::-webkit-details-marker]:hidden">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $iconContainerClass }}">
            @if($isChild)
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM4.5 17.25a5.5 5.5 0 0 1 11 0 .75.75 0 0 1-.75.75h-9.5a.75.75 0 0 1-.75-.75Z" /></svg>
            @else
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 8a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM3 18a7 7 0 1 1 14 0H3Z" /></svg>
            @endif
        </span>
        <span class="min-w-0 flex-1">
            <span class="flex flex-wrap items-center gap-2">
                <span class="font-bold text-slate-900">Guest {{ $index + 1 }}</span>
                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">{{ $isChild ? 'Child' : 'Adult' }}</span>
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
        <div class="mb-4 flex items-start gap-2 rounded-lg {{ $contextClass }} px-3 py-2.5 text-xs leading-5">
            {{ $personalInfoHint }}
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
                    <x-booking.field-group
                        :for="'guest_' . $index . '_full_name'"
                        label-text="Full legal name"
                        required
                        :error-key="$fieldPrefix . 'full_name'"
                    >
                        <input type="text" id="guest_{{ $index }}_full_name" name="guests[{{ $index }}][full_name]" value="{{ $fullName }}" class="{{ $fieldClass }}" placeholder="First name and last name" autocomplete="section-guest-{{ $index }} name" required>
                    </x-booking.field-group>

                    <x-booking.field-group
                        :for="'guest_' . $index . '_contact_number'"
                        :error-key="$fieldPrefix . 'contact_number'"
                    >
                        <x-slot:label>
                            Mobile number
                            @unless($isChild)<span class="text-red-500">*</span>@else<span class="text-xs font-normal text-slate-400">(Optional for children)</span>@endunless
                        </x-slot:label>
                        <input type="tel" id="guest_{{ $index }}_contact_number" name="guests[{{ $index }}][contact_number]" value="{{ $contactNumber }}" class="{{ $fieldClass }}" placeholder="+63 9XX XXX XXXX" autocomplete="section-guest-{{ $index }} tel" {{ $isChild ? '' : 'required' }}>
                    </x-booking.field-group>

                    <x-booking.field-group
                        :for="'guest_' . $index . '_birth_date'"
                        label-text="Date of birth"
                        required
                        :error-key="$fieldPrefix . 'birth_date'"
                    >
                        <input type="date" id="guest_{{ $index }}_birth_date" name="guests[{{ $index }}][birth_date]" value="{{ $birthDate }}" max="{{ now()->subDay()->toDateString() }}" class="{{ $fieldClass }}" required>
                    </x-booking.field-group>

                    <x-booking.field-group
                        :for="'guest_' . $index . '_gender'"
                        label-text="Gender"
                        required
                        :error-key="$fieldPrefix . 'gender'"
                    >
                        <select id="guest_{{ $index }}_gender" name="guests[{{ $index }}][gender]" class="{{ $fieldClass }}" required>
                            <option value="">Select gender</option>
                            <option value="male" @selected($gender === 'male')>Male</option>
                            <option value="female" @selected($gender === 'female')>Female</option>
                            <option value="other" @selected($gender === 'other')>Other</option>
                            <option value="prefer_not_to_say" @selected($gender === 'prefer_not_to_say')>Prefer not to say</option>
                        </select>
                    </x-booking.field-group>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-4" aria-labelledby="guest_{{ $index }}_emergency_heading">
                <div class="mb-3">
                    <h5 id="guest_{{ $index }}_emergency_heading" class="text-xs font-bold uppercase tracking-[0.14em] text-slate-700">Emergency contact</h5>
                    <p class="mt-1 text-xs text-slate-500">Someone we can contact in case of an emergency.</p>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-booking.field-group
                        :for="'guest_' . $index . '_emergency_contact_name'"
                        label-text="Contact name"
                        required
                        :error-key="$fieldPrefix . 'emergency_contact_name'"
                    >
                        <input type="text" id="guest_{{ $index }}_emergency_contact_name" name="guests[{{ $index }}][emergency_contact_name]" value="{{ $emergencyName }}" class="{{ $fieldClass }}" placeholder="Emergency contact's full name" autocomplete="section-emergency-{{ $index }} name" required>
                    </x-booking.field-group>

                    <x-booking.field-group
                        :for="'guest_' . $index . '_emergency_contact_number'"
                        label-text="Contact number"
                        required
                        :error-key="$fieldPrefix . 'emergency_contact_number'"
                    >
                        <input type="tel" id="guest_{{ $index }}_emergency_contact_number" name="guests[{{ $index }}][emergency_contact_number]" value="{{ $emergencyNumber }}" class="{{ $fieldClass }}" placeholder="+63 9XX XXX XXXX" autocomplete="section-emergency-{{ $index }} tel" required>
                    </x-booking.field-group>

                    <x-booking.field-group
                        class="md:col-span-2"
                        :for="'guest_' . $index . '_emergency_contact_relationship'"
                        label-text="Relationship to guest"
                        required
                        :error-key="$fieldPrefix . 'emergency_contact_relationship'"
                    >
                        <input type="text" id="guest_{{ $index }}_emergency_contact_relationship" name="guests[{{ $index }}][emergency_contact_relationship]" value="{{ $emergencyRelationship }}" class="{{ $fieldClass }}" placeholder="Parent, sibling, spouse, friend, etc." required>
                    </x-booking.field-group>
                </div>
            </section>

            @if($isChild)
                <section class="rounded-xl border border-amber-200 bg-amber-50/60 p-4" aria-labelledby="guest_{{ $index }}_guardian_heading">
                    <div class="mb-3">
                        <h5 id="guest_{{ $index }}_guardian_heading" class="text-xs font-bold uppercase tracking-[0.14em] text-amber-900">Parent or legal guardian</h5>
                        <p class="mt-1 text-xs text-amber-800/70">This person must provide the e-signature below.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-booking.field-group
                            :for="'guest_' . $index . '_guardian_full_name'"
                            label-text="Guardian full name"
                            required
                            :error-key="$fieldPrefix . 'guardian_full_name'"
                        >
                            <input type="text" id="guest_{{ $index }}_guardian_full_name" name="guests[{{ $index }}][guardian_full_name]" value="{{ $guardianFullName }}" class="{{ $childFieldClass }}" placeholder="Parent or guardian's full name" required>
                        </x-booking.field-group>

                        <x-booking.field-group
                            :for="'guest_' . $index . '_guardian_relationship'"
                            label-text="Relationship to child"
                            required
                            :error-key="$fieldPrefix . 'guardian_relationship'"
                        >
                            <input type="text" id="guest_{{ $index }}_guardian_relationship" name="guests[{{ $index }}][guardian_relationship]" value="{{ $guardianRelationship }}" class="{{ $childFieldClass }}" placeholder="Mother, father, legal guardian, etc." required>
                        </x-booking.field-group>
                    </div>
                </section>
            @endif
        </div>

        <x-booking.signature-pad
            :title="$signatureTitle"
            :description="$signatureDescription"
            :aria-label="$canvasAriaLabel"
            :error-key="$fieldPrefix . 'signature_data'"
        />

        @if($hasNextGuest)
            <div class="mt-5 flex justify-end">
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-[#8f5d54] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#744a43] focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2" data-next-guest>
                    Continue to guest {{ $index + 2 }}
                    <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        @endif
    </div>
</details>
