{{-- Selected Food Section --}}
@if($selectedFoods?->isNotEmpty() && $foods?->isNotEmpty())
    <section class="space-y-4">
        {{-- Header --}}
        <h4 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="bi bi-cup-straw text-[#8f5d54]"></i>
            Selected Food & Beverages
        </h4>
        
        {{-- Food Items List --}}
        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
            @foreach($selectedFoods as $selectedFood)
                @php
                    $food = $foods->firstWhere('id', $selectedFood['id']);
                    if (!$food) continue;
                    
                    $quantity = (int) ($selectedFood['quantity'] ?? 1);
                    $itemTotal = $food->price * $quantity;
                @endphp

                <x-food-item-card
                    :food="$food"
                    :quantity="$quantity"
                    :itemTotal="$itemTotal"
                    :notes="$selectedFood['notes'] ?? null"
                />
            @endforeach
        </div>

        {{-- Divider --}}
        <hr class="border-gray-200">
    </section>
@endif
