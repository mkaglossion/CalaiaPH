@extends('layouts.admin')

@section('title', 'Shop Inventory Monitor')

@section('content')
<div class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
            Store Shop Inventory Monitor
        </h1>
        <a href="{{ route('products.index') }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
            Manage Products
        </a>
    </div>
    <p class="text-gray-600">Monitor stock levels for products with inventory management enabled.</p>

    {{-- Search and Filter Form --}}
    <div class="bg-white/70 backdrop-blur-lg rounded-2xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('admin.inventory.shop') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search by Name</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Search products...">
            </div>

            <div class="min-w-48">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Filter by Category</label>
                <select name="category" id="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                            {{ ucfirst($cat) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-48">
                <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-2">Filter by Stock Status</label>
                <select name="stock_status" id="stock_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="{{ route('admin.inventory.shop') }}" class="px-4 py-2 rounded-xl bg-gray-500 text-white font-medium shadow hover:bg-gray-600 hover:shadow-lg transition">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Current Stock Levels</h2>
            <p class="text-sm text-gray-600 mt-1">Monitor inventory levels for products with inventory management enabled</p>
        </div>

        {{-- Responsive table container - horizontal scroll only on mobile --}}
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-12 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Category</th>
                        <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Current Stock</th>
                        <th class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Price</th>
                        <th class="w-40 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($products as $index => $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $products->firstItem() + $index }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($product->image)
                                    @php
                                        $imageSrc = str_contains($product->image, 'res.cloudinary.com') 
                                            ? $product->image 
                                            : asset('storage/' . $product->image);
                                    @endphp
                                <img class="h-10 w-10 rounded-lg object-cover shrink-0" src="{{ $imageSrc }}" alt="{{ $product->title }}">
                                @else
                                <div class="h-10 w-10 rounded-lg bg-gray-200 shrink-0 flex items-center justify-center">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 wrap-break-word">
                                        {{ $product->title }}
                                    </div>
                                    <div class="text-sm text-gray-500 wrap-break-word">
                                        {{ $product->description }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($product->category) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium {{ $product->stock_quantity <= 0 ? 'text-red-600' : ($product->stock_quantity <= 5 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($product->stock_quantity <= 0)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                            @elseif($product->stock_quantity <= 5)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Low Stock
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    In Stock
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                            ₱{{ number_format($product->price, 2) }}
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-500">
                            {{ $product->updated_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No products with inventory management found.
                            <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-700 ml-2">Manage Products</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links('pagination::tailwind') }}
        </div>
        @endif
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Inventory Management</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Product inventory is automatically reduced when orders are completed through the checkout process. Items marked as "Out of Stock" or "Low Stock" should be restocked manually through the product management page.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    if (typeof Echo !== 'undefined') {
        Echo.channel('product-stock')
            .listen('.product.updated', (e) => {
                console.log('Product stock updated:', e);
                // Refresh the page to show updated inventory
                location.reload();
            });
    }
</script>
@endpush
