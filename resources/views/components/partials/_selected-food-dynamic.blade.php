{{-- Regular Food Item - Dynamic (Alpine.js) --}}
<div class="space-y-3">
    {{-- Food Header --}}
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-bold text-gray-900" x-text="food.name"></p>
            <p class="text-xs text-gray-500 capitalize" :class="food.category === 'special' ? 'text-green-600' : ''" x-text="food.category"></p>
        </div>
        <button type="button" class="text-gray-400 hover:text-red-600 transition shrink-0" @click="removeFood(food.id)">
            <i class="bi bi-x-lg text-lg"></i>
        </button>
    </div>

    {{-- Unit Price Display --}}
    <template x-if="food.id !== 'cook-your-own' && food.category !== 'special'">
        <div class="text-sm font-bold text-gray-600" x-text="'₱' + (food.price || 0).toLocaleString()"></div>
    </template>

    {{-- Display price for paid items --}}
    <template x-if="food.id !== 'cook-your-own' && food.category !== 'special'">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">Quantity: <span class="font-semibold" x-text="food.quantity"></span></div>
            <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
        </div>
    </template>

    {{-- Notes field --}}
    <template x-if="food.notes">
        <div class="mt-2 text-xs text-gray-600">
            <strong>Notes:</strong> <span x-text="food.notes"></span>
        </div>
    </template>

    {{-- Special message for special options --}}
    <template x-if="food.category === 'special' || food.id === 'cook-your-own'">
        <div class="p-3 rounded-lg border bg-blue-50 border-blue-200" :class="food.name === 'Cook Your Own' ? 'bg-green-50 border-green-200' : ''">
            <p class="text-xs font-medium" :class="food.name === 'Cook Your Own' ? 'text-green-700' : 'text-blue-700'"><i class="bi bi-info-circle mr-1"></i><span x-text="food.name === 'Cook Your Own' ? 'You\'ll have access to a fully equipped kitchen. No additional charge.' : 'Special option selected. No additional charge.'"></span></p>
        </div>
    </template>
</div>
