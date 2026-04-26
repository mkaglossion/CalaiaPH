@php
    $category = strtolower($package->category ?? 'messhall');

    $categoryLabel = match ($category) {
        'messhall' => 'Messhall',
        'cook_your_own' => 'Cook Your Own',
        'special' => 'Special',
        default => ucfirst(str_replace('_', ' ', $category)),
    };

    $packageFoods = $package->foods->map(function ($food) {
        return [
            'id' => $food->id,
            'name' => $food->name,
            'price' => $food->price,
            'quantity' => $food->pivot->quantity,
            'category' => $food->category,
        ];
    })->values();
@endphp

<div class="relative bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#8f5d54] hover:shadow-xl transition cursor-pointer"
     @click="addPackageToCart({{ $package->id }}, @js($package->name), {{ $package->getEffectivePrice() }}, @js($packageFoods))">
    
    {{-- Checkmark Badge --}}
    @include('booking.partials._checkmark-badge', ['condition' => '[' . implode(',', $packageFoods->pluck('id')->toArray()) . '].some(foodId => isSelected(foodId))'])
    <div class="relative bg-gray-200 overflow-hidden h-80 md:h-96">
        @if(!empty($package->image))
            <img src="{{ $package->image }}" alt="{{ $package->name }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full bg-linear-to-br from-slate-200 via-slate-300 to-slate-400 flex items-center justify-center">
                <i class="bi bi-image text-6xl text-white/50"></i>
            </div>
        @endif

        <div class="absolute bottom-4 left-4 z-10">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold text-white shadow"
                  style="background: rgba(143, 93, 84, 0.95);">
                {{ $categoryLabel }}
            </span>
        </div>
    </div>

    <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $package->name }}</h3>

        <div class="mb-3">
            <span class="text-base font-bold text-[#8f5d54]">
                ₱{{ number_format($package->getEffectivePrice(), 0) }}
            </span>

            @if($package->price && $package->calculateItemsTotal() > $package->price)
                <span class="ml-2 text-xs text-gray-400 line-through">
                    ₱{{ number_format($package->calculateItemsTotal(), 0) }}
                </span>
            @endif
        </div>

        @if($package->description)
            <p class="text-gray-600 mb-5 leading-relaxed text-sm">{{ $package->description }}</p>
        @endif

        @if($packageFoods->isNotEmpty())
            <div class="mb-5">
                <h4 class="text-xs font-semibold uppercase tracking-[0.15em] text-gray-400 mb-2">Includes</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($packageFoods as $food)
                        <span class="inline-flex items-center gap-1 rounded-full border border-[#8f5d54]/20 bg-[#8f5d54]/10 px-3 py-1 text-xs font-medium text-[#8f5d54]">
                            <span class="font-semibold">{{ $food['quantity'] }}x</span>
                            <span>{{ $food['name'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <button
            type="button"
            class="w-full py-3 rounded-xl font-semibold transition bg-gray-100 text-gray-700 hover:bg-gray-200 flex items-center justify-center gap-2"
            @click.stop="addPackageToCart({{ $package->id }}, @js($package->name), {{ $package->getEffectivePrice() }}, @js($packageFoods))"
        >
            <i class="bi bi-plus"></i>
            Add to Cart
        </button>
    </div>
</div>
