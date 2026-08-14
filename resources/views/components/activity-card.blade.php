@props(['activity', 'categoryLabels' => []])

@php
    $activityCategoryKey = strtolower(trim((string) ($activity->category ?? 'activity')));
    $activityCategoryLabel = $categoryLabels[$activityCategoryKey] ?? ucfirst(str_replace('_', ' ', $activityCategoryKey ?: 'activity'));
@endphp

<div class="relative h-full flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-xl ring-2 ring-gray-200 hover:ring-[#8f5d54] cursor-pointer"
     @click="toggleActivity({{ $activity->id }}, '{{ addslashes($activity->name) }}', '{{ $activity->category ?? 'activity' }}', {{ $activity->price }})">

    @include('booking.partials.card-selection-actions', [
        'itemType' => 'activity',
        'itemId' => $activity->id,
        'selectionExpression' => "selectedActivities.find(a => String(a.id) === String({$activity->id})) || { guests: 1 }",
        'selectedCondition' => 'isActivitySelected(' . $activity->id . ')',
    ])

    {{-- Image --}}
    <div class="relative bg-gray-200 overflow-hidden h-44 sm:h-56 md:h-96">
        @if($activity->image)
            <img src="{{ filter_var($activity->image, FILTER_VALIDATE_URL) ? $activity->image : asset('storage/' . $activity->image) }}" alt="{{ $activity->name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-44 sm:h-56 md:h-96 bg-linear-to-br from-gray-300 to-gray-400 flex items-center justify-center">
                <i class="bi bi-image text-6xl text-gray-300"></i>
            </div>
        @endif

        <div class="absolute bottom-4 left-4 z-10">
            <span class="px-3 py-1 bg-[#8f5d54] text-white text-sm font-semibold rounded-full">
                {{ $activityCategoryLabel }}
            </span>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-3 sm:p-4 md:p-6 flex flex-col flex-1">
        <h3 class="text-sm sm:text-base md:text-xl font-bold mb-1.5 md:mb-2 leading-tight mobile-card-title">{{ $activity->name }}</h3>

        <div class="mb-3">
            <span class="text-sm sm:text-base md:text-lg font-bold text-[#8f5d54]">
                ₱{{ number_format($activity->price, 0, ',', ',') }}
            </span>
        </div>

        @if($activity->description)
            <p class="text-gray-600 mb-3 md:mb-4 text-xs sm:text-sm mobile-card-description">
                {{ $activity->description }}
            </p>
        @endif

        @if($activity->duration)
            <p class="text-[11px] sm:text-xs text-gray-500 mb-3 md:mb-4 flex items-center gap-1">
                <i class="bi bi-clock"></i>
                <span>{{ $activity->duration }} minutes</span>
            </p>
        @endif

        {{-- Quantity controls for selected activities --}}
        <template x-if="isActivitySelected({{ $activity->id }})">
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="decreaseActivityGuests({{ $activity->id }})">−</button>
                        <input type="number" min="1" max="20" :id="'activity-guests-card-' + {{ $activity->id }}" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="getActivityGuests({{ $activity->id }})" @change.stop="setActivityField({{ $activity->id }}, 'guests', $event.target.value)">
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="increaseActivityGuests({{ $activity->id }})">+</button>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + ({{ $activity->price }} * getActivityGuests({{ $activity->id }})).toLocaleString()"></div>
                    </div>
                </div>
            </div>
        </template>

        <button type="button"
                class="w-full mt-auto py-2 md:py-2.5 rounded-lg text-xs sm:text-sm md:text-base font-semibold transition"
                :class="isActivitySelected({{ $activity->id }}) ? 'bg-[#8f5d54] text-white hover:bg-[#7a4e48]' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                @click.stop="toggleActivity({{ $activity->id }}, '{{ addslashes($activity->name) }}', '{{ $activity->category ?? 'activity' }}', {{ $activity->price }})">
            <i :class="isActivitySelected({{ $activity->id }}) ? 'bi bi-check' : 'bi bi-plus'" class="mr-2"></i>
            <span x-text="isActivitySelected({{ $activity->id }}) ? 'Added to Cart' : 'Add to Cart'"></span>
        </button>
    </div>
</div>
