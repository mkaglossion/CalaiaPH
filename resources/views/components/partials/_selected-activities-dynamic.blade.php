{{-- Dynamic Selected Activities Template (Alpine.js) --}}
<template x-if="selectedActivities.length === 0 && selectedActivityPackages.length === 0">
    <div class="bg-gray-50 rounded-lg p-4 text-center">
        <p class="text-gray-500 text-sm">No activities selected yet</p>
    </div>
</template>

<template x-if="selectedActivities.length > 0 || selectedActivityPackages.length > 0">
    <div class="space-y-3">
        {{-- Selected Activity Packages (Blue Background) --}}
        <template x-for="pkg in selectedActivityPackages" :key="pkg.id">
        <div class="rounded-xl p-4 border border-gray-200 bg-white space-y-3">
            {{-- Package Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 flex-1">
                    <i class="bi bi-briefcase text-gray-600 text-lg"></i>
                    <p class="text-sm font-bold text-gray-900" x-text="pkg.name"></p>
                </div>
                <button type="button" 
                        @click.stop="toggleActivityPackage(pkg.package_id, pkg.name, pkg.price, pkg.included_activities)"
                        class="text-gray-400 hover:text-red-600 transition shrink-0">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            {{-- Quantity Controls and Price --}}
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="decreaseActivityPackageQty(pkg.package_id)">−</button>
                    <input type="number" min="1" max="20" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="pkg.quantity" @change="setActivityPackageField(pkg.package_id, 'quantity', $event.target.value)">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="increaseActivityPackageQty(pkg.package_id)">+</button>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + (pkg.price * pkg.quantity).toLocaleString()"></div>
                </div>
            </div>

            {{-- Included Activities --}}
            <div class="pt-2 border-t border-gray-200">
                <p class="font-semibold text-xs uppercase tracking-widest text-gray-600 mb-2">Includes:</p>
                <div class="space-y-1 text-sm">
                    <template x-for="item in pkg.included_activities" :key="item.id">
                        <div class="text-gray-700">
                            <span class="font-semibold text-gray-600" x-text="(item.quantity || 1) + 'x'"></span>
                            <span x-text="' ' + item.name"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        </template>

        {{-- Selected Individual Activities (White Background) --}}
        <template x-for="activity in selectedActivities" :key="activity.id">
        <div class="rounded-xl p-4 border border-gray-200 bg-white space-y-3">
            {{-- Activity Header --}}
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900" x-text="activity.name"></p>
                    <p class="text-xs text-gray-500 capitalize" x-text="activity.category || 'Activity'"></p>
                </div>
                <button type="button" 
                        @click.stop="toggleActivity(activity.id, activity.name, activity.category, activity.price)"
                        class="text-gray-400 hover:text-red-600 transition shrink-0">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            {{-- Unit Price --}}
            <div class="text-sm font-bold text-gray-600" x-text="'₱' + (activity.price || 0).toLocaleString()"></div>

            {{-- Guest Count Controls and Total Price --}}
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="decreaseActivityGuests(activity.id)">−</button>
                    <input type="number" min="1" max="20" class="w-12 text-center border border-gray-300 rounded-lg py-1 text-sm font-semibold focus:ring-1 focus:ring-[#8f5d54] focus:border-[#8f5d54]" :value="activity.guests" @change="setActivityField(activity.id, 'guests', $event.target.value)">
                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition font-semibold" @click="increaseActivityGuests(activity.id)">+</button>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-[#8f5d54]" x-text="'₱' + ((activity.price || 0) * (activity.guests || 1)).toLocaleString()"></div>
                </div>
            </div>
        </div>
        </template>
    </div>
</template>
