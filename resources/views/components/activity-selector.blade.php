@props([
    'title' => 'Select or Add Activities',
    'subtitle' => 'These are optional enhancements to your booking. Choose from our curated experiences or add your own custom activity.',
    'apiUrl' => '/api/activities',
    'containerId' => 'activities-container',
    'selectedContainerId' => 'selected-activities',
    'totalElementId' => 'total-cost',
    'confirmButtonId' => 'confirm-btn',
    'skipButtonId' => 'skip-btn',
    'skipUrl' => null,
    'storeUrl' => null,
    'totalGuests' => 1,
    'showCustomActivity' => false
])

{{-- Activities Selection --}}
<main class="py-6 sm:py-8 md:py-12 lg:py-16 min-h-[70vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 sm:p-6 md:p-8">
            {{-- Title --}}
            <header class="mb-6 sm:mb-8 text-center">
                <p class="text-xs sm:text-sm font-medium tracking-[0.2em] uppercase text-gray-400 mb-2">
                    CalaiaPH Booking
                </p>
                <h1 class="text-2xl font-bold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">
                    {{ $title }}
                </h1>
                <p class="mt-2 text-xs text-gray-500 max-w-2xl mx-auto px-2" style="font-family: 'Gotham Book', sans-serif;">
                    {{ $subtitle }}
                </p>
            </header>

            <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] items-start">
                {{-- Activities List --}}
                <section aria-labelledby="activities-heading">
                    <h2 id="activities-heading" class="sr-only">Available Activities</h2>

                    {{-- Curated Activities --}}
                    <div class="mb-8">
                        <h3 class="text-2xl font-semibold text-gray-800 mb-4" style="font-family: 'Gotham Book', sans-serif;">Curated Activities</h3>
                        <div id="{{ $containerId }}" class="grid gap-4 sm:grid-cols-2">
                            <!-- Activities will be loaded here -->
                        </div>
                    </div>

                    @if($showCustomActivity)
                        {{-- Custom Activity --}}
                        <div class="mb-8">
                            <h3 class="text-2xl font-semibold text-gray-800 mb-4" style="font-family: 'Gotham Book', sans-serif;">Add Custom Activity</h3>
                            <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
                                <form id="custom-activity-form" class="space-y-4">
                                    <div>
                                        <label for="custom-name" class="block text-sm font-medium text-gray-700 mb-1">Activity Name</label>
                                        <input type="text" id="custom-name" name="custom_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-[#8f5d54] focus:border-[#8f5d54]" placeholder="e.g., Private Yoga Session">
                                    </div>
                                    <div>
                                        <label for="custom-description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                                        <textarea id="custom-description" name="custom_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-[#8f5d54] focus:border-[#8f5d54]" placeholder="Brief description of the activity"></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="custom-price" class="block text-sm font-medium text-gray-700 mb-1">Price per Person</label>
                                            <input type="number" id="custom-price" name="custom_price" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-[#8f5d54] focus:border-[#8f5d54]" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label for="custom-guests" class="block text-sm font-medium text-gray-700 mb-1">Number of Guests</label>
                                            <input type="number" id="custom-guests" name="custom_guests" min="1" max="{{ $totalGuests }}" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-[#8f5d54] focus:border-[#8f5d54]">
                                        </div>
                                    </div>
                                    <button type="button" id="add-custom-activity" class="w-full bg-[#8f5d54] text-white py-2 px-4 rounded-md hover:bg-[#a0675a] transition-colors">
                                        Add Custom Activity
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Summary Sidebar --}}
                <x-booking-summary
                    :selected-activities="[]"
                    :activities-total="0"
                    :total-guests="$totalGuests"
                    title="Selected Activities"
                />

                {{-- Action Buttons --}}
                <div class="mt-6 space-y-3">
                    <button
                        type="button"
                        onclick="window.history.back()"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </button>

                    @if($skipUrl)
                        <button
                            type="button"
                            id="{{ $skipButtonId }}"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors"
                        >
                            Proceed to Payment Without Activities
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    @endif

                    <button
                        type="button"
                        id="{{ $confirmButtonId }}"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-4 py-2 text-sm font-semibold text-white hover:bg-[#a0675a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#8f5d54] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled
                    >
                        Confirm and Proceed
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>