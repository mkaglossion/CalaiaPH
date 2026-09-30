<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\Receipt;
use App\Helpers\ImageHelper;
use App\Models\Product;
use App\Models\Region;
use App\Models\Province;
use App\Models\City;
use App\Models\Barangay;
use App\Models\CustomerAddress;
use App\Events\ProductStockUpdated;
use App\Services\VatService;
use App\Services\PaymongoWebhookService;
use App\Models\ShopOrder;

class CartController extends Controller
{
    private const SERVICE_REGION_PSGC = '0400000000'; // CALABARZON
    private const SERVICE_PROVINCE_PSGC = '0401000000'; // Batangas
    private const SERVICE_CITY_PSGC = '0401007000'; // Calaca

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
            if ($qty < 1) {
                continue;
            }

            $product = $this->resolveCartProduct($id, $it);

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
                if (!$product || !$product->is_active) {
                    continue;
                }

                $price = (float) $product->price;
                $subtotal = $price * $qty;
            }

            $items[] = [
                'id'       => $id,
                'cart_key' => $id,
                'product_id' => $product?->id,
                'title'    => $product?->title ?? ($it['title'] ?? null),
                'price'    => $price,
                'image'    => $product ? $this->productImageUrl($product) : ($it['image'] ?? ImageHelper::placeholder()),
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'type'     => $product?->category ?? ($it['type'] ?? null),
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

    protected function resolveCartProduct(string|int $cartKey, array $item): ?Product
    {
        $productId = $item['product_id'] ?? null;

        if ($productId === null && is_numeric($cartKey)) {
            $productId = $cartKey;
        }

        return $productId ? Product::find($productId) : null;
    }

    protected function productImageUrl(Product $product): string
    {
        if (empty($product->image)) {
            return ImageHelper::placeholder();
        }

        if (str_contains($product->image, 'res.cloudinary.com')) {
            return $product->image;
        }

        return asset('storage/' . $product->image);
    }

    protected function firstCartAvailabilityError(array $cart): ?string
    {
        foreach ($cart as $id => $item) {
            $product = $this->resolveCartProduct($id, is_array($item) ? $item : []);
            $quantity = (int) ($item['quantity'] ?? 0);

            if (!$product || !$product->is_active) {
                return 'One or more cart items are no longer available.';
            }

            if (!$product->is_inventory_managed) {
                continue;
            }

            if ($quantity < 1 || $quantity > $product->stock_quantity) {
                return 'Insufficient stock. Only ' . $product->stock_quantity . ' item(s) available for ' . $product->title . '.';
            }
        }

        return null;
    }

    protected function normalizeProductCartItem(Product $product, int $quantity, array $data = []): array
    {
        $isDining = str_contains(strtolower($product->title), 'set menus')
            || (($data['isDining'] ?? '0') == '1');

        return array_filter([
            'product_id' => $product->id,
            'title' => $product->title,
            'price' => (float) $product->price,
            'image' => $this->productImageUrl($product),
            'quantity' => $quantity,
            'isDining' => $isDining,
            'type' => $product->category,
            'meal_period' => $data['meal_period'] ?? null,
            'reservation_date' => $data['reservation_date'] ?? null,
            'reservation_time' => $data['reservation_time'] ?? null,
            'adults' => $data['adults'] ?? null,
            'children' => $data['children'] ?? null,
            'salutation' => $data['salutation'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'special_requests' => $data['special_requests'] ?? null,
        ], fn ($value) => $value !== null);
    }

    /**
     * Helper: return JSON summary (with optional extra keys).
     */
    protected function jsonSummary(array $cart, array $extra = [])
    {
        return response()->json(array_merge($this->buildCartSummary($cart), $extra));
    }

    /**
     * Build a printable full delivery address from structured checkout fields.
     */
    protected function formatDeliveryAddress(array $data): string
    {
        $region = Region::find($data['region_id'] ?? null);
        $province = !empty($data['province_id']) ? Province::find($data['province_id']) : null;
        $city = City::find($data['city_id'] ?? null);
        $barangay = Barangay::find($data['barangay_id'] ?? null);

        $line1 = trim(implode(' ', array_filter([
            $data['street_address'] ?? null,
            $data['subdivision'] ?? null,
        ])));

        $line2 = trim(implode(', ', array_filter([
            optional($barangay)->name,
            optional($city)->name,
            optional($province)->name,
            optional($region)->name,
        ])));

        $line3 = trim(implode(', ', array_filter([
            !empty($data['zip_code']) ? ('ZIP ' . $data['zip_code']) : null,
            !empty($data['landmark']) ? ('Landmark: ' . $data['landmark']) : null,
        ])));

        return trim(implode("\n", array_filter([$line1, $line2, $line3])));
    }

    /**
     * Resolve fixed service area: Calaca, Batangas, CALABARZON.
     */
    protected function resolveServiceArea(): array
    {
        $region = Region::where('psgc_code', self::SERVICE_REGION_PSGC)->first();
        $province = Province::where('psgc_code', self::SERVICE_PROVINCE_PSGC)->first();
        $city = City::where('psgc_code', self::SERVICE_CITY_PSGC)->first();

        return [$region, $province, $city];
    }

    public function index(Request $request)
    {
        $rawCart = session('cart', []);
        $cart    = is_array($rawCart) ? $rawCart : [];
        $summary = $this->buildCartSummary($cart);
        $displayCart = collect($summary['items'])
            ->mapWithKeys(fn (array $item) => [(string) $item['cart_key'] => $item])
            ->all();

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
            'cart'      => $displayCart,
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
            'id'       => 'required|integer|exists:products,id',
            'title'    => 'nullable|string',
            'price'    => 'nullable|numeric|min:0',
            'image'    => 'nullable|string',
            'quantity' => 'required|integer|min:1|max:100',
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

        $product = Product::whereKey($data['id'])->where('is_active', true)->first();
        if (!$product) {
            return redirect()->back()->with('error', 'This product is no longer available.');
        }

        // Check inventory for products
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
            $cart[$id] = $this->normalizeProductCartItem($product, (int) $cart[$id]['quantity'], array_merge($cart[$id], $data));
        } else {
            // new item
            $cart[$id] = $this->normalizeProductCartItem($product, (int) $data['quantity'], $data);
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

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $cart = session()->get('cart', []);
        if (!is_array($cart)) {
            $cart = [];
        }

        $id = urldecode($id);

        if (isset($cart[$id])) {
            $product = $this->resolveCartProduct($id, $cart[$id]);
            if (!$product || !$product->is_active) {
                unset($cart[$id]);
                session()->put('cart', $cart);

                return redirect()->back()->with('error', 'This product is no longer available.');
            }
            if ($product->is_inventory_managed && (int) $data['quantity'] > $product->stock_quantity) {
                return redirect()->back()->with('error', 'Insufficient stock. Only ' . $product->stock_quantity . ' items available.');
            }

            $cart[$id]['quantity'] = $data['quantity'];
            $cart[$id] = $this->normalizeProductCartItem($product, (int) $data['quantity'], $cart[$id]);

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
                $product = $this->resolveCartProduct($old_key, $cart[$old_key]);
                if (!$product || !$product->is_active) {
                    unset($cart[$old_key]);
                    session()->put('cart', $cart);

                    return redirect()->back()->with('error', 'This product is no longer available.');
                }
                if ($product->is_inventory_managed && (int) $data['quantity'] > $product->stock_quantity) {
                    return redirect()->back()->with('error', 'Insufficient stock. Only ' . $product->stock_quantity . ' items available.');
                }

                $cart[$old_key]['quantity'] = $data['quantity'];
                $cart[$old_key] = $this->normalizeProductCartItem($product, (int) $data['quantity'], $cart[$old_key]);

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

    public function remove(Request $request, string $id)
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
        $displayCart = collect($summary['items'])
            ->mapWithKeys(fn (array $item) => [(string) $item['cart_key'] => $item])
            ->all();
        [$region, $province, $city] = $this->resolveServiceArea();

        $barangays = collect();
        if ($city) {
            $barangays = Barangay::query()
                ->where('city_id', $city->id)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('checkout', [
            'cart'      => $displayCart,
            'cartCount' => $summary['count'],
            'cartTotal' => $summary['total'],
            'vatAmount' => $summary['vat_amount'],
            'finalTotal' => $summary['final_total'],
            'fixedRegionId' => $region?->id,
            'fixedProvinceId' => $province?->id,
            'fixedCityId' => $city?->id,
            'fixedLocationLabel' => 'Calaca, Batangas, CALABARZON, Philippines',
            'barangays' => $barangays,
        ]);
    }

    public function checkoutSubmit(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'email'          => 'required|email|max:191',
            'phone'          => 'required|string|max:30',
            'delivery_option' => 'required|in:pickup,delivery',
            'address'        => 'nullable|string|max:1000',
            'barangay_id'     => 'required_if:delivery_option,delivery|nullable|exists:barangays,id',
            'street_address'  => 'required_if:delivery_option,delivery|nullable|string|max:255',
            'subdivision'     => 'nullable|string|max:255',
            'zip_code'        => 'required_if:delivery_option,delivery|nullable|string|max:10',
            'landmark'        => 'nullable|string|max:255',
            'booking_date'   => 'required|date|after_or_equal:today',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $deliveryAddress = null;
        $data['payment_method'] = 'paymongo';
        if (($data['delivery_option'] ?? null) === 'delivery') {
            [$region, $province, $city] = $this->resolveServiceArea();
            if (!$region || !$province || !$city) {
                return back()->withErrors([
                    'delivery_option' => 'Delivery area is temporarily unavailable. Please contact support.',
                ])->withInput();
            }

            $barangay = Barangay::find($data['barangay_id']);

            if ($barangay && (int) $barangay->city_id !== (int) $city->id) {
                return back()->withErrors([
                    'barangay_id' => 'Selected barangay is outside Calaca service area.',
                ])->withInput();
            }

            $data['region_id'] = $region->id;
            $data['province_id'] = $province->id;
            $data['city_id'] = $city->id;

            $deliveryAddress = $this->formatDeliveryAddress($data);
        }

        $rawCart = session('cart', []);
        $cart    = is_array($rawCart) ? $rawCart : [];
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $availabilityError = $this->firstCartAvailabilityError($cart);
        if ($availabilityError) {
            return redirect()->route('cart.index')->with('error', $availabilityError);
        }
        
        $summary = $this->buildCartSummary($cart);
        if (empty($summary['items'])) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        
        // Use final total (with VAT) for payment
        $cartTotal = $summary['final_total'];

        // Generate order reference
        $orderReference = 'SHOP-' . strtoupper(uniqid());

        // Create shop order in database
        try {
            $shopOrder = DB::transaction(function () use ($data, $deliveryAddress, $orderReference, $summary) {
                $shopOrder = ShopOrder::create([
                    'order_reference' => $orderReference,
                    'customer_name' => $data['name'],
                    'customer_email' => $data['email'],
                    'customer_phone' => $data['phone'],
                    'cart_items' => $summary['items'],
                    'original_total' => $summary['total'],
                    'vat_amount' => $summary['vat_amount'],
                    'final_total' => $summary['final_total'],
                    'delivery_option' => $data['delivery_option'],
                    'delivery_address' => $deliveryAddress,
                    'pickup_date' => $data['booking_date'],
                    'payment_method' => $data['payment_method'],
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'payment_status' => 'pending',
                    'order_status' => 'pending',
                    'special_requests' => $data['special_requests'] ?? null,
                ]);

                // Reserve stock using locked product rows so concurrent checkouts cannot go negative.
                foreach ($summary['items'] as $item) {
                    if (!empty($item['isDining'])) {
                        continue;
                    }

                    $product = Product::query()->lockForUpdate()->find($item['product_id'] ?? null);
                    if (!$product || !$product->is_active) {
                        throw new \RuntimeException('One or more cart items are no longer available.');
                    }
                    if ($product->is_inventory_managed) {
                        $quantity = (int) ($item['quantity'] ?? 0);
                        if ($quantity < 1 || $quantity > $product->stock_quantity) {
                            throw new \RuntimeException('Insufficient stock. Only ' . $product->stock_quantity . ' item(s) available for ' . $product->title . '.');
                        }

                        $product->decrement('stock_quantity', $quantity);
                        event(new ProductStockUpdated($product));
                    }
                }

                return $shopOrder;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        // Store normalized delivery address for reporting/reuse.
        if (($data['delivery_option'] ?? null) === 'delivery') {
            CustomerAddress::create([
                'user_id' => Auth::id(),
                'region_id' => $data['region_id'],
                'province_id' => $data['province_id'] ?? null,
                'city_id' => $data['city_id'],
                'barangay_id' => $data['barangay_id'],
                'street_address' => $data['street_address'],
                'subdivision' => $data['subdivision'] ?? null,
                'zip_code' => $data['zip_code'],
                'landmark' => $data['landmark'] ?? null,
            ]);
        }

        // Add payment details for email
        $data['transaction_id'] = $shopOrder->transaction_id;
        $data['payment_date'] = now()->toDateTimeString();
        $data['vat_amount'] = $summary['vat_amount'];
        $data['original_total'] = $summary['total'];
        $data['order_reference'] = $orderReference;

        // Redirect to PayMongo for payment
        try {
            // Create PayMongo checkout session
            $checkoutResponse = $this->createPaymongoCheckout(
                (int)($cartTotal * 100), // Convert to centavos
                $data['email'],
                $data['name'],
                $data['transaction_id'],
                $summary
            );

            if ($checkoutResponse && isset($checkoutResponse['checkout_url'])) {
                // Update shop order with PayMongo checkout ID
                $shopOrder->update([
                    'paymongo_checkout_id' => $checkoutResponse['id'] ?? null,
                ]);

                // Store order data in session for later confirmation
                session([
                    'current_shop_order_id' => $shopOrder->id,
                    'pending_order' => [
                        'transaction_id' => $data['transaction_id'],
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'delivery_option' => $data['delivery_option'],
                        'address' => $deliveryAddress,
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
            return redirect()->back()->with('error', 'Payment could not be initialized. Please try again.');
        }
    }

    /**
     * Create PayMongo checkout session
     */
    protected function createPaymongoCheckout(int $amount, string $email, string $name, string $transactionId, array $summary): array
    {
        $client = new \GuzzleHttp\Client([
            'connect_timeout' => config('services.paymongo.connect_timeout'),
            'timeout' => config('services.paymongo.timeout'),
        ]);
        $apiKey = config('services.paymongo.secret_key');

        // Offer QR Ph + Card + E-Wallets on hosted checkout.
        // Note: methods still depend on what is activated in your PayMongo account.
        $paymentMethodTypes = ['qrph', 'card', 'gcash', 'grab_pay', 'paymaya'];

        $lineItems = array_map(function (array $item) {
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

        $checkoutId = $body['data']['id'] ?? null;
        $checkoutUrl = $body['data']['attributes']['checkout_url'] ?? null;

        // PayMongo may return 200 or 201 depending on API behavior/version.
        if (in_array($statusCode, [200, 201], true) && $checkoutId && $checkoutUrl) {
            return [
                'id' => $checkoutId,
                'checkout_url' => $checkoutUrl,
            ];
        }

        throw new \Exception('Failed to create PayMongo checkout session.');
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
            $client = new \GuzzleHttp\Client([
                'connect_timeout' => config('services.paymongo.connect_timeout'),
                'timeout' => config('services.paymongo.timeout'),
            ]);
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
            ]);

            if ($statusCode !== 200) {
                throw new \Exception('Failed to verify payment with PayMongo');
            }

            $checkoutData = $data['data']['attributes'] ?? [];
            $payment = is_array($checkoutData['payments'][0] ?? null)
                ? $checkoutData['payments'][0]
                : [];
            $paymentAttributes = is_array($payment['attributes'] ?? null)
                ? $payment['attributes']
                : [];
            $paymentStatus = $paymentAttributes['status']
                ?? ($checkoutData['payment_status'] ?? ($checkoutData['payment_intent']['attributes']['status'] ?? 'unknown'));
            
            // Check if payment was successful
            if ($paymentStatus !== 'succeeded' && $paymentStatus !== 'paid') {
                Log::warning('Payment not successful', [
                    'checkout_id' => $checkoutId,
                    'status' => $paymentStatus
                ]);
                return redirect()->route('checkout.failed');
            }

            // Bind the verified provider checkout to its own server-side order.
            $shopOrder = ShopOrder::where('paymongo_checkout_id', $checkoutId)->first();
            
            if (!$shopOrder) {
                Log::error('Shop order not found for checkout', [
                    'checkout_id' => $checkoutId,
                ]);
                return redirect()->route('checkout.failed')->with('error', 'Order not found. Please contact support.');
            }

            $paidAmountCentavos = $paymentAttributes['amount'] ?? ($checkoutData['amount'] ?? null);
            if (!is_numeric($paidAmountCentavos)) {
                Log::warning('PayMongo checkout response did not include a paid amount', [
                    'checkout_id' => $checkoutId,
                    'shop_order_id' => $shopOrder->id,
                ]);
                return redirect()->route('checkout.failed')->with('error', 'Payment amount could not be verified.');
            }

            $paymentReference = $payment['id'] ?? data_get($checkoutData, 'payment_intent.id');
            if (!PaymongoWebhookService::markShopOrderPaidFromVerifiedCheckout(
                $checkoutId,
                $paymentReference,
                (int) $paidAmountCentavos
            )) {
                return redirect()->route('checkout.failed')->with('error', 'Payment amount did not match the order.');
            }

            $shopOrder->refresh();

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
                $emailData
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
