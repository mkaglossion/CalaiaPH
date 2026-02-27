<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ShopOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscountVerificationController extends Controller
{
    /**
     * Display a listing of discount verifications
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        // Query bookings with discounts (using discount_verification_status field)
        $bookingsQuery = Booking::where('discount_type', '!=', 'none')
            ->whereNotNull('discount_type');

        // Query shop orders with discounts (using discount_status field)
        // Only show paid orders - don't show pending/unpaid orders
        $shopOrdersQuery = ShopOrder::where('discount_type', '!=', 'none')
            ->whereNotNull('discount_type')
            ->where('payment_status', 'paid');


        // Apply status filter
        if ($status !== 'all') {
            $bookingsQuery->where('discount_verification_status', $status);
            $shopOrdersQuery->where('discount_status', $status);
        }

        // Apply search filter
        if ($search) {
            $bookingsQuery->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('discount_id_number', 'like', "%{$search}%");
            });

            $shopOrdersQuery->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('discount_id_number', 'like', "%{$search}%")
                  ->orWhere('order_reference', 'like', "%{$search}%");
            });
        }

        $bookings = $bookingsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'bookings_page');
        $shopOrders = $shopOrdersQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'orders_page');

        // Count pending
        $pendingBookings = Booking::where('discount_type', '!=', 'none')
            ->where('discount_verification_status', 'pending')
            ->count();

        $pendingShopOrders = ShopOrder::where('discount_type', '!=', 'none')
            ->where('discount_status', 'pending')
            ->where('payment_status', 'paid')
            ->count();


        return view('admin.discount-verifications.index', compact(
            'bookings', 'shopOrders', 'status', 'search',
            'pendingBookings', 'pendingShopOrders'
        ));
    }

    /**
     * Display the specified booking verification
     */
    public function show($id)
    {
        $booking = Booking::findOrFail($id);
        
        // Normalize booking fields to match expected view format
        $booking->discount_status = $booking->discount_verification_status;
        $booking->rejection_reason = $booking->discount_rejection_reason;
        $booking->verified_at = $booking->discount_verified_at;
        $booking->verified_by = $booking->discount_verified_by;
        $booking->claimed_at = $booking->discount_claimed_at_checkin ? now() : null;
        $booking->id_image_path = $booking->discount_id_image;
        $booking->booking_reference = $booking->transaction_id;
        $booking->original_total = $booking->subtotal + $booking->discount_amount;
        $booking->final_total = $booking->total_amount;
        
        return view('admin.discount-verifications.show', [
            'item' => $booking,
            'type' => 'booking'
        ]);
    }

    /**
     * Display the specified shop order verification
     */
    public function showShopOrder(ShopOrder $shopOrder)
    {
        // Normalize shop order fields to match expected view format
        $shopOrder->booking_reference = $shopOrder->order_reference;
        $shopOrder->original_total = $shopOrder->original_total;
        $shopOrder->final_total = $shopOrder->final_total;
        $shopOrder->checkin_date = $shopOrder->pickup_date;
        $shopOrder->booking_type = 'shop';
        // Ensure id_image_path is set for the view
        $shopOrder->id_image_path = $shopOrder->id_image_path;
        
        return view('admin.discount-verifications.show', [
            'item' => $shopOrder,
            'type' => 'shop_order'
        ]);
    }


    /**
     * Approve discount verification
     */
    public function approve(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        $item = $model::findOrFail($id);

        $statusField = $type === 'booking' ? 'discount_verification_status' : 'discount_status';
        
        if ($item->$statusField !== 'pending') {
            return back()->with('error', 'This discount has already been processed.');
        }

        $updateData = [
            $statusField => 'verified',
        ];

        if ($type === 'booking') {
            $updateData['discount_verified_at'] = now();
            $updateData['discount_verified_by'] = Auth::user()->name ?? Auth::user()->email;
            $updateData['discount_rejection_reason'] = null;
        } else {
            $updateData['verified_at'] = now();
            $updateData['verified_by'] = Auth::user()->name ?? Auth::user()->email;
            $updateData['rejection_reason'] = null;
        }

        $item->update($updateData);

        return back()->with('success', 'Discount approved successfully. Customer can claim at ' . ($type === 'booking' ? 'check-in' : 'pickup') . '.');
    }

    /**
     * Reject discount verification
     */
    public function reject(Request $request, $type, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $model = $this->getModel($type);
        $item = $model::findOrFail($id);

        $statusField = $type === 'booking' ? 'discount_verification_status' : 'discount_status';

        if ($item->$statusField !== 'pending') {
            return back()->with('error', 'This discount has already been processed.');
        }

        $updateData = [
            $statusField => 'rejected',
        ];

        if ($type === 'booking') {
            $updateData['discount_rejection_reason'] = $request->reason;
            $updateData['discount_verified_at'] = now();
            $updateData['discount_verified_by'] = Auth::user()->name ?? Auth::user()->email;
        } else {
            $updateData['rejection_reason'] = $request->reason;
            $updateData['verified_at'] = now();
            $updateData['verified_by'] = Auth::user()->name ?? Auth::user()->email;
        }

        $item->update($updateData);

        return back()->with('success', 'Discount rejected. Customer must pay the difference.');
    }

    /**
     * Mark discount as claimed at check-in/pickup
     */
    public function claim(Request $request, $type, $id)
    {
        $model = $this->getModel($type);
        $item = $model::findOrFail($id);

        $statusField = $type === 'booking' ? 'discount_verification_status' : 'discount_status';

        if ($item->$statusField !== 'verified') {
            return back()->with('error', 'Discount must be verified before claiming.');
        }

        $updateData = [
            $statusField => 'claimed',
        ];

        if ($type === 'booking') {
            $updateData['discount_claimed_at_checkin'] = true;
        } else {
            $updateData['claimed_at'] = now();
        }

        $item->update($updateData);

        return back()->with('success', 'Discount marked as claimed at ' . ($type === 'booking' ? 'check-in' : 'pickup') . '.');
    }

    /**
     * Record payment for rejected discount
     */
    public function recordPayment(Request $request, $type, $id)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,gcash,card,other',
            'reference' => 'nullable|string|max:100',
        ]);

        $model = $this->getModel($type);
        $item = $model::findOrFail($id);

        $statusField = $type === 'booking' ? 'discount_verification_status' : 'discount_status';

        if ($item->$statusField !== 'rejected') {
            return back()->with('error', 'Payment can only be recorded for rejected discounts.');
        }

        // For bookings, store in discount_difference_paid fields
        if ($type === 'booking') {
            $item->update([
                'discount_difference_paid' => $request->amount_paid,
                'discount_difference_paid_at' => now(),
                'discount_verification_status' => 'payment_recorded',
            ]);
        } else {
            // For shop orders, store in metadata
            $paymentDetails = [
                'amount_paid' => $request->amount_paid,
                'payment_method' => $request->payment_method,
                'reference' => $request->reference,
                'recorded_at' => now(),
                'recorded_by' => Auth::user()->name ?? Auth::user()->email,
            ];
            
            $item->update([
                'payment_details' => json_encode($paymentDetails),
                'discount_status' => 'payment_recorded',
            ]);
        }

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Bulk approve pending verifications
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'type' => 'required|in:bookings,shop_orders',
        ]);

        $model = $this->getModel($request->type === 'bookings' ? 'booking' : 'shop_order');
        $isBooking = $request->type === 'bookings';
        $statusField = $isBooking ? 'discount_verification_status' : 'discount_status';
        
        $count = 0;
        foreach ($request->ids as $id) {
            $item = $model::find($id);
            if ($item && $item->$statusField === 'pending') {
                $updateData = [
                    $statusField => 'verified',
                ];

                if ($isBooking) {
                    $updateData['discount_verified_at'] = now();
                    $updateData['discount_verified_by'] = Auth::user()->name ?? Auth::user()->email;
                } else {
                    $updateData['verified_at'] = now();
                    $updateData['verified_by'] = Auth::user()->name ?? Auth::user()->email;
                }

                $item->update($updateData);
                $count++;
            }
        }

        return back()->with('success', "{$count} discount(s) approved successfully.");
    }

    /**
     * Get pending count for dashboard
     */
    public function pendingCount()
    {
        $bookingCount = Booking::where('discount_type', '!=', 'none')
            ->where('discount_verification_status', 'pending')
            ->count();

        $shopOrderCount = ShopOrder::where('discount_type', '!=', 'none')
            ->where('discount_status', 'pending')
            ->count();

        return response()->json([
            'bookings' => $bookingCount,
            'shop_orders' => $shopOrderCount,
            'total' => $bookingCount + $shopOrderCount,
        ]);
    }

    /**
     * Get model class based on type
     */
    private function getModel($type)
    {
        return match($type) {
            'booking' => Booking::class,
            'shop_order' => ShopOrder::class,
            default => throw new \InvalidArgumentException("Invalid type: {$type}"),
        };
    }
}
