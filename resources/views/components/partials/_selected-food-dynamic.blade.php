{{-- Regular Food Item - Dynamic (Alpine.js) --}}
<div>
    <div class="flex items-start justify-between gap-2 mb-3">
        <div class="flex-1">
            <p class="text-sm font-semibold" x-text="food.name"></p>
            <p class="text-xs text-gray-500 capitalize" :class="food.category === 'special' ? 'text-green-600' : ''" x-text="food.category"></p>
            <p class="text-sm font-bold mt-1" :class="food.price === 0 ? 'text-green-600' : 'text-[#8f5d54]'" x-text="food.price === 0 ? 'FREE' : '₱' + Number(food.price).toLocaleString()"></p>
        </div>
        <button type="button" class="text-gray-400 hover:text-red-600 transition" @click="removeFood(food.id)">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    {{-- Quantity controls only for paid items --}}
    <template x-if="food.id !== 'cook-your-own' && food.category !== 'special'">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" @click="decreaseQty(food.id)">-</button>
                <input type="number" id="quantity" name="quantity" min="1" max="20" class="w-16 text-center border border-gray-300 rounded-md py-1 text-sm" :value="food.quantity" @change="setField(food.id, 'quantity', $event.target.value)">
                <button type="button" class="w-8 h-8 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100 transition" @click="increaseQty(food.id)">+</button>
            </div>
            <div class="text-right text-[#8f5d54] font-bold text-sm" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
        </div>

        <div class="mt-3">
            <textarea rows="2" class="w-full border border-gray-100 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54] placeholder-gray-400" placeholder="Notes or dietary preferences..." :value="food.notes || ''" @input="setField(food.id, 'notes', $event.target.value)"></textarea>
        </div>
    </template>

    {{-- Special message for special options --}}
    <template x-if="food.category === 'special' || food.id === 'cook-your-own'">
        <div class="mt-3 p-3 rounded-lg border" :class="food.name === 'Cook Your Own' ? 'border-green-300' : 'border-blue-300'">
            <p class="text-xs" :class="food.name === 'Cook Your Own' ? 'text-green-700' : 'text-blue-700'"><i class="bi bi-info-circle mr-1"></i><span x-text="food.name === 'Cook Your Own' ? 'You\'ll have access to a fully equipped kitchen. No additional charge.' : 'Special option selected. No additional charge.'"></span></p>
        </div>
    </template>
</div>
