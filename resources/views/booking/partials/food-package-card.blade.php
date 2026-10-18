@php
    $category = strtolower($package->category ?? 'messhall');

    $packageFoods = $package->foods->map(function ($food) {
        return [
            'id' => $food->id,
            'name' => $food->name,
            'price' => $food->price,
            'quantity' => $food->pivot->quantity,
            'category' => $food->category,
        ];
    })->values();

    $packageFoodLookup = $package->foods->keyBy('id');

    $packageSections = collect($package->sections ?? [])->map(function ($section) use ($packageFoodLookup) {
        return [
            'title' => $section['title'] ?? '',
            'mode' => $section['mode'] ?? 'included',
            'note' => $section['note'] ?? '',
            'choice_limit' => $section['choice_limit'] ?? 1,
            'items' => collect($section['items'] ?? [])->map(function ($item) use ($packageFoodLookup) {
                $food = $packageFoodLookup->get($item['food_id'] ?? null);

                return [
                    'id' => $food?->id,
                    'name' => $food?->name ?? 'Unknown item',
                    'price' => $food?->price ?? 0,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                ];
            })->filter(fn ($item) => !empty($item['id']))->values(),
        ];
    })->filter(fn ($section) => filled($section['title']) && $section['items']->isNotEmpty())->values();

    $packageDisplaySections = $packageSections->isNotEmpty()
        ? $packageSections
        : collect([
            [
                'title' => 'Includes',
                'mode' => 'included',
                'items' => $packageFoods,
            ],
        ]);
@endphp

<div class="relative h-full w-full min-w-0 flex flex-col bg-white rounded-2xl shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#8f5d54] hover:shadow-xl transition cursor-pointer"
     @click="togglePackage({{ $package->id }}, @js($package->name), {{ $package->getEffectivePrice() }}, @js($packageFoods), @js($packageSections))">

    @include('booking.partials.card-selection-actions', [
        'itemType' => 'food_package',
        'itemId' => $package->id,
        'selectionExpression' => "selectedFoods.find(f => f.is_package && String(f.package_id) === String({$package->id})) || { quantity: 1 }",
        'selectedCondition' => 'isPackageSelected(' . $package->id . ')',
    ])

    <div class="relative bg-gray-200 overflow-hidden h-64 sm:h-64 md:h-80 lg:h-96">
        @if(!empty($package->image))
            <img src="{{ \App\Helpers\ImageHelper::src($package->image) }}" alt="{{ $package->name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
        @else
            <div class="w-full h-full bg-linear-to-br from-slate-200 via-slate-300 to-slate-400 flex items-center justify-center">
                <i class="bi bi-image text-6xl text-white/50"></i>
            </div>
        @endif

        @if($package->is_inventory_managed && $package->stock_quantity <= 0)
            <div class="absolute bottom-4 right-4 z-10">
                <span class="availability-badge px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded-full">
                    Out of Stock
                </span>
            </div>
        @elseif($package->is_inventory_managed && $package->stock_quantity <= 5)
            <div class="absolute bottom-4 right-4 z-10">
                <span class="availability-badge px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded-full animate-pulse">
                    Only {{ $package->stock_quantity }} left
                </span>
            </div>
        @endif

    </div>

    <div class="p-4 sm:p-4 md:p-6 flex flex-col flex-1">
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

        @include('booking.partials.expandable-description', [
            'description' => $package->description,
            'wrapperClass' => 'mb-4 md:mb-5',
            'textClass' => 'text-gray-600',
            'buttonClass' => 'text-[#8f5d54]',
            'clampLines' => 3,
        ])

        {{-- Quantity controls for selected packages --}}
        <template x-if="isPackageSelected({{ $package->id }})">
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="decreasePackageQty({{ $package->id }})">−</button>
                        <input type="number" min="1" max="{{ $package->is_inventory_managed ? min(20, max(1, $package->stock_quantity)) : 20 }}" :id="'package-qty-card-' + {{ $package->id }}" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="getPackageQuantity({{ $package->id }})" @change.stop="setPackageField({{ $package->id }}, 'quantity', $event.target.value)">
                        <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click.stop="increasePackageQty({{ $package->id }})">+</button>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + ({{ $package->getEffectivePrice() }} * getPackageQuantity({{ $package->id }})).toLocaleString()"></div>
                    </div>
                </div>
            </div>
        </template>

        @if($packageDisplaySections->isNotEmpty())
            <div class="mb-5">
                <h4 class="text-xs font-semibold uppercase tracking-[0.15em] text-gray-400 mb-2">Includes</h4>
                <div class="space-y-3">
                    @foreach($packageDisplaySections as $section)
                        <div>
                            @if($packageSections->isNotEmpty())
                                <div class="mb-1 flex items-center gap-2">
                                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">
                                        {{ $section['title'] }}
                                    </span>
                                    @if(($section['mode'] ?? 'included') === 'choice')
                                        <span class="inline-flex items-center rounded-full bg-[#8f5d54]/10 px-2 py-0.5 text-[10px] font-semibold text-[#8f5d54]">
                                            Choose one
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="text-xs font-medium text-[#8f5d54] leading-5">
                                @foreach($section['items'] as $item)
                                    @php
                                        $itemLabel = is_array($item)
                                            ? (($item['quantity'] ?? 1) . 'x ' . ($item['name'] ?? ''))
                                            : ((isset($item['quantity']) ? $item['quantity'] : 1) . 'x ' . ($item['name'] ?? ''));
                                    @endphp
                                    <span>{{ $itemLabel }}@if(! $loop->last), @endif</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <button
            type="button"
            class="w-full mt-auto py-2.5 md:py-3 rounded-xl text-xs sm:text-sm md:text-base font-semibold transition flex items-center justify-center gap-2"
:class="{
    'bg-[#8f5d54] text-white hover:bg-[#7a4e48]': isPackageSelected({{ $package->id }}),
    'bg-gray-300 text-gray-500 cursor-not-allowed': !isPackageSelected({{ $package->id }}) && {{ $package->is_inventory_managed ? 'true' : 'false' }} && {{ $package->stock_quantity }} <= 0,
    'bg-gray-100 text-gray-700 hover:bg-gray-200': !isPackageSelected({{ $package->id }}) && (!{{ $package->is_inventory_managed ? 'true' : 'false' }} || {{ $package->stock_quantity }} > 0)
}"
            :disabled="{{ $package->is_inventory_managed && $package->stock_quantity <= 0 ? 'true' : 'false' }}"
            @click.stop="togglePackage({{ $package->id }}, @js($package->name), {{ $package->getEffectivePrice() }}, @js($packageFoods), @js($packageSections))"
        >
            <i :class="isPackageSelected({{ $package->id }}) ? 'bi bi-check' : 'bi bi-plus'"></i>
            <span x-text="isPackageSelected({{ $package->id }}) ? 'Added to Cart' : '{{ $package->is_inventory_managed && $package->stock_quantity <= 0 ? 'Out of Stock' : 'Add to Cart' }}'"></span>
        </button>
    </div>
</div>
