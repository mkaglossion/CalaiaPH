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

    {{-- Quantity Controls and Price (with count) --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="decreaseQty(food.id)">−</button>
            <input type="number" min="1" max="20" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="food.quantity" @change="setField(food.id, 'quantity', $event.target.value)">
            <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="increaseQty(food.id)">+</button>
        </div>
        <div class="text-right">
            <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
        </div>
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
