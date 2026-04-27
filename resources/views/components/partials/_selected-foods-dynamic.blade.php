{{-- Dynamic Selected Foods Template (Alpine.js) --}}
<template x-if="selectedFoods.length === 0">
    <div class="bg-gray-50 rounded-lg p-4 text-center">
        <p class="text-gray-500 text-sm">No food selected yet</p>
    </div>
</template>

<template x-if="selectedFoods.length > 0">
    <div class="space-y-3">
        <template x-for="food in selectedFoods" :key="food.id">
        <div class="rounded-xl p-4 border" :class="food.is_package ? 'border-gray-200 bg-white' : 'border-gray-200 bg-white'">
            {{-- Package Item --}}
            <template x-if="food.is_package">
                @include('components.partials._selected-package-dynamic')
            </template>

            {{-- Regular Food Item --}}
            <template x-if="!food.is_package">
                @include('components.partials._selected-food-dynamic')
            </template>
        </div>
        </template>

    </div>
</template>
