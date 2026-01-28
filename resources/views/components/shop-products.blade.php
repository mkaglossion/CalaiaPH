{{-- Frozen Products --}}
<div class="mb-16">
  <h3 class="text-[25px] font-['Gotham Book',sans-serif] font-bold text-[#8f5d54] mb-8">Frozen Products</h3>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach ($frozenProducts as $product)
    <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300 flex flex-col min-h-125 relative">
      @if($product->image)
        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-56 object-cover bg-gray-100 rounded-t-lg" alt="{{ $product->title }}">
      @else
        <div class="w-full h-56 bg-gray-100 rounded-t-lg flex items-center justify-center">
          <span class="text-gray-500">No Image</span>
        </div>
      @endif

      {{-- Stock Availability Badge --}}
      @if($product->is_inventory_managed)
        @if($product->stock_quantity <= 0)
          <div class="absolute top-4 right-4 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold shadow-lg">
            Out of Stock
          </div>
        @elseif($product->stock_quantity <= 5)
          <div class="absolute top-4 right-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg">
            Only {{ $product->stock_quantity }} left
          </div>
        @else
          <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg">
            In Stock
          </div>
        @endif
      @endif
      <div class="mt-4 flex flex-col grow">
        <h4 class="text-xl font-semibold mb-2 text-[#8f5d54]">{{ $product->title }}</h4>
        <p class="text-base text-gray-600 mb-4 grow">{{ $product->description }}</p>
        <div class="text-lg font-bold text-[#8f5d54] mb-4">₱{{ number_format($product->price, 2) }}</div>
        <form action="{{ route('cart.add') }}" method="POST" class="inline mt-auto">
          @csrf
          <input type="hidden" name="id" value="{{ $product->id }}">
          <input type="hidden" name="title" value="{{ $product->title }}">
          <input type="hidden" name="price" value="{{ $product->price }}">
          <input type="hidden" name="image" value="{{ $product->image ? asset('storage/' . $product->image) : '' }}">
          <input type="hidden" name="quantity" value="1">
          <button type="submit" class="bg-brand text-white px-6 py-3 rounded-2xl hover:bg-brand transition-colors duration-300 w-full font-semibold">Add to Cart</button>
        </form>
      </div>
    </article>
    @endforeach
  </div>
</div>

{{-- Fresh Vegetables --}}
<div>
  <h3 class="text-[25px] font-['Gotham Book',sans-serif] font-bold text-[#8f5d54] mb-8">Fresh Vegetables</h3>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    @foreach ($freshProducts as $product)
    <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300 flex flex-col min-h-112.5 relative">
      @if($product->image)
        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-56 object-cover bg-gray-100 rounded-t-lg" alt="{{ $product->title }}">
      @else
        <div class="w-full h-56 bg-gray-100 rounded-t-lg flex items-center justify-center">
          <span class="text-gray-500">No Image</span>
        </div>
      @endif

      {{-- Stock Availability Badge --}}
      @if($product->is_inventory_managed)
        @if($product->stock_quantity <= 0)
          <div class="absolute top-4 right-4 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold shadow-lg">
            Out of Stock
          </div>
        @elseif($product->stock_quantity <= 5)
          <div class="absolute top-4 right-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg">
            Only {{ $product->stock_quantity }} left
          </div>
        @else
          <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg">
            In Stock
          </div>
        @endif
      @endif
      <div class="mt-4 flex flex-col grow">
        <h4 class="text-xl font-semibold mb-2 text-[#8f5d54]">{{ $product->title }}</h4>
        <p class="text-base text-gray-600 mb-4 grow">{{ $product->description }}</p>
        <div class="text-lg font-bold text-[#8f5d54] mb-4">₱{{ number_format($product->price, 2) }}</div>
        <form action="{{ route('cart.add') }}" method="POST" class="inline mt-auto">
          @csrf
          <input type="hidden" name="id" value="{{ $product->id }}">
          <input type="hidden" name="title" value="{{ $product->title }}">
          <input type="hidden" name="price" value="{{ $product->price }}">
          <input type="hidden" name="image" value="{{ $product->image ? asset('storage/' . $product->image) : '' }}">
          <input type="hidden" name="quantity" value="1">
          <button type="submit" class="bg-brand text-white px-6 py-3 rounded-2xl hover:bg-[#8f5d54]/90 transition-colors duration-300 w-full font-semibold">Add to Cart</button>
        </form>
      </div>
    </article>
    @endforeach
  </div>
</div>