<?php

namespace App\Http\Controllers;

use App\Services\BookingWishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingCartController extends Controller
{
    public function __construct(private BookingWishlistService $service)
    {
    }

    public function index(): View
    {
        $cart = session('booking_cart', []);
        $items = collect($cart['items'] ?? [])->map(function (array $item) {
            try {
                return $this->service->present($item);
            } catch (\Throwable) {
                return null;
            }
        })->filter()->values();
        $subtotal = $items->sum('line_total');

        return view('booking.cart', compact('items', 'subtotal', 'cart'));
    }

    public function destroy(string $key): RedirectResponse
    {
        $cart = session('booking_cart', []);
        unset($cart['items'][$key]);
        if (empty($cart['items'])) {
            session()->forget('booking_cart');
        } else {
            session(['booking_cart' => $cart]);
        }

        return back()->with('success', 'Item removed from your Booking Cart.');
    }

    public function updateQuantity(Request $request, string $key): RedirectResponse
    {
        $quantity = (int) $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ])['quantity'];
        $cart = session('booking_cart', []);
        $item = $cart['items'][$key] ?? null;

        if (!is_array($item)) {
            return back()->with('error', 'That item is no longer in your Booking Cart.');
        }

        $type = (string) ($item['item_type'] ?? '');
        if ($type === 'staycation') {
            return back()->with('error', 'Room and tent quantities must be configured in the booking step.');
        }

        $model = $this->service->findActiveItem($type, (int) ($item['item_id'] ?? 0));
        $maxQuantity = $this->service->maxPurchasableQuantity($model);
        if ($quantity > $maxQuantity) {
            return back()->with('error', $maxQuantity > 0
                ? "Only {$maxQuantity} available for this item."
                : 'This item is currently out of stock.');
        }

        $selection = is_array($item['selection'] ?? null) ? $item['selection'] : [];
        $selection[$type === 'activity' ? 'guests' : 'quantity'] = $quantity;
        $cart['items'][$key]['selection'] = $this->service->normalizeSelection(
            $type,
            (int) $item['item_id'],
            $selection
        );
        session(['booking_cart' => $cart]);

        return back();
    }

    public function clear(): RedirectResponse
    {
        session()->forget('booking_cart');

        return redirect()->route('booking.cart.index')->with('success', 'Booking Cart cleared.');
    }
}
