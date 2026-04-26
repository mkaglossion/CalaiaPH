{{-- Dynamic Selected Foods Template (Alpine.js) --}}
<template x-if="selectedFoods.length === 0">
    <div class="bg-gray-50 rounded-lg p-3 space-y-2 text-xs">
        <p class="text-gray-500">No food selected yet</p>
    </div>
</template>

<template x-if="selectedFoods.length > 0">
    <div class="space-y-4">
        <template x-for="food in selectedFoods" :key="food.id">
        <div class="border rounded-xl p-4 shadow-sm" :class="food.is_package ? 'border-blue-200 bg-blue-50' : (food.id === 'cook-your-own' ? 'border-green-200 bg-green-50' : 'border-gray-100')">
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
