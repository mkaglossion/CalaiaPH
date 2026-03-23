<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTimeline;
use App\Models\DiningReservation;
use App\Services\BookingStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReservationMonitorController extends Controller
{
    /**
     * Show the reservation monitoring dashboard
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status', 'all');
        $perPage = 25; // Items per page - increased to show more
        $page = $request->input('page', 1);

        // Get recent bookings
        $bookingsQuery = Booking::query();
        
        if ($statusFilter !== 'all') {
            $bookingsQuery->where('status', $statusFilter);
        }

        $bookings = $bookingsQuery
            ->select('id', 'transaction_id', 'customer_name', 'customer_email', 'customer_phone', 
                     'booking_type', 'checkin_date', 'checkout_date', 'total_amount', 'status', 'payment_status', 'payment_method', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform bookings to arrays
        $bookingsArray = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'type' => 'Booking',
                'booking_type_display' => ucfirst(str_replace('_', ' ', $booking->booking_type)),
                'name' => $booking->customer_name,
                'email' => $booking->customer_email,
                'phone' => $booking->customer_phone,
                'reference' => $booking->transaction_id,
                'date' => $booking->checkin_date,
                'checkout_date' => $booking->checkout_date,
                'amount' => $booking->total_amount,
                'status' => $booking->status,
                'payment_status' => $booking->payment_status,
                'payment_method' => $booking->payment_method,
                'created_at' => $booking->created_at,
                'route' => route('admin.bookings.show', $booking->id),
            ];
        })->toArray();

        // Get recent dining reservations
        $diningArray = [];
        $diningQuery = DiningReservation::query();
        
        if ($statusFilter !== 'all') {
            $diningQuery->where('status', $statusFilter);
        }

        $dining = $diningQuery
                ->select('id', 'first_name', 'last_name', 'email', 'phone', 
                        'reservation_date', 'reservation_time', 'adults', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

        // Transform dining to arrays
        $diningArray = $dining->map(function ($res) {
            return [
                'id' => $res->id,
                'type' => 'Dining',
                'booking_type_display' => 'Dining Reservation',
                'name' => $res->first_name . ' ' . $res->last_name,
                'email' => $res->email,
                'phone' => $res->phone,
                'reference' => 'DIN-' . str_pad($res->id, 5, '0', STR_PAD_LEFT),
                'date' => $res->reservation_date,
                'time' => $res->reservation_time,
                'guests' => $res->adults,
                'status' => $res->status,
                'created_at' => $res->created_at,
                'route' => '#', // Link to dining if available
            ];
        })->toArray();

        // Merge arrays and sort by created_at
        $allReservations = array_merge($bookingsArray, $diningArray);
        usort($allReservations, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        // Create manual pagination
        $total = count($allReservations);
        $items = array_slice($allReservations, ($page - 1) * $perPage, $perPage);
        
        $bookings = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => route('admin.reservations.monitor'),
                'query' => $request->query(),
            ]
        );

        // Statistics
        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'confirmed')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'total_dining' => DiningReservation::count(),
            'pending_dining' => DiningReservation::where('status', 'pending')->count(),
            'confirmed_dining' => DiningReservation::where('status', 'confirmed')->count(),
            'cancelled_dining' => DiningReservation::where('status', 'cancelled')->count(),
        ];

        // Revenue stats
        $revenueStats = [
            'today' => Booking::whereDate('created_at', today())->sum('total_amount'),
            'this_month' => Booking::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount'),
            'total' => Booking::sum('total_amount'),
        ];

        return view('admin.reservations.monitor', compact('bookings', 'stats', 'revenueStats', 'statusFilter'));
    }

    /**
     * Get real-time reservation updates (API)
     */
    public function getRecent(Request $request)
    {
        $limit = $request->input('limit', 10);
        
        $bookings = Booking::select('id', 'transaction_id', 'customer_name', 'total_amount', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $dining = DiningReservation::select('id', 'first_name', 'last_name', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'bookings' => $bookings->map(fn($b) => [
                'type' => 'booking',
                'name' => $b->customer_name,
                'ref' => $b->transaction_id,
                'amount' => $b->total_amount,
                'status' => $b->status,
                'created_at' => $b->created_at->diffForHumans(),
            ]),
            'dining' => $dining->map(fn($d) => [
                'type' => 'dining',
                'name' => $d->first_name . ' ' . $d->last_name,
                'status' => $d->status,
                'created_at' => $d->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Record a payment for a booking
     */
    public function recordPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:online,cash,check,transfer,other',
                'notes' => 'nullable|string|max:500',
            ]);

            $booking = Booking::findOrFail($validated['booking_id']);

            // Update payment status
            $booking->update([
                'payment_status' => 'paid',
                'payment_method' => $validated['payment_method'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'booking' => [
                    'id' => $booking->id,
                    'payment_status' => $booking->payment_status,
                    'payment_method' => $booking->payment_method,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recording payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking details for payment modal
     */
    public function getBookingDetails($id)
    {
        $booking = Booking::findOrFail($id);
        
        return response()->json([
            'id' => $booking->id,
            'reference' => $booking->transaction_id,
            'customer_name' => $booking->customer_name,
            'customer_email' => $booking->customer_email,
            'total_amount' => $booking->total_amount,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
        ]);
    }

    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:confirmed,completed,cancelled',
                'notes' => 'nullable|string|max:500',
            ]);

            $booking = Booking::findOrFail($id);

            // Update status
            $booking->update([
                'status' => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "Booking marked as " . ucfirst($validated['status']),
                'status' => $booking->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $booking = Booking::findOrFail($id);

            if ($booking->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is already cancelled'
                ], 422);
            }

            $booking->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully',
                'status' => $booking->status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking timeline events
     */
    public function timeline($bookingId)
    {
        try {
            $booking = Booking::findOrFail($bookingId);
            $timeline = BookingTimeline::where('booking_id', $bookingId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'timeline' => $timeline->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'event_type' => $event->event_type,
                        'event_label' => $event->event_label,
                        'event_icon' => $event->event_icon,
                        'event_color' => $event->event_color,
                        'description' => $event->description,
                        'field_name' => $event->field_name,
                        'old_value' => $event->old_value,
                        'new_value' => $event->new_value,
                        'triggered_by' => $event->triggered_by,
                        'created_at' => $event->created_at->toIso8601String(),
                        'created_at_display' => $event->created_at->format('M d, Y g:i A'),
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch timeline', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch timeline',
            ], 500);
        }
    }

    /**
     * Bulk update reservation statuses
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:bookings,id',
            'status' => 'required|in:confirmed,completed,cancelled',
        ]);

        try {
            $updated = 0;
            $failed = [];
            $adminId = Auth::id();

            foreach ($validated['ids'] as $bookingId) {
                $booking = Booking::find($bookingId);
                if ($booking) {
                    if (BookingStatusService::updateStatus(
                        $booking,
                        $validated['status'],
                        'admin',
                        $adminId
                    )) {
                        $updated++;
                    } else {
                        $failed[] = $bookingId;
                    }
                }
            }

            Log::info("Bulk status update completed", [
                'total' => count($validated['ids']),
                'updated' => $updated,
                'failed' => count($failed),
                'status' => $validated['status'],
                'admin_id' => $adminId,
            ]);

            return response()->json([
                'success' => count($failed) === 0,
                'message' => $updated . ' booking(s) updated successfully' . (count($failed) > 0 ? ', ' . count($failed) . ' failed' : ''),
                'updated' => $updated,
                'failed' => $failed,
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bookings',
            ], 500);
        }
    }

    /**
     * Export selected reservations as CSV
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:bookings,id',
        ]);

        try {
            $bookings = Booking::whereIn('id', $validated['ids'])->get();

            // Create CSV
            $filename = 'reservations-' . now()->format('Y-m-d-His') . '.csv';
            $handle = fopen('php://memory', 'r+');

            // Write headers
            fputcsv($handle, [
                'ID',
                'Reference',
                'Customer Name',
                'Email',
                'Phone',
                'Check-In Date',
                'Check-Out Date',
                'Booking Type',
                'Total Amount',
                'Payment Status',
                'Booking Status',
                'Created At',
            ]);

            // Write data
            foreach ($bookings as $booking) {
                fputcsv($handle, [
                    $booking->id,
                    $booking->transaction_id,
                    $booking->customer_name,
                    $booking->customer_email,
                    $booking->customer_phone,
                    $booking->checkin_date,
                    $booking->checkout_date,
                    ucfirst(str_replace('_', ' ', $booking->booking_type)),
                    $booking->total_amount,
                    ucfirst($booking->payment_status),
                    ucfirst($booking->status),
                    $booking->created_at->toDateTimeString(),
                ]);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Export failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export reservations',
            ], 500);
        }
    }
}
