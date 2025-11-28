<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Receipt;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = session('cart', []);
        $cartCount = 0;
        $cartTotal = 0;

        if (is_array($cart)) {
            foreach ($cart as $item) {
                $qty = $item['quantity'] ?? 0;
                $cartCount += $qty;
                $cartTotal += ($item['price'] ?? 0) * $qty;
            }
        }

        // Return JSON for AJAX requests (used by header dropdown)
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            $items = [];
            foreach ($cart as $id => $it) {
                $qty = $it['quantity'] ?? 0;
                $price = $it['price'] ?? 0;
                $items[] = [
                    'id' => $id,
                    'title' => $it['title'] ?? null,
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $price * $qty,
                ];
            }

            return response()->json([
                'items' => $items,
                'total' => $cartTotal,
                'count' => $cartCount,
            ]);
        }

        return view('cart', compact('cart', 'cartCount', 'cartTotal'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'id'       => 'required|string',
            'title'    => 'required|string',
            'price'    => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$data['id']])) {
            $cart[$data['id']]['quantity'] += $data['quantity'];
            $cart[$data['id']]['price'] = $data['price']; // Update price in case it changed
        } else {
            $cart[$data['id']] = [
                'title'    => $data['title'],
                'price'    => $data['price'],
                'quantity' => $data['quantity'],
            ];
        }

        session()->put('cart', $cart);

        // If the request expects JSON (AJAX), return a cart summary
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            $cartItems = [];
            $cartTotal = 0;
            $cartCount = 0;

            foreach ($cart as $id => $it) {
                $qty = $it['quantity'] ?? 0;
                $price = $it['price'] ?? 0;
                $subtotal = $price * $qty;
                $cartItems[] = [
                    'id' => $id,
                    'title' => $it['title'] ?? null,
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ];
                $cartTotal += $subtotal;
                $cartCount += $qty;
            }

            return response()->json([
                'success' => true,
                'message' => 'Activity added to cart.',
                'items' => $cartItems,
                'total' => $cartTotal,
                'count' => $cartCount,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Activity added to cart.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $data['quantity'];
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Quantity updated.');
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    public function checkoutForm()
    {
        $cart = session('cart', []);
        $cartCount = 0;
        $cartTotal = 0;

        if (is_array($cart)) {
            foreach ($cart as $item) {
                $qty = $item['quantity'] ?? 0;
                $cartCount += $qty;
                $cartTotal += ($item['price'] ?? 0) * $qty;
            }
        }

        return view('checkout', compact('cart', 'cartCount', 'cartTotal'));
    }

    public function checkoutSubmit(Request $request)
    {
        // Validate and process checkout
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'booking_date' => 'required|date|after:today',
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer',
        ]);

        // Get cart data for email
        $cart = session('cart', []);
        $cartTotal = 0;
        if (is_array($cart)) {
            foreach ($cart as $item) {
                $qty = $item['quantity'] ?? 0;
                $cartTotal += ($item['price'] ?? 0) * $qty;
            }
        }

        // Send receipt email
        Mail::to($data['email'])->send(new Receipt($cart, $cartTotal, $data));

        // Here you would process the payment and save the order
        // For now, just clear the cart and redirect to confirmation

        session()->forget('cart');

        return redirect()->route('checkout.confirmation')->with('success', 'Order placed successfully!');
    }
}