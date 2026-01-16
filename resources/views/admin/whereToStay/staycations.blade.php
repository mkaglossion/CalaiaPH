@extends('layouts.admin')

@section('title', 'Staycations')

@section('content')
<div
    x-data="staycationComponent()"

>
    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Staycations</h2>
        <button
            type="button"
            @click="showStaycationModal = true; resetForm()"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Staycation
        </button>
    </div>

    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Today Avail</th>
                    <th class="px-4 py-2">Adults</th>
                    <th class="px-4 py-2">Children</th>
                    <th class="px-4 py-2">Pricing</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staycations as $index => $act)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $act->name ?? 'Untitled' }}</td>
                    <td class="px-4 py-2">
                        @php
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                            $inv = \App\Models\StaycationInventory::where('staycation_id', $act->id)
                                ->whereDate('date', $today)
                                ->first();
                            $todayAvail = $inv ? $inv->available_quantity : ($act->quantity ?? 0);
                        @endphp
                        {{ $todayAvail }}
                    </td>
                    <td class="px-4 py-2">{{ $act->adults ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $act->children ?? 'N/A' }}</td>
                    <td class="px-4 py-2">
                        @if($act->price_configs && count($act->price_configs) > 0)
                            @php $config = $act->price_configs[0]; @endphp
                            Non-Peak: ₱{{ $config['rates']['nonPeak'] ?? 0 }}, Peak: ₱{{ $config['rates']['peak'] ?? 0 }}, Super Peak: ₱{{ $config['rates']['superPeak'] ?? 0 }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $act->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <button type="button" class="text-blue-600 hover:underline" @click="editStaycation({{ $act->id }})">
                            Edit
                        </button>
                        <form action="{{ route('staycation-list.destroy', $act->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="text-red-600 hover:underline"
                                onclick="return confirm('Delete this staycation?')"
                            >
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-4 text-center text-gray-500">No staycations added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="showStaycationModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" x-cloak>
        <div @click.away="showStaycationModal = false" class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">

            <h3 class="text-lg font-semibold mb-4" x-text="editModeStaycation ? 'Edit Staycation' : 'Add Staycation'"></h3>

            <form id="staycationForm" @submit.prevent="saveStaycation()" class="space-y-5">
                @csrf

                <!-- Staycation Name, Max Capacity, Adults & Children -->
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Staycation Name</label>
                        <input type="text" x-model="form.name" class="w-full border rounded px-3 py-2 h-10" placeholder="Premium Glamping Tent" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Max Capacity</label>
                        <div class="flex items-center gap-2">
                            <input type="number" x-model.number="form.quantity" min="1" class="w-full border rounded px-3 py-2 h-10" placeholder="1" @input="updateThresholdFromQuantity()">
                            <button type="button" class="shrink-0 text-xs px-3 py-2 h-10 rounded border bg-white hover:bg-gray-50" @click="computeAutoCapacity()">
                                Suggest
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Base capacity; per-date inventory can be lower. Threshold (30%): <span class="font-semibold" x-text="computedThreshold"></span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Adults</label>
                        <input type="number" x-model.number="form.adults" min="0" class="w-full border rounded px-3 py-2 h-10" placeholder="0" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Children</label>
                        <input type="number" x-model.number="form.children" min="0" class="w-full border rounded px-3 py-2 h-10" placeholder="0" required>
                    </div>
                </div>

                <!-- Dynamic Price Configs -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium">Price Configurations</label>
                    <template x-for="(config, index) in form.priceConfigs" :key="index">
                        <div class="border p-3 rounded space-y-2">

                            <!-- Config Name and Type -->
                            <div class="flex space-x-2 items-center">
                                <input type="text" x-model="config.name" placeholder="Config Name (Weekend, Holiday, etc.)" class="flex-1 border rounded px-2 py-1" required>

                                <select x-model="config.type" class="border rounded px-2 py-1">
                                    <option value="day">Day Type</option>
                                    <option value="date">Specific Dates</option>
                                </select>

                                <button type="button" @click="removePriceConfig(index)" class="text-red-500 font-bold">&times;</button>
                            </div>

                            <!-- Days checkboxes -->
                            <div x-show="config.type === 'day'" class="flex space-x-2">
                                <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                                    <label class="flex items-center space-x-1">
                                        <input type="checkbox" :value="day" x-model="config.days">
                                        <span x-text="day"></span>
                                    </label>
                                </template>
                            </div>

                            <!-- Dates input -->
                            <div x-show="config.type === 'date'" class="space-y-2">
                                <label class="block text-sm font-medium">Select Date(s)</label>

                                <!-- Single date -->
                                <template x-if="!config.multipleDates">
                                    <input type="date" x-model="config.dates" class="w-full border rounded px-2 py-1">
                                </template>

                                <!-- Multiple dates (comma-separated) -->
                                <template x-if="config.multipleDates">
                                    <input type="text" x-model="config.dates" placeholder="YYYY-MM-DD, YYYY-MM-DD" class="w-full border rounded px-2 py-1">
                                </template>

                                <!-- Toggle for single/multiple dates -->
                                <label class="flex items-center space-x-2 mt-1">
                                    <input type="checkbox" x-model="config.multipleDates">
                                    <span class="text-sm text-gray-600">Allow multiple dates (comma-separated)</span>
                                </label>
                            </div>

                            <!-- Rates per season -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="flex flex-col">
                                    <label class="text-sm font-semibold mb-1">Non-Peak</label>
                                    <input type="number" x-model.number="config.rates.nonPeak" class="border rounded px-2 py-1" min="0">
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-semibold mb-1">Peak</label>
                                    <input type="number" x-model.number="config.rates.peak" class="border rounded px-2 py-1" min="0">
                                </div>
                                <div class="flex flex-col">
                                    <label class="text-sm font-semibold mb-1">Super Peak</label>
                                    <input type="number" x-model.number="config.rates.superPeak" class="border rounded px-2 py-1" min="0">
                                </div>
                            </div>

                        </div>
                    </template>

                    <button type="button" @click="addPriceConfig()" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">+ Add Price Config</button>
                </div>

                <!-- ===== INVENTORY MANAGEMENT ===== -->
                <div class="border-t pt-5 mt-5">
                    <h4 class="text-lg font-semibold mb-4 text-gray-900">📅 Inventory Management</h4>
                    
                    <div x-show="!form.id" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-yellow-800">💡 <strong>Tip:</strong> Save the staycation first, then return to this section to set inventory for specific dates.</p>
                    </div>

                    <div x-show="form.id">
                        <!-- Tab Selection -->
                        <div class="flex gap-2 mb-4 border-b">
                            <button type="button" @click="inventoryTab = 'quick'" :class="inventoryTab === 'quick' ? 'border-b-2 border-green-600 text-green-600 font-semibold' : 'text-gray-600'" class="pb-2 px-4">
                                ⚡ Quick Set (30 days)
                            </button>
                            <button type="button" @click="inventoryTab = 'custom'" :class="inventoryTab === 'custom' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-600'" class="pb-2 px-4">
                                📆 Custom Date Range
                            </button>
                        </div>

                        <!-- Quick Set Tab -->
                        <div x-show="inventoryTab === 'quick'" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-green-900 mb-3">Set the same quantity for the next 30 days</p>
                            <div class="flex gap-2 items-end">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium mb-1">Quantity per day</label>
                                    <input type="number" x-model.number="inventoryQty" min="1" value="10" class="w-full border rounded px-3 py-2">
                                </div>
                                <button type="button" @click="quickSetInventory()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-semibold whitespace-nowrap">
                                    Apply Quick Set
                                </button>
                            </div>
                        </div>

                        <!-- Custom Date Range Tab -->
                        <div x-show="inventoryTab === 'custom'" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-blue-900 mb-3">Set inventory for a specific date range</p>
                            <div class="space-y-3">
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Start Date</label>
                                        <input type="date" x-model="inventoryStartDate" class="w-full border rounded px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">End Date</label>
                                        <input type="date" x-model="inventoryEndDate" class="w-full border rounded px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Quantity</label>
                                        <input type="number" x-model.number="inventoryQtyRange" min="0" value="10" class="w-full border rounded px-3 py-2">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="inventoryAvailable" checked>
                                        <span class="text-sm font-medium">Available for booking</span>
                                    </label>
                                    <button type="button" @click="addDateRangeInventory()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold">
                                        Add Range
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Preview -->
                        <div class="bg-gray-50 border rounded-lg p-4 max-h-64 overflow-y-auto">
                            <h5 class="font-semibold text-gray-800 mb-3">📋 Upcoming Inventory (Next 30 Days)</h5>
                            <div id="inventoryPreview" class="text-sm text-gray-700">
                                Loading...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== END INVENTORY MANAGEMENT ===== -->

                <!-- Image Upload Section -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium">Upload Image (optional)</label>
                    <input type="file" accept="image/*" x-ref="imageInput" @change="handleImageUpload($event)" class="w-full border rounded px-3 py-2">
                    <div x-show="form.imagePreview" class="mt-2">
                        <img :src="form.imagePreview" class="w-32 h-20 object-cover rounded border">
                        <button type="button" @click="removeImage()" class="ml-2 text-red-500 hover:underline">Remove</button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="showStaycationModal = false; resetForm()" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" x-text="editModeStaycation ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>





    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Swiper Images Management</h2>
        <button 
            @click="showModal = true" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Image
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Preview</th>
                    <th class="px-4 py-2">Staycation Name</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Uploaded</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($images as $index => $img)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">
                            <img src="{{ $img->image_path }}" alt="{{ $img->title }}" class="w-20 h-12 object-cover rounded">
                        </td>
                        <td class="px-4 py-2">{{ $img->staycation->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $img->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <button class="text-blue-600 hover:underline" @click="editImage({{ $img->id }}, '{{ $img->staycation_id }}', '{{ $img->title }}')">
                                Edit
                            </button>
                            <form action="{{ route('staycation-image.destroy', $img->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this image?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No images uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal --}}
    <div 
        x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
        x-transition
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button 
                @click="showModal = false"
                class="absolute top-2 right-3 text-gray-400 hover:text-gray-600 text-2xl leading-none"
            >&times;</button>

            <h3 class="text-lg font-semibold mb-4" x-text="editImageMode ? 'Edit Swiper Image' : 'Upload New Swiper Image'"></h3>

            <form
                id="swiperUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                :action="editImageMode ? '/admin/staycation-image/' + currentImageId : '{{ route('staycation-image.store') }}'"
                method="POST"
                @submit.prevent="
                    const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    const fileInput = $refs.image.files[0];
                    const title = $refs.title.value;
                    const staycationId = $refs.staycation.value;

                    if (!editImageMode && !fileInput) {
                        alert('Please select an image before uploading.');
                        return;
                    }

                    if (!staycationId) {
                        alert('Please select an staycation.');
                        return;
                    }

                    const formData = new FormData();
                    if (fileInput) formData.append('image', fileInput);
                    formData.append('title', title);
                    formData.append('staycation_id', staycationId);
                    if (editImageMode) formData.append('_method', 'PUT');

                    const btn = $el.querySelector('button[type=submit]');
                    btn.innerText = editImageMode ? 'Updating...' : 'Uploading...';
                    btn.disabled = true;

                    const url = editImageMode ? '/admin/staycation-image/' + currentImageId : '{{ route('staycation-image.store') }}';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(async res => {
                        let data;
                        try { data = await res.json(); } catch { throw new Error('Invalid JSON'); }

                        if (res.ok) {
                            alert(data.message || (editImageMode ? 'Image updated successfully!' : 'Image uploaded successfully!'));
                            $refs.image.value = '';
                            $refs.title.value = '';
                            $refs.staycation.value = '';
                            showModal = false;
                            resetImageForm();
                            window.location.reload();
                        } else if (data.errors) {
                            const errorMessages = Object.values(data.errors).flat().join('\\n');
                            alert(errorMessages);
                        } else {
                            alert(data.message || 'Operation failed.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error ' + (editImageMode ? 'updating' : 'uploading') + ' image.');
                    })
                    .finally(() => {
                        btn.innerText = editImageMode ? 'Update' : 'Upload';
                        btn.disabled = false;
                    });
                "
            >
                @csrf
                <input type="hidden" name="_method" value="POST" x-bind:value="editImageMode ? 'PUT' : 'POST'">
                <div class="mb-3">
                    <label class="block font-semibold mb-1">Staycation</label>
                    <select x-ref="staycation" name="staycation_id" class="border rounded p-2 w-full" x-bind:value="editImageMode ? currentStaycationId : ''" required>
                        <option value="">Select Staycation</option>
                        @foreach($staycations as $staycation)
                            <option value="{{ $staycation->id }}">{{ $staycation->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Title (optional)</label>
                    <input type="text" x-ref="title" name="title" class="border rounded p-2 w-full" x-bind:value="editImageMode ? currentImageTitle : ''">
                </div>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Upload Image/Video</label>
                    <input type="file" x-ref="image" name="image" accept="image/*,video/*" class="border rounded p-2 w-full" x-bind:required="!editImageMode">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showModal = false; resetImageForm()" class="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded" x-text="editImageMode ? 'Update' : 'Upload'"></button>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-6">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Text Content Management</h2>
        <button 
            @click="showTextModal = true" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
        >
            + Add Text Content
        </button>
    </div>

    {{-- Text Table --}}
    <div class="overflow-x-auto border rounded-lg shadow">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Staycation Name</th>
                    <th class="px-4 py-2">Label</th>
                    <th class="px-4 py-2">Text</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($texts as $index => $text)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $text->staycation->name ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->title ?? 'Untitled' }}</td>
                        <td class="px-4 py-2">{{ $text->content ?? 'Untitled' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <form action="{{ route('staycation-text.destroy', $text->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this text content?')"
                                >
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">No text content added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Text Content Modal -->
    <div 
        x-show="showTextModal" 
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
        x-cloak
    >
        <div 
            @click.away="showTextModal = false" 
            class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6"
        >
            <h3 class="text-lg font-semibold mb-4" x-text="editMode ? 'Edit Text Content' : 'Add Text Content'"></h3>

            <form
                id="textUploadForm"
                class="space-y-5"
                enctype="multipart/form-data"
                @submit.prevent="saveText()"
            >
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-3">
                    <label class="block font-semibold mb-1">Staycation</label>
                    <select 
                        name="staycation_id" 
                        x-model="form.staycation_id" 
                        class="border rounded p-2 w-full" 
                        required
                    >
                        <option value="">Select Staycation</option>
                        @foreach($staycations as $staycation)
                            <option value="{{ $staycation->id }}">{{ $staycation->name }}</option>
                        @endforeach
                    </select>
                </div>

            
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Label</label>
                    <input 
                        type="text" 
                        name="label" 
                        x-model="form.label" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Optional"
                    >
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Text</label>
                    <textarea 
                        name="text" 
                        x-model="form.text" 
                        rows="4" 
                        class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        placeholder="Enter text text..."
                        required
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button 
                        type="button" 
                        @click="showTextModal = false" 
                        class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    >
                        <span x-text="editMode ? 'Update' : 'Add'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
function staycationComponent() {
    return {
        showModal: false,
        showStaycationModal: false,
        editModeStaycation: false,
        showTextModal: false,
        editMode: false,
        editImageMode: false,
        currentImageId: null,
        currentStaycationId: '',
        currentImageTitle: '',
        staycations: @js($staycations),
        form: {
            id: null,
            name: '',
            quantity: 1,
            adults: 0,
            children: 0,
            priceConfigs: [],
            images: [],
            staycation_id: '',
            label: '',
            text: ''
        },

        computedThreshold: 0,

        resetForm() {
            this.form = { id: null, name: '', quantity: 1, adults: 0, children: 0, priceConfigs: [], image: null, imagePreview: null, existingImage: null, removeImage: false };
            this.editModeStaycation = false;
            this.removeImage(); // Explicitly clear image and input
        },

        resetImageForm() {
            this.editImageMode = false;
            this.currentImageId = null;
            this.currentStaycationId = '';
            this.currentImageTitle = '';
        },

        editImage(id, staycationId, title) {
            this.currentImageId = id;
            this.currentStaycationId = staycationId;
            this.currentImageTitle = title || '';
            this.editImageMode = true;
            this.showModal = true;
        },


        editStaycation(id) {
            this.resetForm();
            const staycation = this.staycations.find(s => s.id === id);
            if (staycation) {
                this.form.id = staycation.id;
                this.form.name = staycation.name;
                this.form.quantity = staycation.quantity;
                this.form.adults = staycation.adults;
                this.form.children = staycation.children;
                // Align inventory inputs with base capacity
                this.inventoryQty = Number(staycation.quantity || 1);
                this.inventoryQtyRange = Number(staycation.quantity || 1);
                this.inventoryTab = 'quick';
                // Compute initial threshold and optionally auto capacity
                this.updateThresholdFromQuantity();
                this.form.priceConfigs = (staycation.price_configs || []).map(pc => {
                    const datesStr = pc.dates || '';
                    const isMultiple = datesStr.includes(',');
                    return {
                        ...pc,
                        multipleDates: isMultiple,
                        dates: datesStr
                    };
                });
                // Handle existing image
                if (staycation.image) {
                    this.form.imagePreview = staycation.image;
                    this.form.existingImage = staycation.image; // Store original
                    this.form.removeImage = false; // Track if user wants to remove
                }
                this.editModeStaycation = true;
                this.showStaycationModal = true;
                // Load preview for this staycation
                this.$nextTick(() => this.loadInventoryPreview());
            }
        },

        async computeAutoCapacity() {
            if (!this.form.id) { this.updateThresholdFromQuantity(); return; }
            try {
                const r = await fetch(`/api/inventory/${this.form.id}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                const data = await r.json();
                const items = Array.isArray(data.items) ? data.items : [];
                const maxQty = items.length ? Math.max(...items.map(i => Number(i.min_quantity || 0))) : Number(this.form.quantity || 1);
                this.form.quantity = isFinite(maxQty) && maxQty > 0 ? maxQty : 1;
                this.inventoryQty = this.form.quantity;
                this.inventoryQtyRange = this.form.quantity;
                this.updateThresholdFromQuantity();
            } catch (e) {
                this.updateThresholdFromQuantity();
            }
        },

        updateThresholdFromQuantity() {
            const base = Number(this.form.quantity || 0);
            const thr = Math.max(1, Math.floor(base * 0.3));
            this.computedThreshold = base > 0 ? thr : 0;
        },

        addPriceConfig() {
            this.form.priceConfigs.push({
                name: '',
                type: 'day',
                days: [],
                dates: '',
                multipleDates: false,
                rates: { nonPeak: 0, peak: 0, superPeak: 0 }
            });
        },

        removePriceConfig(index) {
            this.form.priceConfigs.splice(index, 1);
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.form.image = file;
                    this.form.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        removeImage() {
            this.form.image = null;
            this.form.imagePreview = null;
            this.form.removeImage = true; // Mark for deletion
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = '';
            }
        },



        saveStaycation: async function() {
            // Validate form data
            if (!this.form.name.trim()) {
                alert('Please enter a staycation name.');
                return;
            }
            if (!this.form.quantity || this.form.quantity < 1) {
                alert('Please enter a valid quantity.');
                return;
            }
            if (this.form.adults < 0 || this.form.children < 0) {
                alert('Please enter valid numbers for adults and children.');
                return;
            }
            if (this.form.adults + this.form.children === 0) {
                alert('At least one adult or child is required.');
                return;
            }
            if (!this.form.priceConfigs || this.form.priceConfigs.length === 0) {
                alert('Please add at least one price configuration.');
                return;
            }

            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('_token', csrf);
            formData.append('name', this.form.name);
            formData.append('quantity', this.form.quantity);
            formData.append('adults', this.form.adults);
            formData.append('children', this.form.children);
            formData.append('priceConfigs', JSON.stringify(this.form.priceConfigs));
            if (this.form.image) {
                formData.append('image', this.form.image);
            }
            // Explicitly mark image for deletion if user removed it
            if (this.form.removeImage) {
                formData.append('remove_image', '1');
            }

            let url = '{{ route("staycation-list.store") }}';
            let method = 'POST';
            if (this.editModeStaycation && this.form.id) {
                url = `{{ url('/admin/staycation-list') }}/${this.form.id}`;
                formData.append('_method', 'PUT');
            }

            const btn = document.querySelector('#staycationForm button[type=submit]');
            btn.innerText = this.editModeStaycation ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    alert(data.message || (this.editModeStaycation ? 'Staycation updated!' : 'Staycation added!'));
                    this.resetForm();
                    this.showStaycationModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error('Error submitting staycation:', err);
                alert('Error submitting staycation: ' + (err.message || 'Unknown error'));
            } finally {
                btn.innerText = this.editModeStaycation ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },

        saveText: async function() {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = new FormData();
            formData.append('staycation_id', this.form.staycation_id);
            formData.append('title', this.form.label);
            formData.append('content', this.form.text);

            let url = '{{ route("staycation-text.store") }}';
            let method = 'POST';
            if (this.editMode && this.form.id) {
                url = `{{ url('/admin/staycation-text') }}/${this.form.id}`;
                method = 'PUT';
            }

            const btn = document.querySelector('#textUploadForm button[type=submit]');
            btn.innerText = this.editMode ? 'Updating...' : 'Adding...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                    credentials: 'same-origin'
                });
                const data = await res.json();
                if (res.ok) {
                    alert(data.message || (this.editMode ? 'Text updated!' : 'Text added!'));
                    this.form.staycation_id = '';
                    this.form.label = '';
                    this.form.text = '';
                    this.editMode = false;
                    this.showTextModal = false;
                    window.location.reload();
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    alert(data.message || 'Operation failed.');
                }
            } catch(err) {
                console.error(err);
                alert('Error submitting text.');
            } finally {
                btn.innerText = this.editMode ? 'Update' : 'Add';
                btn.disabled = false;
            }
        },

        // ===== INVENTORY FUNCTIONS =====
        inventoryTab: 'quick',
        inventoryQty: 10,
        inventoryStartDate: '',
        inventoryEndDate: '',
        inventoryQtyRange: 10,
        inventoryAvailable: true,

        quickSetInventory() {
            if (!this.form.id) {
                alert('❌ Save the staycation first!');
                return;
            }
            const qty = this.inventoryQty;
            if (!qty || qty < 1) {
                alert('❌ Please enter a valid quantity');
                return;
            }
            this.sendInventoryRequest({
                room_id: this.form.id,
                quantity: qty,
                type: 'quickset'
            });
        },

        addDateRangeInventory() {
            if (!this.form.id) {
                alert('❌ Save the staycation first!');
                return;
            }
            if (!this.inventoryStartDate || !this.inventoryEndDate) {
                alert('❌ Please select start and end dates');
                return;
            }
            if (this.inventoryStartDate > this.inventoryEndDate) {
                alert('❌ Start date must be before end date');
                return;
            }
            this.sendInventoryRequest({
                room_id: this.form.id,
                start_date: this.inventoryStartDate,
                end_date: this.inventoryEndDate,
                quantity: this.inventoryQtyRange,
                is_available: this.inventoryAvailable,
                type: 'range'
            });
        },

        sendInventoryRequest(data) {
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            let endpoint = '/admin/inventory/store';
            
            if (data.type === 'quickset') {
                endpoint = '/admin/inventory/quickset';
            }

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
            .then(async (r) => {
                const raw = await r.text();
                let res;
                try {
                    res = raw ? JSON.parse(raw) : {};
                } catch (e) {
                    throw new Error(`${r.status} ${r.statusText} - response not JSON`);
                }

                if (!r.ok || res.success === false) {
                    const msg = res.message
                        || (res.errors ? Object.values(res.errors).flat().join('\n') : null)
                        || `Request failed (${r.status})`;
                    throw new Error(msg);
                }

                alert('✅ ' + (res.message || 'Saved'));
                this.inventoryStartDate = '';
                this.inventoryEndDate = '';
                this.inventoryQtyRange = 10;
                this.loadInventoryPreview();
                // Refresh the page so the listing table reflects new availability
                setTimeout(() => window.location.reload(), 300);
            })
            .catch(err => alert('❌ ' + err.message));
        },

        loadInventoryPreview() {
            if (!this.form.id) return;
            const previewDiv = document.getElementById('inventoryPreview');
            if (!previewDiv) return;

            previewDiv.innerHTML = '<span class="text-gray-600">Loading...</span>';

            fetch(`/api/inventory/${this.form.id}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.items || data.items.length === 0) {
                        previewDiv.innerHTML = '<span class="text-gray-500">No inventory set yet</span>';
                        return;
                    }
                    let html = '<table class="w-full text-sm"><tr class="border-b"><th class="text-left py-1">Date</th><th class="text-center py-1">Qty</th><th class="text-center py-1">Status</th></tr>';
                    data.items.forEach(inv => {
                        const dateObj = new Date(inv.date);
                        const dateStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const status = inv.is_available ? '✅ Available' : '🚫 Blocked';
                        html += `<tr class="border-b"><td class="py-1">${dateStr}</td><td class="text-center">${inv.min_quantity}</td><td class="text-center text-xs">${status}</td></tr>`;
                    });
                    html += '</table>';
                    previewDiv.innerHTML = html;
                })
                .catch(err => {
                    previewDiv.innerHTML = '<span class="text-red-500">Error loading inventory</span>';
                });
        },

    };
}

</script>


@endsection
