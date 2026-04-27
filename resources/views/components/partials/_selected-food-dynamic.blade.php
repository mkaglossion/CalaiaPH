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

    {{-- Quantity controls and price for paid items --}}
    <template x-if="food.id !== 'cook-your-own' && food.category !== 'special'">
        <div>
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="decreaseQty(food.id)">−</button>
                    <input type="number" id="quantity" name="quantity" min="1" max="20" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="food.quantity" @change="setField(food.id, 'quantity', $event.target.value)">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="increaseQty(food.id)">+</button>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + (food.price * food.quantity).toLocaleString()"></div>
                </div>
            </div>

            <div class="mt-3">
                <textarea rows="2" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54] placeholder-gray-400" placeholder="Notes or dietary preferences..." :value="food.notes || ''" @input="setField(food.id, 'notes', $event.target.value)"></textarea>
            </div>
        </div>
    </template>

    {{-- Special message for special options --}}
    <template x-if="food.category === 'special' || food.id === 'cook-your-own'">
        <div class="p-3 rounded-lg border bg-blue-50 border-blue-200" :class="food.name === 'Cook Your Own' ? 'bg-green-50 border-green-200' : ''">
            <p class="text-xs font-medium" :class="food.name === 'Cook Your Own' ? 'text-green-700' : 'text-blue-700'"><i class="bi bi-info-circle mr-1"></i><span x-text="food.name === 'Cook Your Own' ? 'You\'ll have access to a fully equipped kitchen. No additional charge.' : 'Special option selected. No additional charge.'"></span></p>
        </div>
    </template>
</div>
