<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\Receipt;
use App\Models\Product;
use App\Events\ProductStockUpdated;
use App\Services\VatService;
use App\Models\ShopOrder;

class CartController extends Controller
{
    /**
     * Build a normalized cart summary array with VAT calculations.
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
                // Tiered pricing for dining based on persons
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

        // Calculate VAT
        $vatService = app(VatService::class);
        $vatBreakdown = $vatService->generateBreakdown($total, true);
        $vatAmount = $vatBreakdown['vat_amount'];
        $finalTotal = $vatBreakdown['gross_amount'];

        return [
            'items' => $items,
            'total' => $total,
            'count' => $count,
            'vat_amount' => $vatAmount,
            'final_total' => $finalTotal,
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

        // Ensure a persistent cart reference exists for display and reconciliation
        $reference = session('cart_reference');
        if (empty($reference)) {
            try {
                $reference = 'CALAIA-' . strtoupper(bin2hex(random_bytes(6)));
            } catch (\Exception $e) {
                $reference = 'CALAIA-' . strtoupper(substr(uniqid(), 0, 12));
            }
            session(['cart_reference' => $reference]);
        }

        return view('cart', [
            'cart'      => $cart,
            'cartCount' => $summary['count'],
            'cartTotal' => $summary['total'],
            'cartReference' => $reference,
            'vatAmount' => $summary['vat_amount'],
            'finalTotal' => $summary['final_total'],
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
            'meal_period' => 'nullable|string',
            'reservation_date' => 'nullable|string',
            'reservation_time' => 'nullable|string',
            'adults' => 'nullable|integer',
            'children' => 'nullable|integer',
            'salutation' => 'nullable|string',
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|string',
            'special_requests' => 'nullable|string',
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
            // Store dining reservation details
            if ($cart[$id]['isDining']) {
                $cart[$id]['meal_period'] = $data['meal_period'] ?? '';
                $cart[$id]['reservation_date'] = $data['reservation_date'] ?? '';
                $cart[$id]['reservation_time'] = $data['reservation_time'] ?? '';
                $cart[$id]['adults'] = $data['adults'] ?? 0;
                $cart[$id]['children'] = $data['children'] ?? 0;
                $cart[$id]['salutation'] = $data['salutation'] ?? '';
                $cart[$id]['first_name'] = $data['first_name'] ?? '';
                $cart[$id]['last_name'] = $data['last_name'] ?? '';
                $cart[$id]['phone'] = $data['phone'] ?? '';
                $cart[$id]['email'] = $data['email'] ?? '';
                $cart[$id]['special_requests'] = $data['special_requests'] ?? '';
            }
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
            // Store dining reservation details for new items
            if (($data['isDining'] ?? '0') == '1') {
                $cart[$id]['meal_period'] = $data['meal_period'] ?? '';
                $cart[$id]['reservation_date'] = $data['reservation_date'] ?? '';
                $cart[$id]['reservation_time'] = $data['reservation_time'] ?? '';
                $cart[$id]['adults'] = $data['adults'] ?? 0;
                $cart[$id]['children'] = $data['children'] ?? 0;
                $cart[$id]['salutation'] = $data['salutation'] ?? '';
                $cart[$id]['first_name'] = $data['first_name'] ?? '';
                $cart[$id]['last_name'] = $data['last_name'] ?? '';
                $cart[$id]['phone'] = $data['phone'] ?? '';
                $cart[$id]['email'] = $data['email'] ?? '';
                $cart[$id]['special_requests'] = $data['special_requests'] ?? '';
            }
        }

        session()->put('cart', $cart);

        // If AJAX / JSON (e.g. future enhancement), return summary
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return $this->jsonSummary($cart, [
                'success' => true,
                'message' => 'Product added to cart.',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
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
            'vatAmount' => $summary['vat_amount'],
            'finalTotal' => $summary['final_total'],
        ]);
    }

    public function checkoutSubmit(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'email'          => 'required|email',
            'phone'          => 'required|string',
            'delivery_option' => 'required|in:pickup,delivery',
            'address'        => 'required_if:delivery_option,delivery|nullable|string',
            'booking_date'   => 'required|date|after_or_equal:today',
            'payment_method' => 'required|in:card,qr_code',
            'special_requests' => 'nullable|string',
        ]);

        $rawCart = session('cart', []);
        $cart    = is_array($rawCart) ? $rawCart : [];
        
        $summary = $this->buildCartSummary($cart);
        
        // Use final total (with VAT) for payment
        $cartTotal = $summary['final_total'];

        // Generate order reference
        $orderReference = 'SHOP-' . strtoupper(uniqid());

        // Create shop order in database
        $shopOrder = ShopOrder::create([
            'order_reference' => $orderReference,
            'customer_name' => $data['name'],
            'customer_email' => $data['email'],
            'customer_phone' => $data['phone'],
            'cart_items' => $cart,
            'original_total' => $summary['total'],
            'vat_amount' => $summary['vat_amount'],
            'final_total' => $summary['final_total'],
            'delivery_option' => $data['delivery_option'],
            'delivery_address' => $data['address'] ?? null,
            'pickup_date' => $data['booking_date'],
            'payment_method' => $data['payment_method'],
            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'special_requests' => $data['special_requests'] ?? null,
        ]);

        // Add payment details for email
        $data['transaction_id'] = $shopOrder->transaction_id;
        $data['payment_date'] = now()->toDateTimeString();
        $data['vat_amount'] = $summary['vat_amount'];
        $data['original_total'] = $summary['total'];
        $data['order_reference'] = $orderReference;

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

        // Redirect to PayMongo for payment
        try {
            // Create PayMongo checkout session
            $checkoutResponse = $this->createPaymongoCheckout(
                (int)($cartTotal * 100), // Convert to centavos
                $data['email'],
                $data['name'],
                $data['transaction_id'],
                $data['payment_method'],
                $summary
            );

            if ($checkoutResponse && isset($checkoutResponse['checkout_url'])) {
                // Update shop order with PayMongo checkout ID
                $shopOrder->update([
                    'paymongo_checkout_id' => $checkoutResponse['id'] ?? null,
                ]);

                // Store order data in session for later confirmation
                session([
                    'pending_order' => [
                        'transaction_id' => $data['transaction_id'],
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'delivery_option' => $data['delivery_option'],
                        'address' => $data['address'] ?? null,
                        'booking_date' => $data['booking_date'],
                        'payment_method' => $data['payment_method'],
                        'cart' => $cart,
                        'total' => $cartTotal,
                        'checkout_id' => $checkoutResponse['id'] ?? null,
                        'shop_order_id' => $shopOrder->id,
                    ]
                ]);

                // Redirect to PayMongo checkout
                return redirect($checkoutResponse['checkout_url']);
            } else {
                Log::error('Invalid PayMongo checkout response', ['response' => $checkoutResponse]);
                return redirect()->back()->with('error', 'Failed to create payment. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Checkout Payment Error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Payment error: ' . $e->getMessage());
        }
    }

    /**
     * Create PayMongo checkout session
     */
    protected function createPaymongoCheckout($amount, $email, $name, $transactionId, $paymentMethod, $summary)
    {
        $client = new \GuzzleHttp\Client();
        $apiKey = config('services.paymongo.secret_key');

        $paymentMethodTypes = $paymentMethod === 'qr_code' ? ['gcash'] : ['card'];

        $lineItems = array_map(function ($item) {
            return [
                'name' => $item['title'],
                'amount' => (int)($item['subtotal'] * 100),
                'quantity' => $item['quantity'],
                'currency' => 'PHP',
            ];
        }, $summary['items']);

        $data = [
            'data' => [
                'attributes' => [
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'payment_method_types' => $paymentMethodTypes,
                    'billing' => [
                        'email' => $email,
                        'name' => $name,
                    ],
                    'description' => 'Shop Order Payment for ' . $transactionId,
                    'line_items' => $lineItems,
                    'reference_number' => $transactionId,
                    'success_url' => route('checkout.success'),
                    'cancel_url' => route('checkout.failed'),
                ],
            ],
        ];

        $response = $client->post('https://api.paymongo.com/v1/checkout_sessions', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);

        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        if ($statusCode === 201) {
            return [
                'id' => $body['data']['id'],
                'checkout_url' => $body['data']['attributes']['checkout_url'],
            ];
        } else {
            throw new \Exception('Failed to create PayMongo checkout session: ' . json_encode($body));
        }
    }

    /**
     * Handle successful checkout payment from PayMongo
     */
    public function checkoutSuccess(Request $request)
    {
        $checkoutId = $request->query('checkout_id');
        
        if (!$checkoutId) {
            Log::error('Checkout success called without checkout_id');
            return redirect()->route('checkout.failed');
        }

        try {
            // Verify payment with PayMongo
            $client = new \GuzzleHttp\Client();
            $apiKey = config('services.paymongo.secret_key');
            
            $response = $client->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}", [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
                    'Accept' => 'application/json',
                ],
                'timeout' => 30
            ]);

            $statusCode = $response->getStatusCode();
            $data = json_decode($response->getBody()->getContents(), true);
            
            Log::info('PayMongo checkout verification response', [
                'status' => $statusCode,
                'checkout_id' => $checkoutId,
                'data' => $data
            ]);

            if ($statusCode !== 200) {
                throw new \Exception('Failed to verify payment with PayMongo');
            }

            $checkoutData = $data['data']['attributes'] ?? [];
            $paymentStatus = $checkoutData['payment_intent']['attributes']['status'] ?? 'unknown';
            
            // Check if payment was successful
            if ($paymentStatus !== 'succeeded' && $paymentStatus !== 'paid') {
                Log::warning('Payment not successful', [
                    'checkout_id' => $checkoutId,
                    'status' => $paymentStatus
                ]);
                return redirect()->route('checkout.failed');
            }

            // Get the shop order from session or database
            $shopOrderId = session('current_shop_order_id');
            $shopOrder = null;
            
            if ($shopOrderId) {
                $shopOrder = ShopOrder::find($shopOrderId);
            }
            
            // If not found by session, try to find by PayMongo checkout ID
            if (!$shopOrder) {
                $shopOrder = ShopOrder::where('paymongo_checkout_id', $checkoutId)->first();
            }
            
            if (!$shopOrder) {
                Log::error('Shop order not found for checkout', [
                    'checkout_id' => $checkoutId,
                    'session_order_id' => $shopOrderId
                ]);
                return redirect()->route('checkout.failed')->with('error', 'Order not found. Please contact support.');
            }

            // Update order status to paid
            $shopOrder->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'paid_at' => now(),
            ]);

            // Build summary for email
            $cart = $shopOrder->cart_items;
            $summary = [
                'items' => [],
                'total' => $shopOrder->original_total,
                'count' => collect($cart)->sum('quantity'),
                'is_vat_exempt' => false,
                'vat_amount' => $shopOrder->vat_amount,
                'final_total' => $shopOrder->final_total,
            ];
            
            // Build items array from cart
            foreach ($cart as $id => $item) {
                $summary['items'][] = [
                    'id' => $id,
                    'title' => $item['title'] ?? 'Unknown',
                    'price' => $item['price'] ?? 0,
                    'quantity' => $item['quantity'] ?? 1,
                    'subtotal' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1),
                ];
            }

            // Prepare data for receipt email
            $emailData = [
                'name' => $shopOrder->customer_name,
                'email' => $shopOrder->customer_email,
                'phone' => $shopOrder->customer_phone,
                'delivery_option' => $shopOrder->delivery_option,
                'address' => $shopOrder->delivery_address,
                'booking_date' => $shopOrder->pickup_date,
                'payment_method' => $shopOrder->payment_method,
                'transaction_id' => $shopOrder->transaction_id,
                'payment_date' => $shopOrder->paid_at,
                'vat_amount' => $shopOrder->vat_amount,
                'original_total' => $shopOrder->original_total,
                'order_reference' => $shopOrder->order_reference,
            ];

            // Send receipt email ONLY after confirmed payment
            Mail::to($shopOrder->customer_email)->send(new Receipt(
                $cart, 
                $shopOrder->final_total, 
                $emailData, 
                $summary
            ));

            Log::info('Receipt email sent after successful payment', [
                'order_id' => $shopOrder->id,
                'order_reference' => $shopOrder->order_reference,
                'email' => $shopOrder->customer_email
            ]);

            // Clear cart and session
            session()->forget('cart');
            session()->forget('cart_reference');
            session()->forget('current_shop_order_id');
            session()->forget('pending_order');

            // Redirect to confirmation page
            return redirect()->route('checkout.confirmation')->with('success', 'Payment successful! A receipt has been sent to your email.');

        } catch (\Exception $e) {
            Log::error('Checkout success verification failed: ' . $e->getMessage(), [
                'checkout_id' => $checkoutId,
                'exception' => $e
            ]);
            return redirect()->route('checkout.failed')->with('error', 'Payment verification failed. Please contact support.');
        }
    }
}
