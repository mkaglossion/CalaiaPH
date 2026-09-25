{{-- Package Management Section embedded in What to Do CMS --}}
<div>
    <style>
        [data-package-card].sortable-ghost {
            opacity: 0.45;
        }

        [data-package-card].sortable-chosen {
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.14);
        }

        [data-package-card].sortable-drag {
            cursor: grabbing;
        }
    </style>

<div x-data="packageManager()" x-init="init()" class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-1">Manage Packages</h2>
    <p class="text-gray-600 mb-6">Manage all vacation packages, bookings, and availability CalaiaPH offers</p>

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-3 mb-6">
        <div>
            <h3 class="text-xl font-bold text-gray-800">All Packages</h3>
            <p class="text-gray-500 text-sm mt-1">Create, edit, and manage packages</p>
        </div>
        <button
            @click="showPackageModal = true; resetForm()"
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#8f5d54] text-white font-medium shadow hover:bg-[#7a4d46] transition flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i> New Package
        </button>
    </div>

    @if(($packages ?? collect())->count() > 0)
        <div x-ref="packageGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($packages as $package)
                @php
                    $packageInclusions = is_array($package->inclusions ?? null)
                        ? array_values(array_filter($package->inclusions, fn ($item) => is_string($item) && trim($item) !== ''))
                        : [];
                    $durationLabel = $package->duration_label
                        ?? (($package->duration_days ?? 0) > 0
                            ? ($package->duration_days . ' Day' . (($package->duration_days ?? 0) > 1 ? 's' : ''))
                            : null);
                @endphp
                <div
                    data-package-card
                    data-package-id="{{ $package->id }}"
                    class="relative bg-white/70 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col h-full cursor-grab active:cursor-grabbing"
                >
                    <div class="absolute right-3 top-3 z-20 flex items-center gap-2">
                        <span data-package-order class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-white/95 px-2 text-xs font-semibold text-gray-700 shadow-sm border border-gray-200">
                            #{{ $package->sort_order ?? $loop->iteration }}
                        </span>
                        <button
                            type="button"
                            data-drag-handle
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white/95 text-gray-500 shadow-sm transition hover:border-gray-300 hover:text-gray-700"
                            aria-label="Drag to reorder package"
                            title="Drag to reorder package"
                        >
                            <i class="fas fa-grip-vertical"></i>
                        </button>
                    </div>
                    @if($package->image)
                        <div class="w-full h-48 overflow-hidden">
                            <img src="{{ \App\Helpers\ImageHelper::src($package->image) }}" alt="{{ $package->name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                        </div>
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                        </div>
                    @endif

                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $package->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                            {{ $package->description ?: 'Experience an unforgettable adventure with this exclusive package.' }}
                        </p>

                        <div class="grid grid-cols-2 gap-2 mb-3 pb-3 border-b border-gray-200">
                            @if($durationLabel)
                                <div class="rounded-lg bg-gray-50 p-2 text-center">
                                    <i class="fas fa-calendar-alt text-[#8f5d54] text-base mb-1"></i>
                                    <p class="text-xs font-semibold text-gray-700">{{ $durationLabel }}</p>
                                </div>
                            @endif
                            @if($package->max_guests)
                                <div class="rounded-lg bg-gray-50 p-2 text-center">
                                    <i class="fas fa-users text-[#8f5d54] text-base mb-1"></i>
                                    <p class="text-xs font-semibold text-gray-700">
                                        {{ $package->pricing_type === 'per_group' ? 'Up to ' . $package->max_guests . ' / group' : $package->max_guests . ' Guest' . ($package->max_guests > 1 ? 's' : '') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <p class="text-2xl font-bold text-gray-900">
                                P{{ number_format((float) $package->price, 0) }}
                                <span class="text-sm font-normal text-gray-600">
                                    {{ $package->pricing_type === 'per_group' ? '/group' : '/head' }}
                                </span>
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $package->pricing_type === 'per_group' ? 'group pricing' : 'per head pricing, total depends on selected guests' }}
                            </p>
                        </div>

                        @if(count($packageInclusions) > 0)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($packageInclusions as $inclusion)
                                    <span class="inline-block rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                                        {{ $inclusion }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <div class="flex justify-between items-center pt-3 mt-auto border-t border-gray-200">
                            <span class="text-xs text-gray-500">{{ optional($package->created_at)->format('Y-m-d') }}</span>
                            <div class="flex items-center space-x-4">
                                <button class="text-[#8f5d54] font-semibold hover:text-[#7a4d46] transition text-sm" @click="editPackage({{ $package->id }})">Edit</button>
                                <button class="text-red-600 font-semibold hover:text-red-700 transition text-sm" @click="deletePackage({{ $package->id }}, '{{ route('admin.packages.destroy', $package->id) }}')">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Packages Yet</h3>
            <p class="text-gray-600 mb-6">Get started by creating your first package</p>
            <button @click="showPackageModal = true; resetForm()" class="inline-flex items-center px-6 py-3 bg-[#8f5d54] text-white rounded-xl hover:bg-[#7a4d46] transition font-semibold">
                <i class="fas fa-plus mr-2"></i> Create Package
            </button>
        </div>
    @endif

    {{-- Package Modal --}}
    <div
        x-show="showPackageModal"
        class="fixed inset-0 z-1000 bg-black/50 backdrop-blur-sm px-4 py-6 overflow-y-auto"
        x-transition.opacity
        @click.self="showPackageModal = false"
    >
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white/95 rounded-2xl shadow-xl w-full max-w-2xl border border-gray-200 max-h-[88vh] flex flex-col" @click.stop>
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900" x-text="isPackageEditMode ? 'Edit Package' : 'Create New Package'"></h3>
                    <button @click="showPackageModal = false" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                    <form @submit.prevent="savePackage" class="space-y-5">
                        @csrf
                        <input type="hidden" x-model="form.id" name="id" />
                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">Package Name *</label>
                            <input type="text" x-model="form.name" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">Description *</label>
                            <textarea x-model="form.description" rows="3" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold mb-2 text-gray-700">Price (P) *</label>
                                <input type="number" x-model="form.price" step="100" min="0" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2 text-gray-700">Duration *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" x-model="form.duration_value" min="1" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                    <select x-model="form.duration_unit" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                        <option value="minutes">Minute(s)</option>
                                        <option value="hours">Hour(s)</option>
                                        <option value="days">Day(s)</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2 text-gray-700" x-text="form.pricing_type === 'per_group' ? 'Max Guests *' : 'Guest Capacity *'"></label>
                                <input type="number" x-model="form.max_guests" min="1" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-2 text-gray-700">Pricing Type *</label>
                                <select x-model="form.pricing_type" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                                    <option value="per_pax">Per Head</option>
                                    <option value="per_group">Per Group</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-2 text-gray-700">Max Groups/Day *</label>
                                <input type="number" x-model="form.max_groups_per_day" min="1" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" required>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">Package Image</label>
                            <input type="file" x-ref="imageFile" accept="image/*" class="w-full border border-gray-300 rounded-xl px-4 py-2.5"
                                @change="handlePackageImageChange($el.files[0])">
                            <template x-if="form.imagePreview">
                                <div class="mt-3 flex items-start gap-3">
                                    <img
                                        :src="form.imagePreview"
                                        alt="Preview"
                                        class="h-32 w-32 rounded-lg border object-cover"
                                        x-on:error="$event.currentTarget.src='{{ \App\Helpers\ImageHelper::placeholder() }}'"
                                    >
                                    <button
                                        type="button"
                                        @click="removePackageImage()"
                                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                                    >
                                        <i class="fas fa-times"></i>
                                        Remove image
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">Inclusions</label>
                            <div class="flex gap-2 mb-3">
                                <input id="newInclusion" type="text" placeholder="e.g., Lunch, ATV Ride..." @keyup.enter="addInclusion()" class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm">
                                <button type="button" @click="addInclusion()" class="bg-[#8f5d54] hover:bg-[#7a4d46] text-white px-4 py-2.5 rounded-xl text-sm">Add</button>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 space-y-2" x-show="form.inclusionsList.length > 0">
                                <template x-for="(item, idx) in form.inclusionsList" :key="idx">
                                    <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-3 py-2">
                                        <span x-text="item" class="text-sm text-gray-700"></span>
                                        <button type="button" @click="removeInclusion(idx)" class="text-red-600 font-bold">x</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">Available Sessions</label>
                            <div class="bg-gray-50 rounded-xl p-3 space-y-3">
                                <template x-for="(session, idx) in form.sessions" :key="session.name">
                                    <div class="bg-white border border-gray-200 rounded-lg p-3">
                                        <label class="flex items-center gap-2 cursor-pointer mb-2">
                                            <input type="checkbox" x-model="session.enabled" class="w-4 h-4 rounded border-gray-300 text-[#8f5d54]">
                                            <span class="font-medium text-sm text-gray-800" x-text="session.name"></span>
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" :class="!session.enabled ? 'opacity-50' : ''">
                                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Start</label><input type="time" x-model="session.start" :disabled="!session.enabled" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm disabled:bg-gray-100"></div>
                                            <div><label class="block text-xs font-medium text-gray-600 mb-1">End</label><input type="time" x-model="session.end" :disabled="!session.enabled" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm disabled:bg-gray-100"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="pkg_active" x-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-[#8f5d54]">
                            <label for="pkg_active" class="font-medium text-gray-700">Active Package</label>
                        </div>
                        <div x-show="errors.length > 0" class="p-4 bg-red-50 border border-red-200 rounded-xl">
                            <template x-for="error in errors" :key="error">
                                <div x-text="error" class="text-red-800 text-sm"></div>
                            </template>
                        </div>
                    </form>
                </div>
                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200">
                    <button type="button" @click="showPackageModal = false" class="px-6 py-2.5 border border-gray-300 rounded-xl hover:bg-gray-100">Cancel</button>
                    <button type="button" @click="savePackage()" :disabled="isLoading" class="px-6 py-2.5 rounded-xl bg-[#8f5d54] text-white hover:bg-[#7a4d46] disabled:opacity-60" x-text="isLoading ? (isPackageEditMode ? 'Updating...' : 'Creating...') : (isPackageEditMode ? 'Update Package' : 'Create Package')"></button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
