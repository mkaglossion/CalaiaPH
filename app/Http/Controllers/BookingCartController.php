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
        })->filter()->sortBy(fn (array $item) => match ($item['item_type']) {
            'staycation' => 1,
            'food', 'food_package' => 2,
            'activity', 'activity_package' => 3,
            default => 4,
        })->values();
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

    public function updateStaycation(Request $request, string $key): RedirectResponse
    {
        $cart = session('booking_cart', []);
        $item = $cart['items'][$key] ?? null;

        if (!is_array($item) || ($item['item_type'] ?? null) !== 'staycation') {
            return back()->with('error', 'That room or tent is no longer in your Booking Cart.');
        }

        $maxPets = $this->service->maxPetsPerRoom();
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['required', 'integer', 'min:0', 'max:20'],
            'pets' => ['required', 'string', 'in:yes,no'],
            'num_pets' => ['nullable', 'integer', 'min:0', 'max:' . $maxPets],
            'pet_agreement' => ['nullable', 'boolean'],
        ]);

        $staycation = $this->service->findActiveItem('staycation', (int) ($item['item_id'] ?? 0));
        $context = is_array($item['booking_context'] ?? null)
            ? $item['booking_context']
            : ($cart['booking_context'] ?? []);
        $maxQuantity = $this->service->maxStaycationQuantity($staycation, $context);
        if ((int) $validated['quantity'] > $maxQuantity) {
            return back()->with('error', $maxQuantity > 0
                ? "Only {$maxQuantity} room(s) available for the selected dates."
                : 'This room is unavailable for the selected dates.');
        }

        $limits = $this->service->staycationGuestLimits($staycation);
        $adults = (int) $validated['adults'];
        $children = (int) $validated['children'];
        if (
            $adults > $limits['adults']
            || $children > $limits['children']
            || ($adults + $children) > $limits['occupancy']
        ) {
            return back()->with('error', "Guest limit exceeded for {$staycation->name}. Maximum {$limits['occupancy']} guest(s) per room.");
        }

        $hasPets = $validated['pets'] === 'yes';
        $petAgreement = (bool) ($validated['pet_agreement'] ?? false);
        $numPets = $hasPets ? (int) ($validated['num_pets'] ?? 0) : 0;
        if ($hasPets && !$petAgreement) {
            return back()->with('error', 'Please agree to the Pet Policy before adding pets.');
        }
        if ($hasPets && $numPets < 1) {
            return back()->with('error', "Please set the number of pets (1-{$maxPets}) per room.");
        }

        $selection = array_merge(
            is_array($item['selection'] ?? null) ? $item['selection'] : [],
            [
                'room_id' => (int) $item['item_id'],
                'quantity' => (int) $validated['quantity'],
                'adults' => $adults,
                'children' => $children,
                'pets' => $validated['pets'],
                'pet_agreement' => $petAgreement,
                'num_pets' => $numPets,
            ]
        );

        if ($this->service->isSpecialStaycation($staycation)) {
            $selection['extra_mattress'] = (int) $validated['quantity'];
        }

        $cart['items'][$key]['selection'] = $this->service->normalizeSelection(
            'staycation',
            (int) $item['item_id'],
            $selection
        );
        session(['booking_cart' => $cart]);

        return back()->with('success', 'Room details updated.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget('booking_cart');

        return redirect()->route('booking.cart.index')->with('success', 'Booking Cart cleared.');
    }
}
