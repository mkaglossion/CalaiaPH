{{-- Static Selected Activities Template (PHP) --}}
@php
    $hasActivities = (!empty($selectedActivities) && count($selectedActivities) > 0) || 
                     (!empty($selectedActivityPackages) && count($selectedActivityPackages) > 0);
@endphp

@if($hasActivities)
<div class="space-y-3">
    @php $calculatedActivityTotal = 0; @endphp
    
    {{-- Display Activity Packages --}}
    @if(!empty($selectedActivityPackages))
        @foreach($selectedActivityPackages as $package)
            @php
                $quantity = $package['quantity'] ?? 1;
                $itemPrice = $package['price'] ?? 0;
                $itemTotal = $itemPrice * $quantity;
                $calculatedActivityTotal += $itemTotal;
                $includedActivities = $package['included_activities'] ?? [];
            @endphp
            @include('components.partials._selected-activity-package-static', ['selectedActivity' => $package, 'quantity' => $quantity, 'itemPrice' => $itemPrice, 'itemTotal' => $itemTotal, 'includedActivities' => $includedActivities])
        @endforeach
    @endif
    
    {{-- Display Individual Activities --}}
    @if(!empty($selectedActivities))
        @foreach($selectedActivities as $selectedActivity)
            @php
                $guests = $selectedActivity['guests'] ?? 1;
                $itemPrice = $selectedActivity['price'] ?? 0;
                $itemTotal = $itemPrice * $guests;
                $calculatedActivityTotal += $itemTotal;
            @endphp
            @include('components.partials._selected-activity-static', ['selectedActivity' => $selectedActivity, 'guests' => $guests, 'itemTotal' => $itemTotal])
        @endforeach
    @endif
</div>
@endif
