<?php

namespace App\Http\Controllers;

use App\Models\BookingWishlistItem;
use App\Services\BookingWishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingWishlistController extends Controller
{
    public function __construct(private BookingWishlistService $service)
    {
    }

    public function index(Request $request): View
    {
        $records = $request->user()
            ? $request->user()->bookingWishlistItems()
                ->oldest('created_at')
                ->oldest('id')
                ->get()
            : collect();
        $items = $records->map(function (BookingWishlistItem $record) {
            try {
                return $this->service->present($record);
            } catch (\Throwable) {
                return null;
            }
        })->filter()->values();

        return view('booking.wishlist', [
            'items' => $items,
            'isAuthenticated' => $request->user() !== null,
        ]);
    }

    public function state(Request $request): JsonResponse
    {
        $context = $this->service->currentContext();
        $hash = $this->service->contextHash($context);
        $keys = $request->user()->bookingWishlistItems()
            ->where('context_hash', $hash)
            ->get(['item_type', 'item_id'])
            ->map(fn ($item) => $item->item_type . ':' . $item->item_id)
            ->values();

        return response()->json([
            'keys' => $keys,
            'count' => $request->user()->bookingWishlistItems()->count(),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_type' => ['required', 'string', 'in:' . implode(',', BookingWishlistService::TYPES)],
            'item_id' => ['required', 'integer', 'min:1'],
            'selection' => ['nullable', 'array'],
        ]);

        $type = $validated['item_type'];
        $id = (int) $validated['item_id'];
        $this->service->findActiveItem($type, $id);
        $context = $this->service->currentContext();
        $hash = $this->service->contextHash($context);

        $query = $request->user()->bookingWishlistItems()
            ->where('item_type', $type)
            ->where('item_id', $id)
            ->where('context_hash', $hash);

        if ($existing = $query->first()) {
            $existing->delete();
            $saved = false;
        } else {
            $selection = $this->service->normalizeSelection($type, $id, $validated['selection'] ?? null);
            $request->user()->bookingWishlistItems()->create([
                'item_type' => $type,
                'item_id' => $id,
                'context_hash' => $hash,
                'booking_context' => $context,
                'selection' => $selection,
            ]);
            $saved = true;
        }

        return response()->json([
            'saved' => $saved,
            'key' => $type . ':' . $id,
            'count' => $request->user()->bookingWishlistItems()->count(),
            'message' => $saved ? 'Saved to your booking wishlist.' : 'Removed from your booking wishlist.',
        ]);
    }

    public function destroy(Request $request, int $wishlistItem): RedirectResponse
    {
        $request->user()->bookingWishlistItems()->whereKey($wishlistItem)->firstOrFail()->delete();

        return back()->with('success', 'Item removed from your booking wishlist.');
    }

    public function removeSelected(Request $request): RedirectResponse
    {
        $ids = $request->validate(['items' => ['required', 'array'], 'items.*' => ['integer']])['items'];
        $request->user()->bookingWishlistItems()->whereIn('id', $ids)->delete();

        return back()->with('success', 'Selected items were removed.');
    }

    public function addSelectedToCart(Request $request): RedirectResponse
    {
        $ids = $request->validate(['items' => ['required', 'array'], 'items.*' => ['integer']])['items'];
        $records = $request->user()->bookingWishlistItems()->whereIn('id', $ids)->get();
        abort_if($records->isEmpty(), 422, 'Select at least one wishlist item.');

        $contextHashes = $records->pluck('context_hash')->unique();
        if ($contextHashes->count() !== 1) {
            return back()->with('error', 'Only items with the same booking dates can be added together.');
        }

        $cart = session('booking_cart', []);
        $hash = $contextHashes->first();
        if (!empty($cart['context_hash']) && $cart['context_hash'] !== $hash) {
            return back()->with('error', 'Your Booking Cart has different dates. Clear it before adding these items.');
        }

        $cart['context_hash'] = $hash;
        $cart['booking_context'] = $records->first()->booking_context;
        $cart['items'] = $cart['items'] ?? [];
        foreach ($records as $record) {
            $this->service->findActiveItem($record->item_type, $record->item_id);
            $key = $record->item_type . ':' . $record->item_id;
            $cart['items'][$key] = [
                'item_type' => $record->item_type,
                'item_id' => $record->item_id,
                'booking_context' => $record->booking_context,
                'selection' => $record->selection,
            ];
        }

        session(['booking_cart' => $cart]);

        return redirect()->route('booking.cart.index')->with('success', 'Selected items were added to your Booking Cart.');
    }
}
