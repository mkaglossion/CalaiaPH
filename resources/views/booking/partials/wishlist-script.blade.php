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

    const getCount = () => {
        const badge = document.querySelector('[data-booking-wishlist-count]');
        const count = Number.parseInt(badge?.textContent || '0', 10);

        return Number.isFinite(count) ? count : 0;
    };

    const setSaved = (key, saved) => {
        document.querySelectorAll(`[data-wishlist-key="${CSS.escape(key)}"]`).forEach((button) => {
            button.dataset.saved = saved ? 'true' : 'false';
            button.classList.toggle('bg-[#8f5d54]', saved);
            button.classList.toggle('text-white', saved);
            button.classList.toggle('bg-white/95', !saved);
            button.classList.toggle('text-[#8f5d54]', !saved);
            const icon = button.querySelector('[data-wishlist-icon]');
            const label = button.querySelector('[data-wishlist-label]');
            icon?.classList.toggle('bi-heart-fill', saved);
            icon?.classList.toggle('bi-heart', !saved);
            if (label) label.textContent = saved ? 'Saved for later' : 'Save for later';
            button.setAttribute('aria-label', saved ? 'Remove from saved items' : 'Save for later');
        });
    };

    const showError = (message) => {
        if (window.Swal) {
            Swal.fire({ icon: 'error', text: message });
        } else {
            window.alert(message);
        }
    };

    const showSuccess = (saved, message) => {
        if (!window.Swal) return;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: saved ? 'Added to Wishlist' : 'Removed from Wishlist',
            text: message,
            timer: 1400,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    };

    const readPayload = async (response) => {
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            return response.json();
        }

        return { message: response.ok ? '' : 'Unable to update your wishlist. Please refresh and try again.' };
    };

    let interactionVersion = 0;

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
        const key = `${itemType}:${itemId}`;
        const wasSaved = button?.dataset.saved === 'true';
        const optimisticSaved = !wasSaved;
        const previousCount = getCount();
        const optimisticCount = Math.max(0, previousCount + (optimisticSaved ? 1 : -1));

        interactionVersion += 1;
        setSaved(key, optimisticSaved);
        setCount(optimisticCount);

        if (button) {
            button.dataset.loading = 'true';
            button.setAttribute('aria-busy', 'true');
        }

        try {
            const response = await fetch(routes.toggle, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ item_type: itemType, item_id: itemId, selection: selection || null }),
            });
            const payload = await readPayload(response);
            if (!response.ok || typeof payload.saved !== 'boolean' || !payload.key) {
                const firstError = Object.values(payload.errors || {}).flat()[0];
                throw new Error(firstError || payload.message || 'Unable to update your wishlist.');
            }
            setSaved(payload.key, payload.saved);
            setCount(payload.count);
            showSuccess(payload.saved, payload.message);
        } catch (error) {
            setSaved(key, wasSaved);
            setCount(previousCount);
            showError(error.message);
        } finally {
            if (button) {
                delete button.dataset.loading;
                button.removeAttribute('aria-busy');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', async () => {
        if (!authenticated || !routes.state || !document.querySelector('[data-booking-wishlist-button]')) return;
        const versionAtStart = interactionVersion;
        try {
            const response = await fetch(routes.state, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const payload = await response.json();
            if (versionAtStart !== interactionVersion) return;
            (payload.keys || []).forEach((key) => setSaved(key, true));
            setCount(payload.count || 0);
        } catch (_) {
            // The toggle endpoint still works if initial-state loading fails.
        }
    });
})();
</script>
