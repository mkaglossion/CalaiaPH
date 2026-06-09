@props(['accommodation', 'variantsJson'])

<div class="bg-white rounded-2xl shadow-[0_18px_40px_rgba(15,23,42,0.08)] border border-gray-100 overflow-hidden flex flex-col">

    {{-- Image --}}
    <img
        src="{{ \App\Helpers\ImageHelper::src($accommodation->image_path ?? null) }}"
        alt="{{ $accommodation->name }}"
        class="w-full object-cover"
        onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
        style="height: 280px;">

    <div class="flex flex-col p-6 h-full">
        {{-- Name --}}
        <h3 class="text-2xl font-extrabold text-gray-900 mb-2">
            {{ $accommodation->name }}
        </h3>

        {{-- Description --}}
        <p class="text-xs text-gray-600 leading-relaxed mb-3">
            {{ Str::limit($accommodation->description, 150) ?: '-' }}
        </p>

        {{-- Includes --}}
        <p class="text-xs text-gray-600 leading-relaxed mb-3">
        Includes: Standard and Premium glamping tents (static)
        </p>

        {{-- Variants --}}
        @if($accommodation->accommodationVariants?->count())
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($accommodation->accommodationVariants as $variant)
                    @php
                        $variantName = $variant->text('Name') ?? $variant->staycation->name;
                    @endphp
                    <span class="inline-block bg-gray-200 px-2 py-1 rounded text-xs">
                        {{ $variantName }}
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Price --}}
        @php
            $priceText = $accommodation->accommodationTexts
                ->where('title', 'Price')
                ->first()
                ->content ?? null;
        @endphp

        <p class="font-semibold mb-3" style="color: #7a7d52; font-size: 16px;">
            {{ $priceText ? $priceText : 'Price not available' }}
        </p>

        {{-- Options --}}
        <p class="text-xs text-gray-600 leading-relaxed mb-3">

        {{-- Button --}}
        <button
            type="button"
            class="select-category-btn mt-auto w-full rounded-full text-lg font-semibold text-white px-6 py-3"
            data-accommodation-id="{{ $accommodation->id }}"
            data-accommodation-name="{{ $accommodation->name }}"
            data-variants='{{ $variantsJson }}'
            onclick="openVariantsModal(this)"
            style="background-color:#8f5d54;"
        >
            Select Accommodation
        </button>
    </div>
</div>
