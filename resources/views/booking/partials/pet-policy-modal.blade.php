@php
    $petPolicyFee = (float) \App\Models\BookingSetting::getValue('pet_price', 2000);
@endphp

<div
    data-pet-policy-modal
    x-data="{ petPolicyOpen: false }"
    x-show="petPolicyOpen"
    x-cloak
    @open-pet-policy.window="petPolicyOpen = true"
    @keydown.escape.window="petPolicyOpen = false"
    @click.self="petPolicyOpen = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="pet-policy-title"
>
    <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
        <div class="mb-4 flex items-center justify-between gap-4">
            <h3 id="pet-policy-title" class="text-lg font-bold text-gray-950">Pet Policy</h3>
            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-800" @click="petPolicyOpen = false" aria-label="Close Pet Policy">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="space-y-3 text-sm text-gray-700">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="font-semibold">Owner Responsibility</div>
                <div>Pet owners are responsible at all times. Pets must not be left unattended.</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="font-semibold">Leash Policy</div>
                <div>Pets may be off-leash only under close and careful supervision.</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="font-semibold">Overnight Stays &amp; Pet Fee</div>
                <div>&#8369;{{ number_format($petPolicyFee, 0) }} per pet fee applies when staying inside. Any damages charged accordingly.</div>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <div class="font-semibold text-red-900">Damage &amp; Liability</div>
                <div class="text-red-800">Owners will be held liable for any damage/injury caused by pets.</div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="button" class="rounded-lg bg-gray-100 px-4 py-2 font-semibold transition hover:bg-gray-200" @click="petPolicyOpen = false">Close</button>
        </div>
    </div>
</div>
