@extends('layouts.admin')

@section('title', 'Food Management')

@section('content')
<div>
    <hr class="my-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Food & Beverage Items</h2>
        <a href="{{ route('admin.foods.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add New Food Item
        </a>
    </div>

    @if ($message = Session::get('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-start gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-semibold">Success</p>
                <p class="text-sm">{{ $message }}</p>
            </div>
        </div>
    @endif

    {{-- Food Items Table --}}
    <div class="overflow-x-auto border rounded-lg shadow bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 font-semibold text-gray-700">#</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Category</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Price</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Image</th>
                    <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-3 font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($foods as $index => $food)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $food->name }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-semibold">
                                {{ ucfirst($food->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">₱{{ number_format($food->price, 2) }}</td>
                        <td class="px-6 py-4">
                            @if ($food->image)
                                <img src="{{ $food->image }}" alt="{{ $food->name }}" class="h-12 w-12 rounded object-cover border border-gray-200">
                            @else
                                <span class="text-gray-400 text-xs">No image</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if ($food->is_active)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Active
                                </span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold flex items-center gap-1 w-fit">
                                    <i data-lucide="x-circle" class="w-3 h-3"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('admin.foods.edit', $food->id) }}" class="text-blue-600 hover:text-blue-900 font-medium text-sm transition">
                                <i data-lucide="edit" class="w-4 h-4 inline mr-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('admin.foods.destroy', $food->id) }}" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this food item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm transition">
                                    <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i>Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                <p class="text-gray-500">No food items found.</p>
                                <a href="{{ route('admin.foods.create') }}" class="text-blue-600 hover:underline font-medium text-sm">Create your first food item →</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <hr class="my-6">
</div>

@endsection
