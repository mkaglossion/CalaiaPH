{{-- Reusable local-delivery address component (Calaca, Batangas only) --}}
<div id="address-field" class="hidden space-y-3">
    <input type="hidden" id="address" name="address" value="{{ old('address') }}">

    <input type="hidden" name="region_id" value="{{ $fixedRegionId ?? '' }}">
    <input type="hidden" name="province_id" value="{{ $fixedProvinceId ?? '' }}">
    <input type="hidden" name="city_id" value="{{ $fixedCityId ?? '' }}">

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
        <p class="text-xs text-gray-500 mb-1">Delivering within</p>
        <p class="text-sm font-semibold text-gray-800">{{ $fixedLocationLabel ?? 'Calaca, Batangas, CALABARZON, Philippines' }}</p>
    </div>

    <div>
        <label for="barangay_id" class="block text-xs font-medium text-gray-600 mb-1">Barangay <span class="text-red-500">*</span></label>
        <select id="barangay_id" name="barangay_id" class="address-select form-select w-full">
            <option value="">Select Barangay</option>
            @foreach(($barangays ?? []) as $barangay)
                <option value="{{ $barangay->id }}" {{ (string) old('barangay_id') === (string) $barangay->id ? 'selected' : '' }}>
                    {{ $barangay->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label for="street_address" class="block text-xs font-medium text-gray-600 mb-1">House/Block/Lot No. + Street <span class="text-red-500">*</span></label>
            <input type="text" id="street_address" name="street_address" value="{{ old('street_address') }}" autocomplete="address-line1"
                   placeholder="e.g. Blk 12 Lot 8, Sampaguita St."
                   class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
        </div>
        <div>
            <label for="subdivision" class="block text-xs font-medium text-gray-600 mb-1">Subdivision / Building / Unit (Optional)</label>
            <input type="text" id="subdivision" name="subdivision" value="{{ old('subdivision') }}" autocomplete="address-line2"
                   placeholder="e.g. Greenfields Subd., Unit 4B"
                   class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
        </div>
        <div>
            <label for="zip_code" class="block text-xs font-medium text-gray-600 mb-1">ZIP Code <span class="text-red-500">*</span></label>
            <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code') }}" inputmode="numeric" maxlength="10" autocomplete="postal-code"
                   placeholder="e.g. 1800"
                   class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
        </div>
        <div>
            <label for="landmark" class="block text-xs font-medium text-gray-600 mb-1">Landmark (Optional)</label>
            <input type="text" id="landmark" name="landmark" value="{{ old('landmark') }}"
                   placeholder="e.g. Near Barangay Hall"
                   class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none">
        </div>
    </div>

    <p class="text-xs text-gray-500">
        Use complete and accurate address details to avoid delivery delays.
    </p>
</div>
