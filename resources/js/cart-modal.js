document.addEventListener('DOMContentLoaded', function () {
  console.log('Cart modal JS loaded');
  const forms = document.querySelectorAll('form.ajax-add-to-cart');
  const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
  const modal = document.getElementById('cart-modal');
  console.log('Modal element', modal);
  const itemsEl = document.getElementById('cart-modal-items');
  const totalEl = document.getElementById('cart-modal-total');
  const closeBtn = document.getElementById('cart-modal-close');

  console.log('Found buttons', document.querySelectorAll('[data-js-cart-button]'));

  function formatCurrency(v) {
    try {
      return '₱' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    } catch (e) {
      return '₱' + v;
    }
  }

  function showModalAtRect(rect) {
    if (!modal) return;
    // width already controlled by CSS; compute left/top to place dropdown below button
    const modalRect = modal.getBoundingClientRect();
    const width = Math.min(400, window.innerWidth - 24);
    modal.style.width = width + 'px';

    let left = Math.round(rect.right - width);
    if (left < 12) left = 12;
    if (left + width > window.innerWidth - 12) left = window.innerWidth - width - 12;

    const top = Math.round(rect.bottom + 10);

    modal.style.left = left + 'px';
    modal.style.top = top + 'px';
    modal.style.display = 'block';
  }

  function hideModal() {
    if (!modal) return;
    modal.style.display = 'none';
  }

  if (closeBtn) closeBtn.addEventListener('click', hideModal);

  // Clicking the header cart button should fetch current cart and open dropdown
  document.querySelectorAll('[data-js-cart-button]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      console.log('Cart button clicked');
      e.preventDefault();
      const href = btn.getAttribute('href') || '/cart';
      fetch(href, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
      })
      .then(async res => {
        console.log('Fetch response', res);
        const data = await res.json().catch(() => null);
        console.log('Fetched data', data);
        if (!res.ok) throw new Error('Could not fetch cart');

        // populate
        const contentEl = document.getElementById('cart-modal-content');
        if (!contentEl) return;

        contentEl.innerHTML = '';

        if (data.count === 0) {
          // Empty cart
          const emptyDiv = document.createElement('div');
          emptyDiv.className = 'text-center py-12';
          emptyDiv.innerHTML = `
            <i class="bi bi-cart-x text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-600 mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-6">Add some activities to get started!</p>
            <a href="/activities" class="bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition">Browse Activities</a>
          `;
          contentEl.appendChild(emptyDiv);
        } else {
          // Cart items
          const itemsDiv = document.createElement('div');
          itemsDiv.className = 'space-y-4';
          (data.items || []).forEach(item => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-4 border-b pb-4';

            const info = document.createElement('div');
            info.className = 'flex-1';
            const title = document.createElement('h3');
            title.className = 'font-semibold text-lg';
            title.textContent = item.title || '';
            const price = document.createElement('p');
            price.className = 'text-gray-600';
            price.textContent = `${formatCurrency(item.price)} each`;

            info.appendChild(title);
            info.appendChild(price);

            const qty = document.createElement('div');
            qty.className = 'flex items-center gap-2';
            qty.innerHTML = `<span class="text-gray-600">Qty: ${item.quantity}</span>`;

            const subtotal = document.createElement('div');
            subtotal.className = 'text-right';
            subtotal.innerHTML = `<p class="font-semibold text-lg">${formatCurrency(item.price * item.quantity)}</p>`;

            row.appendChild(info);
            row.appendChild(qty);
            row.appendChild(subtotal);

            itemsDiv.appendChild(row);
          });

          const totalDiv = document.createElement('div');
          totalDiv.className = 'mt-6 border-t pt-6';
          totalDiv.innerHTML = `
            <div class="flex justify-between items-center text-xl font-bold">
              <span>Total:</span>
              <span>${formatCurrency(data.total || 0)}</span>
            </div>
          `;

          const buttonsDiv = document.createElement('div');
          buttonsDiv.className = 'mt-6 flex gap-4';
          buttonsDiv.innerHTML = `
            <a href="/activities" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">Continue Shopping</a>
            <a href="/checkout" class="bg-[#8f5d54] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#8f5d54]/90 transition">Proceed to Checkout</a>
          `;

          contentEl.appendChild(itemsDiv);
          contentEl.appendChild(totalDiv);
          contentEl.appendChild(buttonsDiv);
        }
        const rect = btn.getBoundingClientRect();
        showModalAtRect(rect);
        document.querySelectorAll('[data-js-cart-count]').forEach(b => b.textContent = data.count || '');

        // Add click outside to close
        const closeModal = () => {
          hideModal();
          document.removeEventListener('click', closeOnClick);
        };
        const closeOnClick = (e) => {
          if (!modal.contains(e.target) && !btn.contains(e.target)) {
            closeModal();
          }
        };
        document.addEventListener('click', closeOnClick);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
      })
      .catch(err => {
        console.error(err);
        alert('Could not load cart');
      });
    });
  });

  forms.forEach(form => {
    form.addEventListener('submit', function (e) {
      // Allow normal submit when JS disabled, but intercept when JS is enabled
      e.preventDefault();

      const url = form.action;
      const formData = new FormData(form);

      fetch(url, {
        method: form.method || 'POST',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf || ''
        },
        body: formData,
        credentials: 'same-origin'
      })
      .then(async res => {
        const data = await res.json().catch(() => null);
        if (!res.ok) {
          console.error('Add to cart failed', data);
          alert((data && data.message) || 'Could not add item to cart.');
          return;
        }

        // Populate modal
        if (data && data.items) {
          itemsEl.innerHTML = '';
          data.items.slice(0,5).forEach(item => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 py-3 border-b last:border-b-0';

            const imgBox = document.createElement('div');
            imgBox.className = 'w-12 h-12 bg-gray-100 rounded overflow-hidden flex items-center justify-center';
            // Placeholder image - could be replaced with real image URL if available
            const img = document.createElement('img');
            img.src = '/assets/images/carousel/go-cart2.jpg';
            img.alt = item.title || '';
            img.className = 'w-full h-full object-cover';
            imgBox.appendChild(img);

            const info = document.createElement('div');
            info.className = 'flex-1';
            const title = document.createElement('div');
            title.className = 'text-sm font-medium';
            title.textContent = item.title || '';
            const meta = document.createElement('div');
            meta.className = 'text-xs text-gray-500';
            meta.innerHTML = `${formatCurrency(item.price)} <strong class="ml-1">x ${item.quantity}</strong>`;

            info.appendChild(title);
            info.appendChild(meta);

            row.appendChild(imgBox);
            row.appendChild(info);

            itemsEl.appendChild(row);
          });

          totalEl.textContent = formatCurrency(data.total || 0);

          // Position modal near header cart button
          const btn = document.querySelector('[data-js-cart-button]');
          if (btn) {
            const rect = btn.getBoundingClientRect();
            showModalAtRect(rect);
          } else {
            // fallback center
            modal.style.display = 'block';
          }

          // Update cart count badges across site
          document.querySelectorAll('[data-js-cart-count]').forEach(b => b.textContent = data.count || '');
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error adding to cart.');
      });
    });
  });
});
