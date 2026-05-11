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

<div class="relative h-full flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#8f5d54] hover:shadow-xl transition cursor-pointer"
     @click="togglePackage({{ $package->id }}, @js($package->name), {{ $package->getEffectivePrice() }}, @js($packageFoods))">
    
    {{-- Checkmark Badge --}}
    @include('booking.partials._checkmark-badge', ['condition' => 'isPackageSelected(' . $package->id . ')'])
    <div class="relative bg-gray-200 overflow-hidden h-44 sm:h-56 md:h-80 lg:h-96">
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

    <div class="p-3 sm:p-4 md:p-6 flex flex-col flex-1">
        <h3 class="text-sm sm:text-base md:text-lg font-bold text-gray-900 mb-1.5 md:mb-2 leading-tight mobile-card-title">{{ $package->name }}</h3>

        <div class="mb-3">
            <span class="text-sm sm:text-base font-bold text-[#8f5d54]">
                ₱{{ number_format($package->getEffectivePrice(), 0) }}
            </span>

            @if($package->price && $package->calculateItemsTotal() > $package->price)
                <span class="ml-2 text-xs text-gray-400 line-through">
                    ₱{{ number_format($package->calculateItemsTotal(), 0) }}
                </span>
            @endif
        </div>

        @if($package->description)
            <p class="text-gray-600 mb-4 md:mb-5 leading-relaxed text-xs sm:text-sm mobile-card-description">{{ $package->description }}</p>
        @endif

        {{-- Quantity controls for selected packages --}}
        <template x-if="isPackageSelected({{ $package->id }})">
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="decreasePackageQty({{ $package->id }})">−</button>
                        <input type="number" min="1" max="20" :id="'package-qty-card-' + {{ $package->id }}" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="getPackageQuantity({{ $package->id }})" @change.stop="setPackageField({{ $package->id }}, 'quantity', $event.target.value)">
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="increasePackageQty({{ $package->id }})">+</button>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + ({{ $package->getEffectivePrice() }} * getPackageQuantity({{ $package->id }})).toLocaleString()"></div>
                    </div>
                </div>
            </div>
        </template>

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
            class="w-full mt-auto py-2.5 md:py-3 rounded-xl text-xs sm:text-sm md:text-base font-semibold transition flex items-center justify-center gap-2"
            :class="isPackageSelected({{ $package->id }}) ? 'bg-[#8f5d54] text-white hover:bg-[#7a4e48]' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            @click.stop="togglePackage({{ $package->id }}, @js($package->name), {{ $package->getEffectivePrice() }}, @js($packageFoods))"
        >
            <i :class="isPackageSelected({{ $package->id }}) ? 'bi bi-check' : 'bi bi-plus'"></i>
            <span x-text="isPackageSelected({{ $package->id }}) ? 'Added to Cart' : 'Add to Cart'"></span>
        </button>
    </div>
</div>
