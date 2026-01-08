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

          <x-shop-products />
        </div>
      </section>
    </main> 
    {{-- Floating Buttons --}}
    <x-floating-buttons />
</x-page-layout>