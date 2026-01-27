<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Receipt;
use App\Models\Product;
use App\Events\ProductStockUpdated;

class CartController extends Controller
{
    /**
     * Build a normalized cart summary array.
     */
    protected function buildCartSummary(array $cart): array
    {
        $items = [];
        $total = 0;
        $count = 0;

        foreach ($cart as $id => $it) {
            $qty   = (int)($it['quantity'] ?? 0);
            $basePrice = (float)($it['price'] ?? 0);

            // Check if it's a dining item (SET MENUS)
            $isDining = str_contains(strtolower($it['title'] ?? ''), 'set menus');

            if ($isDining) {
                // Tiered pricing for dining based on pax
                if ($qty <= 4) {
                    $price = 5980.00;
                } elseif ($qty <= 6) {
                    $price = 8790.00;
                } else {
                    $price = 12900.00;
                }
                $subtotal = $price; // Price is for the set, not per person
            } else {
                $price = $basePrice;
                $subtotal = $price * $qty;
            }

            $items[] = [
                'id'       => $id,
                'title'    => $it['title'] ?? null,
                'price'    => $price,
                'image'    => $it['image'] ?? asset('assets/images/carousel/activities-1.jpg'),
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'isDining' => $isDining,
            ];

            $total += $subtotal;
            $count += $qty;
        }

        return [
            'items' => $items,
            'total' => $total,
            'count' => $count,
        ];
    }

    /**
     * Helper: return JSON summary (with optional extra keys).
     */
    protected function jsonSummary(array $cart, array $extra = [])
    {
        return response()->json(array_merge($this->buildCartSummary($cart), $extra));
    }

    public function index(Request $request)
    {
        $rawCart = session('cart', []);
        $cart    = is_array($rawCart) ? $rawCart : [];
        $summary = $this->buildCartSummary($cart);

        // For header dropdown / AJAX
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json($summary);
        }

        return view('cart', [
            'cart'      => $cart,
            'cartCount' => $summary['count'],
            'cartTotal' => $summary['total'],
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'id'       => 'required|string',
            'title'    => 'required|string',
            'price'    => 'required|numeric|min:0',
            'image'    => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'isDining' => 'nullable',
        ]);

        // Check inventory for products
        $product = Product::find($data['id']);
        if ($product && $product->is_inventory_managed) {
            $currentCartQuantity = 0;
            $cart = session()->get('cart', []);
            if (is_array($cart)) {
                $cartId = md5($data['id']);
                if (isset($cart[$cartId])) {
                    $currentCartQuantity = $cart[$cartId]['quantity'];
                }
            }

            if ($currentCartQuantity + $data['quantity'] > $product->stock_quantity) {
                return redirect()->back()->with('error', 'Insufficient stock. Only ' . $product->stock_quantity . ' items available.');
            }
        }

        $cart = session()->get('cart', []);
        if (!is_array($cart)) {
            $cart = [];
        }

        $id = md5($data['id']);
        if (isset($cart[$id])) {
            // update existing
            $cart[$id]['quantity'] += $data['quantity'];
            $cart[$id]['price']     = $data['price']; // just in case price changed
            if (!empty($data['image'])) {
                $cart[$id]['image'] = $data['image'];
            }
            $cart[$id]['isDining'] = ($data['isDining'] ?? '0') == '1';
        } else {
            // new item
            $cart[$id] = [
                'product_id' => $data['id'],
                'title'    => $data['title'],
                'price'    => $data['price'],
                'image'    => $data['image'] ?? '',
                'quantity' => $data['quantity'],
                'isDining' => ($data['isDining'] ?? '0') == '1',
            ];
        }

        session()->put('cart', $cart);

        // If AJAX / JSON (e.g. future enhancement), return summary
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return $this->jsonSummary($cart, [
                'success' => true,
                'message' => 'Activity added to cart.',
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
        if (!is_array($cart)) {
            $cart = [];
        }

        $id = urldecode($id);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $data['quantity'];

            // Recalculate price for dining items based on new quantity
            if (isset($cart[$id]['isDining']) && $cart[$id]['isDining']) {
                $qty = $data['quantity'];
                if ($qty <= 4) {
                    $cart[$id]['price'] = 5980.00;
                } elseif ($qty <= 6) {
                    $cart[$id]['price'] = 8790.00;
                } else {
                    $cart[$id]['price'] = 12900.00;
                }
            }

            session()->put('cart', $cart);
        } else {
            $old_key = str_replace('-', '%2F', $id);
            if (isset($cart[$old_key])) {
                $cart[$old_key]['quantity'] = $data['quantity'];

                // Recalculate price
                if (isset($cart[$old_key]['isDining']) && $cart[$old_key]['isDining']) {
                    $qty = $data['quantity'];
                    if ($qty <= 4) {
                        $cart[$old_key]['price'] = 5980.00;
                    } elseif ($qty <= 6) {
                        $cart[$old_key]['price'] = 8790.00;
                    } else {
                        $cart[$old_key]['price'] = 12900.00;
                    }
                }

                session()->put('cart', $cart);
            }
        }

        // Support both normal form & AJAX
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return $this->jsonSummary($cart, [
                'success' => true,
                'message' => 'Quantity updated.',
            ]);
        }

        return redirect()->back()->with('success', 'Quantity updated.');
    }

    public function remove(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if (!is_array($cart)) {
            $cart = [];
        }

        $id = urldecode($id);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        } else {
            $old_key = str_replace('-', '%2F', $id);
            if (isset($cart[$old_key])) {
                unset($cart[$old_key]);
                session()->put('cart', $cart);
            }
        }

        // IMPORTANT: for dropdown X button (AJAX)
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return $this->jsonSummary($cart, [
                'success' => true,
                'message' => 'Item removed from cart.',
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    public function checkoutForm()
    {
        $rawCart = session('cart', []);
        $cart    = is_array($rawCart) ? $rawCart : [];
        $summary = $this->buildCartSummary($cart);

        return view('checkout', [
            'cart'      => $cart,
            'cartCount' => $summary['count'],
            'cartTotal' => $summary['total'],
        ]);
    }

    public function checkoutSubmit(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'email'          => 'required|email',
            'phone'          => 'required|string',
            'booking_date'   => 'required|date|after:today',
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer',
        ]);

        $rawCart = session('cart', []);
        $cart    = is_array($rawCart) ? $rawCart : [];
        $summary = $this->buildCartSummary($cart);
        $cartTotal = $summary['total'];

        // Add payment details
        $data['transaction_id'] = 'TXN-' . strtoupper(uniqid());
        $data['payment_date'] = now()->toDateTimeString();

        // Reduce inventory for products
        foreach ($cart as $item) {
            if (isset($item['product_id']) && !$item['isDining']) {
                $product = Product::find($item['product_id']);
                if ($product && $product->is_inventory_managed) {
                    $product->decrement('stock_quantity', $item['quantity']);
                    event(new ProductStockUpdated($product));
                }
            }
        }

        // Send receipt email
        Mail::to($data['email'])->send(new Receipt($cart, $cartTotal, $data));

        // TODO: save order + process payment here

        session()->forget('cart');

        return redirect()
            ->route('checkout.confirmation')
            ->with('success', 'Order placed successfully!');
    }
}