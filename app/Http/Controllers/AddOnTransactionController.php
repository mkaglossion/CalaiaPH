<?php

namespace App\Http\Controllers;

use App\Contracts\AddOnCheckoutGateway;
use App\Models\Activity;
use App\Models\AddOnTransaction;
use App\Models\Booking;
use App\Models\Food;
use App\Services\AddOnPaymentService;
use App\Services\AddOnExpirationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AddOnTransactionController extends Controller
{
    public function create(Booking $booking)
    {
        Gate::authorize('view', $booking);
        $this->ensureBookingAcceptsAddOns($booking);

        $foods = Food::query()
            ->with('swiperImages')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->where(function ($query) {
                $query->whereNull('day_visit_only')->orWhere('day_visit_only', false);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $activities = Activity::query()
            ->with('swiperImages')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('profile.add-ons.create', compact('booking', 'foods', 'activities'));
    }

    public function store(Request $request, Booking $booking, AddOnCheckoutGateway $checkoutGateway)
    {
        Gate::authorize('view', $booking);
        $this->ensureBookingAcceptsAddOns($booking);

        $validated = $request->validate([
            'foods' => ['nullable', 'array'],
            'foods.*' => ['nullable', 'integer', 'min:0', 'max:99'],
            'activities' => ['nullable', 'array'],
            'activities.*' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $items = $this->buildLineItems($validated);
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Please select at least one meal or activity.',
            ]);
        }

        $totalAmount = collect($items)->sum('total_price');
        $addOn = DB::transaction(fn () => AddOnTransaction::create([
            'booking_id' => $booking->id,
            'type' => collect($items)->pluck('type')->unique()->count() > 1
                ? 'mixed'
                : ($items[0]['type'] ?? 'mixed'),
            'items' => $items,
            'total_amount' => $totalAmount,
            'status' => AddOnTransaction::STATUS_PENDING,
            'payment_status' => 'pending',
            'payment_method' => 'paymongo',
            'expires_at' => now()->addMinutes(AddOnTransaction::PAYMENT_WINDOW_MINUTES),
        ]));

        try {
            $checkout = $checkoutGateway->create($addOn, $booking);

            $addOn->update([
                'paymongo_checkout_id' => $checkout->id,
                'checkout_url' => $checkout->checkout_url,
                'expires_at' => now()->addMinutes(AddOnTransaction::PAYMENT_WINDOW_MINUTES),
            ]);

            return redirect()->away($checkout->checkout_url);
        } catch (\Throwable $e) {
            app(AddOnPaymentService::class)->markFailed($addOn);
            report($e);

            return back()->withInput()->with('error', 'Unable to start the add-on payment. Please try again.');
        }
    }

    public function resumePayment(
        Booking $booking,
        AddOnTransaction $addOnTransaction,
        AddOnExpirationService $expirationService,
        AddOnPaymentService $paymentService
    ) {
        Gate::authorize('view', $booking);

        $addOn = $booking->addOnTransactions()
            ->whereKey($addOnTransaction->getKey())
            ->firstOrFail();

        if ($addOn->payment_status === 'paid') {
            return redirect()->route('profile.bookings.show', $booking)
                ->withFragment('add-on-' . $addOn->id)
                ->with('success', 'This add-on payment is already confirmed.');
        }

        if ($expirationService->expireIfStale($addOn)) {
            return redirect()->route('profile.bookings.show', $booking)
                ->with('warning', 'The 60-minute add-on payment window has expired. Please create a new add-on transaction.');
        }

        $addOn->refresh();
        if (!$addOn->canResumePayment()) {
            return redirect()->route('profile.bookings.show', $booking)
                ->with('warning', 'This add-on payment can no longer be resumed. Please create a new transaction.');
        }

        $checkoutUrl = $paymentService->recoverCheckoutUrl($addOn);
        if (!$checkoutUrl) {
            return redirect()->route('profile.bookings.show', $booking)
                ->with('warning', 'The payment page is temporarily unavailable. Please try again.');
        }

        return redirect()->away($checkoutUrl);
    }

    public function paymentSuccess(Request $request, AddOnPaymentService $paymentService)
    {
        $validated = $request->validate([
            'add_on' => ['required', 'integer', 'exists:add_on_transactions,id'],
            'checkout_id' => ['required', 'string', 'max:255'],
        ]);

        $addOn = AddOnTransaction::with('booking')->findOrFail($validated['add_on']);
        Gate::authorize('view', $addOn->booking);

        if (!$paymentService->confirmCheckout($addOn, $validated['checkout_id'])) {
            return redirect()->route('profile.bookings.show', $addOn->booking)
                ->with('error', 'The add-on payment could not be verified yet. Please refresh after a moment.');
        }

        return redirect()->route('profile.bookings.show', $addOn->booking)
            ->withFragment('add-on-' . $addOn->id)
            ->with('success', 'Your add-on payment was confirmed.');
    }

    public function paymentCancelled(Request $request, AddOnPaymentService $paymentService)
    {
        $validated = $request->validate([
            'add_on' => ['required', 'integer', 'exists:add_on_transactions,id'],
            'checkout_id' => ['nullable', 'string', 'max:255'],
        ]);

        $addOn = AddOnTransaction::with('booking')->findOrFail($validated['add_on']);
        Gate::authorize('view', $addOn->booking);

        if ($addOn->payment_status !== 'paid') {
            $paymentService->markFailed($addOn, $validated['checkout_id'] ?? null);
        }

        return redirect()->route('profile.bookings.show', $addOn->booking)
            ->with('warning', 'The add-on payment was cancelled. Your original booking was not changed.');
    }

    private function ensureBookingAcceptsAddOns(Booking $booking): void
    {
        abort_unless(
            $booking->isStaycationFamilyBooking()
            && $booking->payment_status === 'paid'
            && $booking->status === 'confirmed'
            && (!$booking->display_checkout_date || $booking->display_checkout_date->isToday() || $booking->display_checkout_date->isFuture()),
            422,
            'Add-ons are available only for active, paid staycation bookings.'
        );
    }

    private function buildLineItems(array $validated): array
    {
        $foodQuantities = collect($validated['foods'] ?? [])->map(fn ($quantity) => (int) $quantity)->filter();
        $activityQuantities = collect($validated['activities'] ?? [])->map(fn ($quantity) => (int) $quantity)->filter();
        $items = [];

        $foods = Food::query()
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->whereIn('id', $foodQuantities->keys())
            ->get()
            ->keyBy('id');

        foreach ($foodQuantities as $foodId => $quantity) {
            $food = $foods->get($foodId);
            if (!$food) {
                continue;
            }

            if ($food->is_inventory_managed && $food->stock_quantity < $quantity) {
                throw ValidationException::withMessages([
                    "foods.{$foodId}" => "Only {$food->stock_quantity} serving(s) of {$food->name} are available.",
                ]);
            }

            $unitPrice = (float) $food->price;
            $items[] = [
                'type' => 'meal',
                'item_id' => $food->id,
                'name' => $food->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
            ];
        }

        $activities = Activity::query()
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->whereIn('id', $activityQuantities->keys())
            ->get()
            ->keyBy('id');

        foreach ($activityQuantities as $activityId => $quantity) {
            $activity = $activities->get($activityId);
            if (!$activity) {
                continue;
            }

            $unitPrice = (float) $activity->price;
            $items[] = [
                'type' => 'activity',
                'item_id' => $activity->id,
                'name' => $activity->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
            ];
        }

        return $items;
    }
}
