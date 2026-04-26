{{-- Static Selected Foods Template (PHP) --}}
@if(!empty($selectedFoods) && count($selectedFoods) > 0)
<div class="space-y-3">
    @php $calculatedFoodTotal = 0; @endphp
    @foreach($selectedFoods as $selectedFood)
        @php
            // Check if this is a package
            $isPackage = $selectedFood['is_package'] ?? false;
            $includedFoods = $selectedFood['included_foods'] ?? [];
            $quantity = $selectedFood['quantity'] ?? 1;
            $itemPrice = $selectedFood['price'] ?? 0;
            $itemTotal = $itemPrice * $quantity;
            $calculatedFoodTotal += $itemTotal;
        @endphp

        @if($isPackage)
            @include('components.partials._selected-package-static', ['selectedFood' => $selectedFood, 'quantity' => $quantity, 'itemPrice' => $itemPrice, 'itemTotal' => $itemTotal, 'includedFoods' => $includedFoods])
        @else
            @include('components.partials._selected-food-static', ['selectedFood' => $selectedFood, 'quantity' => $quantity, 'itemTotal' => $itemTotal])
        @endif
    @endforeach
</div>
@endif
