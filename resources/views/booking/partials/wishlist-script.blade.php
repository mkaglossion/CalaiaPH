<script>
(() => {
    const authenticated = @json(Auth::check());
    const routes = {
        state: @json(Auth::check() ? route('booking.wishlist.state') : null),
        toggle: @json(Auth::check() ? route('booking.wishlist.toggle') : null),
    };

    const setCount = (count) => {
        document.querySelectorAll('[data-booking-wishlist-count]').forEach((badge) => {
            badge.textContent = count;
            badge.classList.toggle('hidden', Number(count) < 1);
        });
    };

    const setSaved = (key, saved) => {
        document.querySelectorAll(`[data-wishlist-key="${CSS.escape(key)}"]`).forEach((button) => {
            button.dataset.saved = saved ? 'true' : 'false';
            button.classList.toggle('bg-[#8f5d54]', saved);
            button.classList.toggle('text-white', saved);
            button.classList.toggle('bg-white/95', !saved);
            button.classList.toggle('text-[#8f5d54]', !saved);
            const icon = button.querySelector('[data-wishlist-icon]');
            icon?.classList.toggle('bi-heart-fill', saved);
            icon?.classList.toggle('bi-heart', !saved);
            button.setAttribute('aria-label', saved ? 'Remove from booking wishlist' : 'Save to booking wishlist');
        });
    };

    const showMessage = (icon, message) => {
        if (window.Swal) {
            Swal.fire({ icon, text: message, timer: icon === 'success' ? 1600 : undefined, showConfirmButton: icon !== 'success' });
        } else {
            window.alert(message);
        }
    };

    window.toggleBookingWishlist = async (itemType, itemId, selection, event) => {
        event?.stopPropagation();
        if (!authenticated) {
            if (window.Alpine?.store('modals')) {
                Alpine.store('modals').showLogin = true;
            }
            return;
        }

        const button = event?.currentTarget;
        if (button?.dataset.loading === 'true') return;
        const label = button?.querySelector('[data-wishlist-label]');
        const icon = button?.querySelector('[data-wishlist-icon]');
        const spinner = button?.querySelector('[data-wishlist-spinner]');
        if (button) {
            button.dataset.loading = 'true';
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        }
        if (label) label.textContent = button?.dataset.saved === 'true' ? 'Removing...' : 'Saving...';
        icon?.classList.add('hidden');
        spinner?.classList.remove('hidden');

        try {
            const response = await fetch(routes.toggle, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ item_type: itemType, item_id: itemId, selection: selection || null }),
            });
            const payload = await response.json();
            if (!response.ok) {
                const firstError = Object.values(payload.errors || {}).flat()[0];
                throw new Error(firstError || payload.message || 'Unable to update your wishlist.');
            }
            setSaved(payload.key, payload.saved);
            setCount(payload.count);
            showMessage('success', payload.message);
        } catch (error) {
            showMessage('error', error.message);
        } finally {
            if (button) {
                delete button.dataset.loading;
                button.disabled = false;
                button.removeAttribute('aria-busy');
            }
            if (label) label.textContent = 'Save';
            icon?.classList.remove('hidden');
            spinner?.classList.add('hidden');
        }
    };

    document.addEventListener('DOMContentLoaded', async () => {
        if (!authenticated || !routes.state || !document.querySelector('[data-booking-wishlist-button]')) return;
        try {
            const response = await fetch(routes.state, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const payload = await response.json();
            (payload.keys || []).forEach((key) => setSaved(key, true));
            setCount(payload.count || 0);
        } catch (_) {
            // The toggle endpoint still works if initial-state loading fails.
        }
    });
})();
</script>
