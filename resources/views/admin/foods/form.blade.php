@extends('layouts.admin')

@section('title', isset($food) ? 'Edit Food Item' : 'Create Food Item')

@section('content')
<div>
    <hr class="my-6">

    <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
        <i data-lucide="utensils" class="w-6 h-6"></i>
        @if (isset($food))
            Edit Food Item
        @else
            Create New Food Item
        @endif
    </h2>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <div class="flex gap-3">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <div>
                    <p class="font-semibold">Validation Errors</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" 
          action="@if (isset($food)) {{ route('admin.foods.update', $food->id) }} @else {{ route('admin.foods.store') }} @endif"
          class="bg-white rounded-lg shadow p-6 max-w-3xl">
        @csrf
        @if (isset($food))
            @method('PUT')
        @endif

        {{-- Food Name --}}
        <div class="mb-6">
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                <i data-lucide="type" class="w-4 h-4 inline mr-1"></i>Food Name *
            </label>
            <input type="text" id="name" name="name" required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                   value="{{ isset($food) ? $food->name : old('name') }}"
                   placeholder="e.g., Breakfast Buffet">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Category & Price --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i data-lucide="tag" class="w-4 h-4 inline mr-1"></i>Category *
                </label>
                <select id="category" name="category" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">Select Category</option>
                    <option value="breakfast" @if(isset($food) && $food->category == 'breakfast') selected @endif>Breakfast</option>
                    <option value="lunch" @if(isset($food) && $food->category == 'lunch') selected @endif>Lunch</option>
                    <option value="dinner" @if(isset($food) && $food->category == 'dinner') selected @endif>Dinner</option>
                    <option value="package" @if(isset($food) && $food->category == 'package') selected @endif>Package</option>
                </select>
                @error('category')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i data-lucide="coins" class="w-4 h-4 inline mr-1"></i>Price (₱) *
                </label>
                <input type="number" id="price" name="price" step="0.01" min="0" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                       value="{{ isset($food) ? $food->price : old('price') }}"
                       placeholder="1500">
                @error('price')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Description --}}
        <div class="mb-6">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i>Description
            </label>
            <textarea id="description" name="description" rows="4"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                      placeholder="Describe your food item...">{{ isset($food) ? $food->description : old('description') }}</textarea>
            @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Image Upload --}}
        <div class="mb-6">
            <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                <i data-lucide="image" class="w-4 h-4 inline mr-1"></i>Image
            </label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                 onclick="document.getElementById('image').click()">
                @if (isset($food) && $food->image)
                    <div class="mb-4">
                        <img src="{{ $food->image }}" alt="{{ $food->name }}" class="max-h-40 mx-auto rounded-lg shadow">
                    </div>
                    <label class="flex items-center justify-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="rounded"> 
                        <span>Remove Current Image</span>
                    </label>
                @else
                    <div class="flex flex-col items-center gap-2">
                        <i data-lucide="cloud-upload" class="w-8 h-8 text-gray-400"></i>
                        <p class="text-gray-600 font-medium">Click to upload or drag and drop</p>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                    </div>
                @endif
                <input type="file" id="image" name="image" accept="image/*" class="hidden">
            </div>
            @error('image')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Active Status --}}
        <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" id="is_active" name="is_active" value="1" 
                       @if(isset($food) && $food->is_active) checked @elseif(!isset($food)) checked @endif
                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <div>
                    <p class="font-semibold text-gray-900">Active Status</p>
                    <p class="text-xs text-gray-600">When checked, this item will be visible on the booking page</p>
                </div>
            </label>
        </div>

        {{-- Form Actions --}}
        <div class="flex gap-4 pt-6 border-t">
            <button type="submit" class="flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                <i data-lucide="save" class="w-4 h-4"></i>
                @if (isset($food))
                    Update Food Item
                @else
                    Create Food Item
                @endif
            </button>
            <a href="{{ route('admin.foods.index') }}" class="flex items-center gap-2 px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition font-medium">
                <i data-lucide="x" class="w-4 h-4"></i>
                Cancel
            </a>
        </div>
    </form>

    <hr class="my-6">
</div>

@endsection
