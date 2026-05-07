{{-- Package Item - Dynamic (Alpine.js) --}}
<div class="space-y-3">
    {{-- Package Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 flex-1">
            <i class="bi bi-briefcase text-gray-600 text-lg"></i>
            <p class="text-sm font-bold text-gray-900" x-text="food.name"></p>
        </div>
        <button type="button" class="text-gray-400 hover:text-red-600 transition shrink-0" @click="removeFood(food.id)">
            <i class="bi bi-x-lg text-lg"></i>
        </button>
    </div>

    {{-- Display Quantity and Price --}}
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">Quantity: <span class="font-semibold" x-text="food.quantity"></span></div>
        <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
    </div>

    {{-- Included Foods --}}
    <div class="pt-2 border-t border-gray-200">
        <p class="font-semibold text-xs uppercase tracking-widest text-gray-600 mb-2">Includes:</p>
        <div class="space-y-1 text-sm">
            <template x-for="item in food.included_foods" :key="item.id">
                <div class="text-gray-700">
                    <span class="font-semibold text-gray-600" x-text="item.quantity + 'x'"></span>
                    <span x-text="item.name"></span>
                </div>
            </template>
        </div>
    </div>
</div>
