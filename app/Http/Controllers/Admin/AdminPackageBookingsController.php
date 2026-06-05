<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\PackageBooking;
use App\Models\PackageInventory;
use App\Models\AdminSystemAlert;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AdminPackageBookingsController extends Controller
{
    public function show($id)
    {
        $booking = PackageBooking::findOrFail($id);

        return view('admin.bookings.show_package', compact('booking'));
    }

    public function confirm($id)
    {
        $booking = PackageBooking::findOrFail($id);
        $oldStatus = $booking->status;
        $booking->status = 'confirmed';
        $booking->save();

        AdminActivityLog::log(
            'package_booking_status_update',
            "Confirmed package booking {$booking->booking_reference} from {$oldStatus} to confirmed",
            $booking,
            ['old_status' => $oldStatus, 'new_status' => 'confirmed']
        );

        // Log and create admin alert
        Log::info('Package booking confirmed by admin', ['id' => $booking->id, 'reference' => $booking->booking_reference]);
        AdminSystemAlert::create([
            'type' => 'package_booking',
            'severity' => 'info',
            'message' => "Package booking {$booking->booking_reference} confirmed by admin",
            'data' => ['booking_id' => $booking->id]
        ]);

        return response()->json(['success' => true, 'message' => 'Package booking confirmed']);
    }

    public function cancel($id)
    {
        $booking = PackageBooking::findOrFail($id);
        $oldStatus = $booking->status;
        $booking->status = 'cancelled';
        $booking->save();

        // Try to release inventory group for this booking date
        try {
            $inv = PackageInventory::where('package_id', $booking->package_id)
                ->whereDate('date', $booking->booking_date)
                ->first();

            if ($inv) {
                $groups = $booking->groups_booked ?? 1;
                if ($booking->inventory_booked) {
                    $released = $inv->releaseGroups($groups);
                } else {
                    $released = false;
                }
                if ($released) {
                    Log::info('Released package inventory groups on booking cancel', ['booking_id' => $booking->id, 'inventory_id' => $inv->id]);
                    AdminSystemAlert::create([
                        'type' => 'inventory',
                        'severity' => 'info',
                        'message' => "Released 1 group for package inventory (package_id={$booking->package_id}, date={$booking->booking_date}) due to booking cancel",
                        'data' => ['booking_id' => $booking->id, 'inventory_id' => $inv->id]
                    ]);
                    // clear inventory_booked flag
                    $booking->inventory_booked = false;
                    $booking->save();
                } else {
                    Log::warning('Failed to release package inventory groups on booking cancel', ['booking_id' => $booking->id, 'inventory_id' => $inv->id]);
                    AdminSystemAlert::create([
                        'type' => 'inventory',
                        'severity' => 'warning',
                        'message' => "Failed to release package inventory for booking {$booking->booking_reference}",
                        'data' => ['booking_id' => $booking->id]
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error releasing package inventory on cancel: '.$e->getMessage(), ['booking_id' => $booking->id]);
            AdminSystemAlert::create([
                'type' => 'inventory',
                'severity' => 'critical',
                'message' => "Error releasing package inventory for booking {$booking->booking_reference}",
                'data' => ['booking_id' => $booking->id, 'error' => $e->getMessage()]
            ]);
        }

        // Log and create admin alert for cancellation
        Log::info('Package booking cancelled by admin', ['id' => $booking->id, 'reference' => $booking->booking_reference]);
        AdminActivityLog::log(
            'package_booking_status_update',
            "Cancelled package booking {$booking->booking_reference} from {$oldStatus} to cancelled",
            $booking,
            ['old_status' => $oldStatus, 'new_status' => 'cancelled']
        );
        AdminSystemAlert::create([
            'type' => 'package_booking',
            'severity' => 'info',
            'message' => "Package booking {$booking->booking_reference} cancelled by admin",
            'data' => ['booking_id' => $booking->id]
        ]);

        return response()->json(['success' => true, 'message' => 'Package booking cancelled']);
    }

    public function recordPayment(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'required|integer|exists:package_bookings,id',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric',
            'notes' => 'nullable|string'
        ]);

        $booking = PackageBooking::findOrFail($data['booking_id']);
        $oldPaymentStatus = $booking->payment_status;
        $booking->payment_status = 'paid';
        $booking->payment_reference = $data['notes'] ?? $booking->payment_reference;
        $booking->total_amount = $data['amount'];
        $booking->status = 'confirmed';
        $booking->save();

        // Try to book inventory group for this booking date
        try {
            $inv = PackageInventory::where('package_id', $booking->package_id)
                ->whereDate('date', $booking->booking_date)
                ->first();

            if ($inv) {
                $groups = $booking->groups_booked ?? 1;
                // prevent double booking
                if ($booking->inventory_booked) {
                    $booked = false;
                } else {
                    $booked = $inv->bookGroups($groups);
                }
                if ($booked) {
                    Log::info('Booked package inventory groups on payment record', ['booking_id' => $booking->id, 'inventory_id' => $inv->id]);
                    AdminSystemAlert::create([
                        'type' => 'inventory',
                        'severity' => 'info',
                        'message' => "Booked 1 group for package inventory (package_id={$booking->package_id}, date={$booking->booking_date}) due to payment",
                        'data' => ['booking_id' => $booking->id, 'inventory_id' => $inv->id]
                    ]);
                } else {
                    Log::warning('Failed to book package inventory groups on payment record', ['booking_id' => $booking->id, 'inventory_id' => $inv->id]);
                    AdminSystemAlert::create([
                        'type' => 'inventory',
                        'severity' => 'warning',
                        'message' => "Failed to book package inventory for booking {$booking->booking_reference}",
                        'data' => ['booking_id' => $booking->id]
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error booking package inventory on payment: '.$e->getMessage(), ['booking_id' => $booking->id]);
            AdminSystemAlert::create([
                'type' => 'inventory',
                'severity' => 'critical',
                'message' => "Error booking package inventory for booking {$booking->booking_reference}",
                'data' => ['booking_id' => $booking->id, 'error' => $e->getMessage()]
            ]);
        }

        // Mark inventory_booked if booking succeeded
        if (isset($booked) && $booked) {
            $booking->inventory_booked = true;
            $booking->save();
        }

        // Log and admin alert for payment
        Log::info('Package booking payment recorded by admin', ['id' => $booking->id, 'reference' => $booking->booking_reference, 'amount' => $booking->total_amount]);
        AdminActivityLog::log(
            'package_booking_payment_recorded',
            "Recorded payment for package booking {$booking->booking_reference} via {$data['payment_method']}",
            $booking,
            [
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => 'paid',
                'payment_method' => $data['payment_method'],
                'amount' => (float) $booking->total_amount,
                'notes' => $data['notes'] ?? null,
            ]
        );
        AdminSystemAlert::create([
            'type' => 'package_booking',
            'severity' => 'info',
            'message' => "Payment recorded for package booking {$booking->booking_reference}",
            'data' => ['booking_id' => $booking->id, 'amount' => $booking->total_amount]
        ]);

        return response()->json(['success' => true, 'message' => 'Payment recorded for package booking']);
    }
}
