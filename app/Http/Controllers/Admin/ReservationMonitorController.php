<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTimeline;
use App\Models\DiningReservation;
use App\Models\AdminActivityLog;
use App\Services\BookingStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\VatService;

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

        // Get ONLY dining reservations (no bookings)
        $diningQuery = DiningReservation::query();
        
        if ($statusFilter !== 'all') {
            $diningQuery->where('status', $statusFilter);
        }

        $dining = $diningQuery
                ->with('food') // Eager load food relationship
                ->select('id', 'food_id', 'first_name', 'last_name', 'email', 'phone', 
                        'reservation_date', 'reservation_time', 'adults', 'status', 'payment_status', 'total_amount', 'created_at')
                ->orderBy('created_at', 'asc')
                ->get();

        // Transform dining to arrays
        $vatService = app(VatService::class);

        $allReservations = $dining->map(function ($res) use ($vatService) {
            return [
                'id' => 'dining_' . $res->id,
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
                'payment_status' => $res->payment_status ?? 'pending',
                'amount' => (float)($res->total_amount ?? 0),
                'vat_amount' => $vatService->extractVatFromInclusive((float)($res->total_amount ?? 0)),
                'food_name' => $res->food ? $res->food->name : 'N/A',
                'food_id' => $res->food_id,
                'created_at' => $res->created_at,
                'route' => '#', // Link to dining if available
            ];
        })->toArray();

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
            'pending_bookings' => Booking::where('status', 'pending')->count(),
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
     * Get fresh statistics (used for real-time updates)
     */
    public function getStats()
    {
        // Show ONLY dining reservation stats
        $stats = [
            'total_bookings' => 0, // Hidden - dining only
            'pending_bookings' => 0, // Hidden - dining only
            'completed_bookings' => 0, // Hidden - dining only
            'cancelled_bookings' => 0, // Hidden - dining only
            'total_dining' => DiningReservation::count(),
            'pending_dining' => DiningReservation::where('status', 'pending')->count(),
            'confirmed_dining' => DiningReservation::where('status', 'confirmed')->count(),
            'cancelled_dining' => DiningReservation::where('status', 'cancelled')->count(),
        ];

        $revenueStats = [
            'today' => 0, // No revenue for dining
            'this_month' => 0, // No revenue for dining
            'total' => 0, // No revenue for dining
        ];

        return response()->json([
            'stats' => $stats,
            'revenueStats' => $revenueStats,
        ]);
    }

    /**
     * Record a payment for a booking
     */
    public function recordPayment(Request $request)
    {
        // Log incoming payload to aid debugging payment issues
        Log::info('recordPayment payload', ['payload' => $request->all(), 'ip' => $request->ip(), 'user_id' => Auth::id()]);

        try {
            $validated = $request->validate([
                'booking_id' => 'nullable|exists:bookings,id',
                'dining_id' => 'nullable|exists:dining_reservations,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:online,cash,check,transfer,other,bank_transfer,credit_card,paymongo',
                'notes' => 'nullable|string|max:500',
            ]);

            // Handle booking payment
            if ($validated['booking_id']) {
                $booking = Booking::findOrFail($validated['booking_id']);

                // Use BookingStatusService to record payment with timeline logging
                Log::info('Recording booking payment via admin', ['booking_id' => $booking->id, 'amount' => $validated['amount'], 'method' => $validated['payment_method']]);

                // Call service with expected signature (booking, newPaymentStatus, amount, method, reference=null, triggeredBy='system', adminId=null)
                $success = BookingStatusService::updatePaymentStatus(
                    $booking,
                    'paid',
                    (float) $validated['amount'],
                    $validated['payment_method'],
                    $validated['notes'] ?? null,
                    'admin',
                    Auth::id()
                );

                // Refresh booking to ensure we return updated attributes
                $booking->refresh();

                Log::info('Booking after payment update', ['booking_id' => $booking->id, 'payment_status' => $booking->payment_status, 'payment_method' => $booking->payment_method]);

                if (!$success) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to record payment. Please try again.'
                    ], 400);
                }

                AdminActivityLog::log(
                    'booking_payment_recorded',
                    "Recorded payment for booking {$booking->transaction_id} via {$validated['payment_method']}",
                    $booking,
                    [
                        'new_payment_status' => $booking->payment_status,
                        'payment_method' => $booking->payment_method,
                        'amount' => (float) $validated['amount'],
                        'notes' => $validated['notes'] ?? null,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'booking' => [
                        'id' => $booking->id,
                        'payment_status' => $booking->payment_status,
                        'payment_method' => $booking->payment_method,
                    ]
                ]);
            }
            
            // Handle dining reservation payment
            if ($validated['dining_id']) {
                $dining = DiningReservation::findOrFail($validated['dining_id']);

                Log::info('Recording dining payment via admin - before update', ['dining_id' => $dining->id, 'current_payment_status' => $dining->payment_status, 'current_total' => $dining->total_amount]);

                $dining->update([
                    'payment_status' => 'paid',
                    'payment_method' => $validated['payment_method'],
                    'payment_notes' => $validated['notes'] ?? null,
                    'total_amount' => (float) $validated['amount'],
                ]);

                // Refresh and log after update to ensure persistence
                $dining->refresh();
                Log::info('Recording dining payment via admin - after update', ['dining_id' => $dining->id, 'payment_status' => $dining->payment_status, 'total_amount' => $dining->total_amount]);

                AdminActivityLog::log(
                    'dining_payment_recorded',
                    "Recorded payment for dining reservation #{$dining->id} via {$validated['payment_method']}",
                    $dining,
                    [
                        'new_payment_status' => $dining->payment_status,
                        'payment_method' => $dining->payment_method,
                        'amount' => (float) $validated['amount'],
                        'notes' => $validated['notes'] ?? null,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'dining' => [
                        'id' => $dining->id,
                        'payment_status' => $dining->payment_status,
                        'payment_method' => $dining->payment_method,
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid reservation type'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error recording payment', [
                'booking_id' => $request->booking_id ?? null,
                'dining_id' => $request->dining_id ?? null,
                'error' => $e->getMessage(),
            ]);
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
        $booking = Booking::find($id);
        
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found. It may have been deleted.',
            ], 404);
        }
        
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

            $booking = Booking::find($id);
            
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found. It may have been deleted.',
                ], 404);
            }

            $oldStatus = $booking->status;

            // Use BookingStatusService to update status with timeline logging
            $success = BookingStatusService::updateStatus(
                $booking,
                $validated['status'],
                'admin',
                Auth::id(),
                ($validated['notes'] ?? null) ? ['notes' => $validated['notes']] : []
            );

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition for current booking status.',
                ], 400);
            }

            $booking->refresh();
            AdminActivityLog::log(
                'booking_status_update',
                "Updated booking {$booking->transaction_id} status from {$oldStatus} to {$booking->status}",
                $booking,
                [
                    'old_status' => $oldStatus,
                    'new_status' => $booking->status,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Booking marked as " . ucfirst($validated['status']),
                'status' => $booking->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating booking status', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update dining reservation status
     */
    public function updateDiningStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,completed,cancelled',
                'notes' => 'nullable|string|max:500',
            ]);

            $dining = DiningReservation::find($id);
            
            if (!$dining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dining reservation not found. It may have been deleted.',
                ], 404);
            }

            // Store old status for activity log
            $oldStatus = $dining->status;

            // Update status
            $dining->update([
                'status' => $validated['status'],
            ]);

            // Log the action - with proper error handling
            try {
                AdminActivityLog::log(
                    'dining_reservation_status_update',
                    "Updated dining reservation #{$id} status from {$oldStatus} to {$validated['status']}",
                    $dining,
                    ['old_status' => $oldStatus, 'new_status' => $validated['status'], 'notes' => $validated['notes'] ?? null]
                );
            } catch (\Exception $logError) {
                Log::warning('Could not create activity log for dining status update', [
                    'dining_id' => $id,
                    'log_error' => $logError->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Dining reservation marked as " . ucfirst($validated['status']),
                'status' => $dining->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating dining status', [
                'dining_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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

            $booking = Booking::find($id);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found. It may have been deleted.',
                ], 404);
            }

            if ($booking->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is already cancelled'
                ], 422);
            }

            $oldStatus = $booking->status;

            $booking->update([
                'status' => 'cancelled',
            ]);

            AdminActivityLog::log(
                'booking_status_update',
                "Cancelled booking {$booking->transaction_id} from {$oldStatus} to cancelled",
                $booking,
                ['old_status' => $oldStatus, 'new_status' => 'cancelled', 'reason' => $validated['reason'] ?? null]
            );

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
            $booking = Booking::find($bookingId);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found. It may have been deleted.',
                ], 404);
            }
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
            'booking_ids' => 'nullable|array',
            'booking_ids.*' => 'integer|exists:bookings,id',
            'dining_ids' => 'nullable|array',
            'dining_ids.*' => 'integer|exists:dining_reservations,id',
            'status' => 'required|in:confirmed,completed,cancelled',
        ]);

        try {
            $updated = 0;
            $failed = [];
            $adminId = Auth::id();
            $bookingIds = $validated['booking_ids'] ?? [];
            $diningIds = $validated['dining_ids'] ?? [];

            // If attempting to mark completed, first validate none of the selected
            // reservations are unpaid. Return a 422 with details so frontend can
            // display a clear error instead of silently skipping items.
            if ($validated['status'] === 'completed') {
                $unpaid = [];

                foreach ($bookingIds as $bookingId) {
                    $booking = Booking::find($bookingId);
                    if ($booking && ($booking->payment_status ?? 'pending') !== 'paid') {
                        $unpaid[] = 'booking_' . $bookingId;
                    }
                }

                foreach ($diningIds as $diningId) {
                    $dining = DiningReservation::find($diningId);
                    if ($dining && ($dining->payment_status ?? 'pending') !== 'paid') {
                        $unpaid[] = 'dining_' . $diningId;
                    }
                }

                if (!empty($unpaid)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some selected reservations are unpaid and cannot be marked completed.',
                        'unpaid' => $unpaid,
                    ], 422);
                }
            }

            // Update bookings
            foreach ($bookingIds as $bookingId) {
                $booking = Booking::find($bookingId);
                if ($booking) {
                    // If attempting to mark completed, ensure booking is paid
                    if ($validated['status'] === 'completed' && ($booking->payment_status ?? 'pending') !== 'paid') {
                        $failed[] = 'booking_' . $bookingId . ':unpaid';
                        continue;
                    }

                    if (BookingStatusService::updateStatus(
                        $booking,
                        $validated['status'],
                        'admin',
                        $adminId
                    )) {
                        $updated++;
                    } else {
                        $failed[] = 'booking_' . $bookingId;
                    }
                }
            }

            // Update dining reservations
            foreach ($diningIds as $diningId) {
                $dining = DiningReservation::find($diningId);
                if ($dining) {
                    // If attempting to mark completed, ensure dining reservation is paid
                    if ($validated['status'] === 'completed' && ($dining->payment_status ?? 'pending') !== 'paid') {
                        $failed[] = 'dining_' . $diningId . ':unpaid';
                        continue;
                    }

                    $oldStatus = $dining->status;
                    $dining->update(['status' => $validated['status']]);
                    $updated++;
                }
            }

            if ($updated > 0) {
                AdminActivityLog::log(
                    'bulk_reservation_status_update',
                    "Bulk updated {$updated} reservation(s) to {$validated['status']}",
                    null,
                    [
                        'booking_ids' => $bookingIds,
                        'dining_ids' => $diningIds,
                        'new_status' => $validated['status'],
                        'updated_count' => $updated,
                        'failed' => $failed,
                    ]
                );
            }

            Log::info("Bulk status update completed", [
                'bookings_total' => count($bookingIds),
                'dining_total' => count($diningIds),
                'updated' => $updated,
                'failed' => count($failed),
                'status' => $validated['status'],
                'admin_id' => $adminId,
            ]);

            return response()->json([
                'success' => count($failed) === 0,
                'message' => $updated . ' reservation(s) updated successfully' . (count($failed) > 0 ? ', ' . count($failed) . ' failed' : ''),
                'updated' => $updated,
                'failed' => $failed,
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reservations',
            ], 500);
        }
    }

    /**
     * Export selected reservations as CSV
     */
    public function export(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'string',
            ]);

            $bookingIds = [];
            $diningIds = [];

            // Parse prefixed IDs
            foreach ($validated['ids'] as $prefixedId) {
                [$type, $id] = explode('_', $prefixedId, 2);
                if ($type === 'booking') {
                    $bookingIds[] = (int)$id;
                } elseif ($type === 'dining') {
                    $diningIds[] = (int)$id;
                }
            }

            // Fetch bookings
            $bookings = [];
            if (!empty($bookingIds)) {
                $bookings = Booking::whereIn('id', $bookingIds)
                    ->select('id', 'transaction_id', 'customer_name', 'customer_email', 'customer_phone', 
                             'checkin_date', 'checkout_date', 'booking_type', 'total_amount', 
                             'payment_status', 'status', 'created_at')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(fn($b) => [
                        'type' => 'Booking',
                        'name' => $b->customer_name,
                        'contact' => $b->customer_email . ' / ' . $b->customer_phone,
                        'reference' => $b->transaction_id,
                        'date' => $b->checkin_date ? \Carbon\Carbon::parse($b->checkin_date)->format('M d, Y') : '',
                        'status' => ucfirst($b->status),
                        'payment' => ucfirst($b->payment_status) . ' - ₱' . number_format($b->total_amount, 2),
                        'time' => $b->checkout_date ? 'to ' . \Carbon\Carbon::parse($b->checkout_date)->format('M d, Y') : '',
                    ])->toArray();
            }

            // Fetch dining reservations
            $dining = [];
            if (!empty($diningIds)) {
                $dining = DiningReservation::whereIn('id', $diningIds)
                    ->select('id', 'first_name', 'last_name', 'email', 'phone', 
                             'reservation_date', 'reservation_time', 'adults', 'status', 'created_at')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(fn($d) => [
                        'type' => 'Dining',
                        'name' => $d->first_name . ' ' . $d->last_name,
                        'contact' => $d->email . ' / ' . $d->phone,
                        'reference' => 'DR-' . str_pad($d->id, 6, '0', STR_PAD_LEFT),
                        'date' => $d->reservation_date ? \Carbon\Carbon::parse($d->reservation_date)->format('M d, Y') : '',
                        'status' => ucfirst($d->status),
                        'payment' => $d->adults . ' guest' . ($d->adults > 1 ? 's' : ''),
                        'time' => $d->reservation_time ? date('H:i', strtotime($d->reservation_time)) : '',
                    ])->toArray();
            }

            // Merge both arrays
            $allReservations = array_merge($bookings, $dining);

            if (empty($allReservations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reservations found for export',
                ]);
            }

            $filename = 'reservations-' . now()->format('Y-m-d-His') . '.csv';

            // Clear any buffered output
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Set headers
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Write CSV headers
            $csvHeaders = ['Type', 'Name', 'Contact', 'Reference', 'Date', 'Status', 'Payment', 'Time'];
            fputcsv($output, $csvHeaders);

            // Write data rows
            foreach ($allReservations as $row) {
                fputcsv($output, [
                    $row['type'],
                    $row['name'],
                    $row['contact'],
                    $row['reference'],
                    $row['date'],
                    $row['status'],
                    $row['payment'],
                    $row['time'],
                ]);
            }

            fclose($output);
            exit();

        } catch (\Illuminate\Validation\ValidationException $e) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            $errors = \Illuminate\Support\Arr::flatten($e->errors());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $errors),
            ]);
            exit();
        } catch (\Exception $e) {
            Log::error('Export failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Failed to export reservations: ' . $e->getMessage(),
            ]);
            exit();
        }
    }
}
