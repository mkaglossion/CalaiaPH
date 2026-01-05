<x-page-layout
    title="CalaiaPH - Shop"
    description="Shop farm-fresh produce and quality frozen goods from CalaiaPH. Fresh vegetables, pasture-raised meats, and natural products delivered to you."
    keywords="shop, farm fresh produce, frozen goods, organic vegetables, pasture-raised meat, CalaiaPH shop, Philippines farm products"
    active-header="shop"
>
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css">
    @endpush

    {{-- Hero Slider --}}
    <x-hero-slider :images="$swiperImages" :texts="$texts" />

    <main id="main" class="">
      {{-- SECTION: Shop Our Products --}}
      <section id="shop" class="shop-section py-16" aria-labelledby="shop-products-heading">
        <div class="container mx-auto px-4 max-w-7xl">
          <x-section-header
              id="shop-products-heading"
              title="Shop Our Products"
              subtitle="Farm-fresh produce and quality frozen goods delivered straight from our fields to your table"
          />

          {{-- Frozen Products --}}
          <div class="mb-16">
            <h3 class="font-bold text-[#8f5d54] mb-8" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Frozen Products</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
              @foreach ([
                ['img'=>'Glamping-1.jpg','title'=>'Grass-fed Beef Burger Patties (500g)','desc'=>'Juicy, all-natural beef patties made from our pasture-raised cattle. No preservatives.','price'=>'₱350'],
                ['img'=>'Retreat-1164.jpg','title'=>'Heritage Pork Sausages (500g)','desc'=>'Traditional recipe, no fillers, made from our free-range heritage pigs.','price'=>'₱280'],
                ['img'=>'Retreat-705.jpg','title'=>'Pasture-raised Chicken Breast (1kg)','desc'=>'Tender, hormone-free chicken breast, perfect for healthy meals.','price'=>'₱420'],
              ] as $product)
              <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300">
                <img src="{{ asset('assets/images/carousel/'.$product['img']) }}" class="w-full h-56 object-cover bg-gray-100 rounded-t-lg" alt="{{ $product['title'] }}">
                <div class="mt-4">
                  <h4 class="text-xl font-semibold mb-2 text-[#8f5d54]">{{ $product['title'] }}</h4>
                  <p class="text-base text-gray-600 mb-4">{{ $product['desc'] }}</p>
                  <div class="text-lg font-bold text-[#8f5d54] mb-4">{{ $product['price'] }}</div>
                  <button class="btn custom-bg w-full mt-2 add-cart-btn" aria-label="Add {{ $product['title'] }} to cart">Add to Cart</button>
                </div>
              </article>
              @endforeach
            </div>
          </div>

          {{-- Fresh Vegetables --}}
          <div>
            <h3 class="font-bold text-[#8f5d54] mb-8" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Fresh Vegetables</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
              @foreach ([
                ['img'=>'organic.jpg','title'=>'Heirloom Tomatoes (500g)','desc'=>'Colorful, sweet, and juicy tomatoes grown without pesticides.','price'=>'₱120'],
                ['img'=>'our-farm.jpg','title'=>'Rainbow Chard (250g)','desc'=>'Vibrant, nutrient-rich chard, perfect for salads and sautés.','price'=>'₱90'],
                ['img'=>'our-farm2.jpg','title'=>'Zucchini (500g)','desc'=>'Fresh, tender zucchini, harvested daily from our fields.','price'=>'₱80'],
              ] as $product)
              <article class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 hover:shadow-lg transition-shadow duration-300">
                <img src="{{ asset('assets/images/carousel/'.$product['img']) }}" class="w-full h-56 object-cover bg-gray-100 rounded-t-lg" alt="{{ $product['title'] }}">
                <div class="mt-4">
                  <h4 class="text-xl font-semibold mb-2 text-[#8f5d54]">{{ $product['title'] }}</h4>
                  <p class="text-base text-gray-600 mb-4">{{ $product['desc'] }}</p>
                  <div class="text-lg font-bold text-[#8f5d54] mb-4">{{ $product['price'] }}</div>
                  <button class="btn custom-bg w-full mt-2 add-cart-btn" aria-label="Add {{ $product['title'] }} to cart">Add to Cart</button>
                </div>
              </article>
              @endforeach
            </div>
          </div>
        </div>
      </section>
    </main> 
    {{-- Floating Buttons --}}
    <x-floating-buttons />
</x-page-layout>