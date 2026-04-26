{{-- Package Item - Dynamic (Alpine.js) --}}
<div>
    <div class="flex items-start justify-between gap-2 mb-3">
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <i class="bi bi-box2 text-blue-600"></i>
                <p class="text-sm font-bold text-blue-900" x-text="food.name"></p>
            </div>
        </div>
        <button type="button" class="text-gray-400 hover:text-red-600 transition" @click="removeFood(food.id)">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Quantity Controls for Package --}}
    <div class="flex items-center justify-between gap-2 mb-3 pb-3 border-b border-blue-200">
        <div class="flex items-center gap-2">
            <button type="button" class="w-8 h-8 rounded-md border border-blue-300 text-blue-700 hover:bg-blue-100 transition" @click="decreaseQty(food.id)">-</button>
            <input type="number" min="1" max="20" class="w-16 text-center border border-blue-300 rounded-md py-1 text-sm" :value="food.quantity" @change="setField(food.id, 'quantity', $event.target.value)">
            <button type="button" class="w-8 h-8 rounded-md border border-blue-300 text-blue-700 hover:bg-blue-100 transition" @click="increaseQty(food.id)">+</button>
        </div>
        <div class="text-right text-[#8f5d54] font-bold text-sm" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
    </div>

    {{-- Included Foods --}}
    <div class="pt-3 text-xs space-y-1">
        <p class="font-semibold text-blue-900 text-xs uppercase tracking-wide">Includes:</p>
        <template x-for="item in food.included_foods" :key="item.id">
            <div class="text-blue-800">
                <span class="font-semibold" x-text="item.quantity + 'x'"></span>
                <span x-text="item.name"></span>
            </div>
        </template>
    </div>
</div>
