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

    {{-- Real-time inventory updates --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Listen for product stock updates
            window.Echo.channel('product-stock')
                .listen('.product.updated', (e) => {
                    console.log('Product stock updated:', e);
                    updateProductStockDisplay(e.id, e.stock_quantity, e.is_inventory_managed);
                });
        });

        function updateProductStockDisplay(productId, stockQuantity, isInventoryManaged) {
            // Find all product cards with this ID
            const productCards = document.querySelectorAll(`form[action*="/cart/add"] input[name="id"][value="${productId}"]`);

            productCards.forEach(input => {
                const form = input.closest('form');
                const card = form.closest('article');
                const button = form.querySelector('button[type="submit"]');
                const stockBadge = card.querySelector('.stock-badge');

                if (isInventoryManaged) {
                    // Update or create stock badge
                    if (!stockBadge) {
                        const badgeDiv = document.createElement('div');
                        badgeDiv.className = 'stock-badge absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-semibold shadow-lg';
                        card.appendChild(badgeDiv);
                    }

                    const badgeDiv = card.querySelector('.stock-badge');

                    if (stockQuantity <= 0) {
                        // Update badge
                        badgeDiv.className = 'stock-badge absolute top-4 right-4 bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold shadow-lg';
                        badgeDiv.textContent = 'Out of Stock';

                        // Update button
                        button.disabled = true;
                        button.className = button.className.replace('bg-[#b0775c] text-white hover:bg-[#8f5d54]/90 transition-colors duration-300', 'bg-gray-400 text-gray-600 cursor-not-allowed');
                        button.textContent = 'Out of Stock';
                    } else if (stockQuantity <= 5) {
                        // Update badge
                        badgeDiv.className = 'stock-badge absolute top-4 right-4 bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg';
                        badgeDiv.textContent = `Only ${stockQuantity} left`;

                        // Update button
                        button.disabled = false;
                        button.className = button.className.replace('bg-gray-400 text-gray-600 cursor-not-allowed', 'bg-[#b0775c] text-white hover:bg-[#8f5d54]/90 transition-colors duration-300');
                        button.textContent = 'Add to Cart';
                    } else {
                        // Update badge
                        badgeDiv.className = 'stock-badge absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-lg';
                        badgeDiv.textContent = 'In Stock';

                        // Update button
                        button.disabled = false;
                        button.className = button.className.replace('bg-gray-400 text-gray-600 cursor-not-allowed', 'bg-[#b0775c] text-white hover:bg-[#8f5d54]/90 transition-colors duration-300');
                        button.textContent = 'Add to Cart';
                    }
                } else {
                    // Remove stock badge if inventory is no longer managed
                    if (stockBadge) {
                        stockBadge.remove();
                    }
                    button.disabled = false;
                    button.className = button.className.replace('bg-gray-400 text-gray-600 cursor-not-allowed', 'bg-[#b0775c] text-white hover:bg-[#8f5d54]/90 transition-colors duration-300');
                    button.textContent = 'Add to Cart';
                }
            });
        }
    </script>
    </x-page-layout>
