<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PackageBooking;
use App\Models\Staycation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminBookingMonitoringController extends Controller
{
    private const PER_PAGE = 10;

    public function staycations(Request $request): View
    {
        $rows = $this->rowsForMode('staycation');

        return $this->renderPage(
            $request,
            'Staycation Bookings',
            'Room and accommodation bookings only.',
            $rows,
            [
                ['label' => 'Reference', 'key' => 'reference_html'],
                ['label' => 'Guest', 'key' => 'guest_html'],
                ['label' => 'Booking Date', 'key' => 'booking_date_html'],
                ['label' => 'Stay Dates', 'key' => 'schedule_html'],
                ['label' => 'Room Type', 'key' => 'details_html'],
                ['label' => 'Guests', 'key' => 'quantity_html'],
                ['label' => 'Total Amount', 'key' => 'amount_html'],
                ['label' => 'Payment', 'key' => 'payment_html'],
                ['label' => 'Status', 'key' => 'status_html'],
                ['label' => 'Actions', 'key' => 'actions_html'],
            ],
            'staycation'
        );
    }

    public function packages(Request $request): View
    {
        $rows = $this->rowsForMode('package');

        return $this->renderPage(
            $request,
            'Package Bookings',
            'Package bookings only.',
            $rows,
            [
                ['label' => 'Reference', 'key' => 'reference_html'],
                ['label' => 'Guest', 'key' => 'guest_html'],
                ['label' => 'Booking Date', 'key' => 'booking_date_html'],
                ['label' => 'Package Date', 'key' => 'schedule_html'],
                ['label' => 'Package', 'key' => 'details_html'],
                ['label' => 'Guests', 'key' => 'quantity_html'],
                ['label' => 'Total Amount', 'key' => 'amount_html'],
                ['label' => 'Payment', 'key' => 'payment_html'],
                ['label' => 'Status', 'key' => 'status_html'],
                ['label' => 'Actions', 'key' => 'actions_html'],
            ],
            'package'
        );
    }

    public function foodAndBeverage(Request $request): View
    {
        $rows = $this->rowsForMode('food');

        return $this->renderPage(
            $request,
            'Food & Beverage Bookings',
            'Food and meal orders connected to bookings.',
            $rows,
            [
                ['label' => 'Reference', 'key' => 'reference_html'],
                ['label' => 'Guest', 'key' => 'guest_html'],
                ['label' => 'Booking Date', 'key' => 'booking_date_html'],
                ['label' => 'Food Items', 'key' => 'details_html'],
                ['label' => 'Qty', 'key' => 'quantity_html'],
                ['label' => 'Serving Date', 'key' => 'schedule_html'],
                ['label' => 'Total Amount', 'key' => 'amount_html'],
                ['label' => 'Payment', 'key' => 'payment_html'],
                ['label' => 'Status', 'key' => 'status_html'],
                ['label' => 'Actions', 'key' => 'actions_html'],
            ],
            'food'
        );
    }

    public function activities(Request $request): View
    {
        $rows = $this->rowsForMode('activity');

        return $this->renderPage(
            $request,
            'Activities Bookings',
            'Selected activities and activity packages connected to bookings.',
            $rows,
            [
                ['label' => 'Reference', 'key' => 'reference_html'],
                ['label' => 'Guest', 'key' => 'guest_html'],
                ['label' => 'Booking Date', 'key' => 'booking_date_html'],
                ['label' => 'Activities', 'key' => 'details_html'],
                ['label' => 'Pax', 'key' => 'quantity_html'],
                ['label' => 'Schedule', 'key' => 'schedule_html'],
                ['label' => 'Total Amount', 'key' => 'amount_html'],
                ['label' => 'Payment', 'key' => 'payment_html'],
                ['label' => 'Status', 'key' => 'status_html'],
                ['label' => 'Actions', 'key' => 'actions_html'],
            ],
            'activity'
        );
    }

    public function exportStaycations(Request $request): StreamedResponse
    {
        return $this->exportPage($request, 'staycation');
    }

    public function exportPackages(Request $request): StreamedResponse
    {
        return $this->exportPage($request, 'package');
    }

    public function exportFoodAndBeverage(Request $request): StreamedResponse
    {
        return $this->exportPage($request, 'food');
    }

    public function exportActivities(Request $request): StreamedResponse
    {
        return $this->exportPage($request, 'activity');
    }

    private function renderPage(Request $request, string $title, string $subtitle, Collection $rows, array $columns, string $mode): View
    {
        $statusFilter = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $filteredRows = $this->filterRows($rows, $statusFilter, $search, $dateFrom, $dateTo);

        $todayCount = $filteredRows->filter(fn (object $row) => ($row->schedule_date ?? null) instanceof Carbon && $row->schedule_date->isToday())->count();
        $upcomingCount = $filteredRows->filter(fn (object $row) => ($row->schedule_date ?? null) instanceof Carbon && $row->schedule_date->isFuture())->count();
        $pendingCount = $filteredRows->filter(fn (object $row) => (bool) ($row->is_pending ?? false))->count();
        $totalAmount = (float) $filteredRows->sum(fn (object $row) => (float) ($row->total_amount ?? 0));

        $statsCards = [
            [
                'label' => "Today's Bookings",
                'value' => $todayCount,
                'subtitle' => 'Bookings scheduled for today',
                'icon' => 'sun',
                'iconBg' => 'bg-amber-50',
                'iconClass' => 'text-amber-600',
            ],
            [
                'label' => 'Upcoming Bookings',
                'value' => $upcomingCount,
                'subtitle' => 'Bookings scheduled after today',
                'icon' => 'calendar-range',
                'iconBg' => 'bg-blue-50',
                'iconClass' => 'text-blue-600',
            ],
            [
                'label' => 'Pending Bookings',
                'value' => $pendingCount,
                'subtitle' => 'Awaiting payment or confirmation',
                'icon' => 'clock-3',
                'iconBg' => 'bg-rose-50',
                'iconClass' => 'text-rose-600',
            ],
            [
                'label' => 'Total Amount',
                'value' => html_entity_decode('&#8369;') . number_format($totalAmount, 2),
                'subtitle' => 'Current filtered amount',
                'icon' => 'wallet',
                'iconBg' => 'bg-emerald-50',
                'iconClass' => 'text-emerald-600',
            ],
        ];

        $perPage = self::PER_PAGE;
        $page = max(1, (int) $request->query('page', 1));
        $total = $filteredRows->count();
        $pageRows = $filteredRows->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageRows,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $statusOptions = [
            'all' => 'All',
            'today' => 'Today',
            'upcoming' => 'Upcoming',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $tabs = [
            ['label' => 'Staycation Bookings', 'route' => 'admin.bookings.staycations', 'active' => $mode === 'staycation'],
            ['label' => 'Package Bookings', 'route' => 'admin.bookings.packages', 'active' => $mode === 'package'],
            ['label' => 'Food & Beverage Bookings', 'route' => 'admin.bookings.foodandbeverage', 'active' => $mode === 'food'],
            ['label' => 'Activities Bookings', 'route' => 'admin.bookings.activities', 'active' => $mode === 'activity'],
        ];

        return view('admin.bookings.monitoring.index', [
            'title' => $title,
            'subtitle' => $subtitle,
            'mode' => $mode,
            'columns' => $columns,
            'rows' => $paginator,
            'statsCards' => $statsCards,
            'statusOptions' => $statusOptions,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'clearRoute' => $this->clearRouteForMode($mode),
            'exportRoute' => $this->exportRouteForMode($mode),
            'tabs' => $tabs,
        ]);
    }

    private function exportPage(Request $request, string $mode): StreamedResponse
    {
        $rows = $this->filterRows(
            $this->rowsForMode($mode),
            (string) $request->query('status', 'all'),
            trim((string) $request->query('search', '')),
            $request->query('date_from'),
            $request->query('date_to')
        );

        $filename = $this->exportFilenameForMode($mode);
        $headers = $this->exportHeadersForMode($mode);

        return response()->streamDownload(function () use ($rows, $headers, $mode): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $this->exportRowForMode($mode, $row));
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function rowsForMode(string $mode): Collection
    {
        return match ($mode) {
            'staycation' => Booking::query()
                ->whereIn('booking_type', Booking::STAYCATION_TYPES)
                ->latest()
                ->get()
                ->map(fn (Booking $booking) => $this->mapStaycationRow($booking))
                ->filter()
                ->values(),
            'package' => PackageBooking::query()
                ->with('package')
                ->latest()
                ->get()
                ->map(fn (PackageBooking $booking) => $this->mapPackageRow($booking))
                ->values(),
            'food' => Booking::query()
                ->latest()
                ->get()
                ->map(fn (Booking $booking) => $this->mapFoodRow($booking))
                ->filter()
                ->values(),
            'activity' => Booking::query()
                ->latest()
                ->get()
                ->map(fn (Booking $booking) => $this->mapActivityRow($booking))
                ->filter()
                ->values(),
            default => collect(),
        };
    }

    private function filterRows(Collection $rows, string $statusFilter, string $search, mixed $dateFrom, mixed $dateTo): Collection
    {
        return $rows->filter(function (object $row) use ($statusFilter, $search, $dateFrom, $dateTo) {
            if ($search !== '') {
                $haystack = strtolower(implode(' ', array_filter([
                    (string) ($row->reference ?? ''),
                    (string) ($row->guest_name ?? ''),
                    (string) ($row->contact ?? ''),
                    (string) ($row->email ?? ''),
                ])));

                if (! str_contains($haystack, strtolower($search))) {
                    return false;
                }
            }

            if ($statusFilter !== 'all' && ! $this->matchesStatusFilter($row, $statusFilter)) {
                return false;
            }

            if ($dateFrom || $dateTo) {
                $scheduleDate = $row->schedule_date ?? null;
                if (! $scheduleDate instanceof Carbon) {
                    return false;
                }

                if ($dateFrom && $scheduleDate->lt(Carbon::parse($dateFrom)->startOfDay())) {
                    return false;
                }

                if ($dateTo && $scheduleDate->gt(Carbon::parse($dateTo)->endOfDay())) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    private function exportHeadersForMode(string $mode): array
    {
        return match ($mode) {
            'staycation' => [
                'Reference',
                'Guest Name',
                'Contact Number',
                'Email',
                'Booking Date',
                'Stay Dates',
                'Room Type',
                'Guests',
                'Total Amount',
                'Payment Status',
                'Booking Status',
                'Monitoring Status',
            ],
            'package' => [
                'Reference',
                'Guest Name',
                'Contact Number',
                'Email',
                'Booking Date',
                'Package Date',
                'Package',
                'Guests',
                'Total Amount',
                'Payment Status',
                'Booking Status',
                'Monitoring Status',
            ],
            'food' => [
                'Reference',
                'Guest Name',
                'Contact Number',
                'Email',
                'Booking Date',
                'Food Items',
                'Qty',
                'Serving Date',
                'Total Amount',
                'Payment Status',
                'Booking Status',
                'Monitoring Status',
            ],
            'activity' => [
                'Reference',
                'Guest Name',
                'Contact Number',
                'Email',
                'Booking Date',
                'Activities',
                'Pax',
                'Schedule',
                'Total Amount',
                'Payment Status',
                'Booking Status',
                'Monitoring Status',
            ],
            default => [],
        };
    }

    private function exportRowForMode(string $mode, object $row): array
    {
        return match ($mode) {
            'staycation' => [
                $row->reference ?? '',
                $row->guest_name ?? '',
                $row->contact ?? '',
                $row->email ?? '',
                $row->booking_label ?? '',
                $row->schedule_label ?? '',
                $row->details ?? '',
                $row->quantity ?? '',
                number_format((float) ($row->total_amount ?? 0), 2, '.', ''),
                $row->payment_status ?? '',
                $row->status ?? '',
                $this->monitoringLabel((string) ($row->monitoring_status ?? 'upcoming')),
            ],
            'package' => [
                $row->reference ?? '',
                $row->guest_name ?? '',
                $row->contact ?? '',
                $row->email ?? '',
                $row->booking_label ?? '',
                $row->schedule_label ?? '',
                $row->details ?? '',
                $row->quantity ?? '',
                number_format((float) ($row->total_amount ?? 0), 2, '.', ''),
                $row->payment_status ?? '',
                $row->status ?? '',
                $this->monitoringLabel((string) ($row->monitoring_status ?? 'upcoming')),
            ],
            'food' => [
                $row->reference ?? '',
                $row->guest_name ?? '',
                $row->contact ?? '',
                $row->email ?? '',
                $row->booking_label ?? '',
                $row->details ?? '',
                $row->quantity ?? '',
                $row->schedule_label ?? '',
                number_format((float) ($row->total_amount ?? 0), 2, '.', ''),
                $row->payment_status ?? '',
                $row->status ?? '',
                $this->monitoringLabel((string) ($row->monitoring_status ?? 'upcoming')),
            ],
            'activity' => [
                $row->reference ?? '',
                $row->guest_name ?? '',
                $row->contact ?? '',
                $row->email ?? '',
                $row->booking_label ?? '',
                $row->details ?? '',
                $row->quantity ?? '',
                $row->schedule_label ?? '',
                number_format((float) ($row->total_amount ?? 0), 2, '.', ''),
                $row->payment_status ?? '',
                $row->status ?? '',
                $this->monitoringLabel((string) ($row->monitoring_status ?? 'upcoming')),
            ],
            default => [],
        };
    }

    private function exportFilenameForMode(string $mode): string
    {
        return match ($mode) {
            'staycation' => 'staycation-bookings-' . now()->format('Y-m-d-His') . '.csv',
            'package' => 'package-bookings-' . now()->format('Y-m-d-His') . '.csv',
            'food' => 'food-and-beverage-bookings-' . now()->format('Y-m-d-His') . '.csv',
            'activity' => 'activities-bookings-' . now()->format('Y-m-d-His') . '.csv',
            default => 'bookings-export-' . now()->format('Y-m-d-His') . '.csv',
        };
    }

    private function clearRouteForMode(string $mode): string
    {
        return match ($mode) {
            'staycation' => route('admin.bookings.staycations'),
            'package' => route('admin.bookings.packages'),
            'food' => route('admin.bookings.foodandbeverage'),
            'activity' => route('admin.bookings.activities'),
            default => route('admin.bookings.index'),
        };
    }

    private function exportRouteForMode(string $mode): string
    {
        return match ($mode) {
            'staycation' => 'admin.bookings.staycations.export',
            'package' => 'admin.bookings.packages.export',
            'food' => 'admin.bookings.foodandbeverage.export',
            'activity' => 'admin.bookings.activities.export',
            default => 'admin.bookings.index',
        };
    }

    private function mapStaycationRow(Booking $booking): ?object
    {
        if (! in_array($booking->booking_type, Booking::STAYCATION_TYPES, true)) {
            return null;
        }

        $scheduleDate = $this->resolveScheduleDate($booking, 'checkin_date');
        $accommodationLabel = $this->resolveAccommodationLabel($booking);
        $guestCount = (int) data_get($booking->booking_data, 'total_guests', data_get($booking->booking_data, 'adults_total', data_get($booking->booking_data, 'guests', 0)));

        return $this->buildRow([
            'reference' => $booking->transaction_id,
            'guest_name' => $booking->display_customer_name,
            'contact' => $booking->customer_phone,
            'email' => $booking->customer_email,
            'booking_date' => $booking->created_at,
            'schedule_date' => $scheduleDate,
            'monitoring_status' => $this->resolveMonitoringStatus($booking, $scheduleDate),
            'is_pending' => $this->isPendingRow($booking),
            'total_amount' => (float) $booking->total_amount,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status ?? 'pending',
            'details' => $accommodationLabel,
            'quantity' => $guestCount > 0 ? $guestCount : null,
            'schedule_label' => $this->formatDateLabel($scheduleDate),
            'booking_label' => $booking->created_at?->format('M d, Y H:i') ?? 'N/A',
            'extra_label' => $booking->display_checkout_date?->format('M d, Y') ?? 'N/A',
            'actions_route' => route('admin.bookings.show', $booking->id),
        ]);
    }

    private function mapPackageRow(PackageBooking $booking): object
    {
        $scheduleDate = $booking->booking_date instanceof Carbon
            ? $booking->booking_date
            : Carbon::parse($booking->booking_date);

        return $this->buildRow([
            'reference' => $booking->booking_reference,
            'guest_name' => $booking->full_name,
            'contact' => $booking->phone,
            'email' => $booking->email,
            'booking_date' => $booking->created_at,
            'schedule_date' => $scheduleDate,
            'monitoring_status' => $this->resolvePackageMonitoringStatus($booking, $scheduleDate),
            'is_pending' => $this->isPendingPackageRow($booking),
            'total_amount' => (float) $booking->total_amount,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status ?? 'pending',
            'details' => $booking->package?->name ?? 'Package',
            'quantity' => $booking->total_guests > 0 ? $booking->total_guests : null,
            'schedule_label' => $this->formatDateLabel($scheduleDate),
            'booking_label' => $booking->created_at?->format('M d, Y H:i') ?? 'N/A',
            'extra_label' => $booking->display_session,
            'actions_route' => route('admin.package_bookings.show', $booking->id),
        ]);
    }

    private function mapFoodRow(Booking $booking): ?object
    {
        $foodItems = $this->extractFoodItems($booking->booking_data ?? []);
        if (empty($foodItems)) {
            return null;
        }

        $scheduleDate = $this->resolveScheduleDate($booking, 'checkin_date');
        $servingDate = $this->resolveServingDate($booking, $foodItems);
        $foodSummary = collect($foodItems)->pluck('name')->filter()->implode(', ');
        $totalQuantity = (int) collect($foodItems)->sum('quantity');

        return $this->buildRow([
            'reference' => $booking->transaction_id,
            'guest_name' => $booking->display_customer_name,
            'contact' => $booking->customer_phone,
            'email' => $booking->customer_email,
            'booking_date' => $booking->created_at,
            'schedule_date' => $scheduleDate,
            'monitoring_status' => $this->resolveMonitoringStatus($booking, $scheduleDate),
            'is_pending' => $this->isPendingRow($booking),
            'total_amount' => (float) $booking->total_amount,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status ?? 'pending',
            'details' => $foodSummary !== '' ? $foodSummary : 'Food order',
            'quantity' => $totalQuantity > 0 ? $totalQuantity : null,
            'schedule_label' => $this->formatDateLabel($servingDate),
            'booking_label' => $booking->created_at?->format('M d, Y H:i') ?? 'N/A',
            'extra_label' => $this->foodOrderMeta($foodItems),
            'actions_route' => route('admin.bookings.show', $booking->id),
        ]);
    }

    private function mapActivityRow(Booking $booking): ?object
    {
        [$activities, $packages] = $this->extractActivities($booking->booking_data ?? []);
        if (empty($activities) && empty($packages)) {
            return null;
        }

        $scheduleDate = $this->resolveActivityDate($booking, $activities, $packages);
        $summary = collect($activities)->pluck('name')->merge(collect($packages)->pluck('name'))->filter()->implode(', ');
        $pax = (int) collect($activities)->sum('guests') + (int) collect($packages)->sum('quantity');

        return $this->buildRow([
            'reference' => $booking->transaction_id,
            'guest_name' => $booking->display_customer_name,
            'contact' => $booking->customer_phone,
            'email' => $booking->customer_email,
            'booking_date' => $booking->created_at,
            'schedule_date' => $scheduleDate,
            'monitoring_status' => $this->resolveMonitoringStatus($booking, $scheduleDate),
            'is_pending' => $this->isPendingRow($booking),
            'total_amount' => (float) $booking->total_amount,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status ?? 'pending',
            'details' => $summary !== '' ? $summary : 'Activities order',
            'quantity' => $pax > 0 ? $pax : null,
            'schedule_label' => $this->formatDateLabel($scheduleDate),
            'booking_label' => $booking->created_at?->format('M d, Y H:i') ?? 'N/A',
            'extra_label' => $this->activityOrderMeta($activities, $packages),
            'actions_route' => route('admin.bookings.show', $booking->id),
        ]);
    }

    private function buildRow(array $data): object
    {
        $scheduleDate = $data['schedule_date'] ?? null;
        $row = (object) array_merge($data, [
            'row_class' => $scheduleDate instanceof Carbon && $scheduleDate->isToday() ? 'bg-amber-50/70' : '',
            'reference_html' => $this->referenceCell(
                (string) ($data['reference'] ?? ''),
                $scheduleDate instanceof Carbon ? $scheduleDate : null,
                (string) ($data['monitoring_status'] ?? 'upcoming'),
                (bool) ($data['is_pending'] ?? false)
            ),
            'guest_html' => $this->guestCell(
                (string) ($data['guest_name'] ?? ''),
                $data['contact'] ?? null,
                $data['email'] ?? null
            ),
            'booking_date_html' => $this->plainCell((string) ($data['booking_label'] ?? 'N/A')),
            'schedule_html' => $this->scheduleCell((string) ($data['schedule_label'] ?? 'N/A'), $data['extra_label'] ?? null),
            'details_html' => $this->detailCell((string) ($data['details'] ?? 'N/A')),
            'quantity_html' => $this->plainCell((string) ($data['quantity'] ?? 'N/A')),
            'amount_html' => $this->moneyCell((float) ($data['total_amount'] ?? 0)),
            'payment_html' => $this->paymentCell((string) ($data['payment_status'] ?? 'pending')),
            'status_html' => $this->statusCell(
                (string) ($data['status'] ?? 'pending'),
                (string) ($data['monitoring_status'] ?? 'upcoming'),
                (bool) ($data['is_pending'] ?? false)
            ),
            'actions_html' => $this->actionsCell((string) ($data['actions_route'] ?? route('admin.bookings.index'))),
        ]);

        return $row;
    }

    private function referenceCell(string $reference, ?Carbon $scheduleDate, string $monitoringStatus, bool $isPending): string
    {
        return '<div class="text-sm font-semibold text-gray-900">' . e($reference) . '</div>';
    }

    private function guestCell(string $name, ?string $contact, ?string $email): string
    {
        $meta = array_filter([$contact, $email]);

        return '<div class="space-y-1"><div class="text-sm font-semibold text-gray-900">' . e($name) . '</div><div class="text-xs text-gray-500">' . e(implode(' | ', $meta)) . '</div></div>';
    }

    private function scheduleCell(string $dateLabel, ?string $extraLabel = null): string
    {
        $extra = $extraLabel ? '<div class="text-xs text-gray-500 mt-1">' . e($extraLabel) . '</div>' : '';

        return '<div class="text-sm text-gray-900">' . e($dateLabel) . '</div>' . $extra;
    }

    private function detailCell(string $details): string
    {
        return '<div class="text-sm text-gray-900 leading-6">' . e($details) . '</div>';
    }

    private function plainCell(string $value): string
    {
        return '<div class="text-sm text-gray-900">' . e($value) . '</div>';
    }

    private function moneyCell(float $amount): string
    {
        return '<div class="text-sm font-semibold text-gray-900">' . e(html_entity_decode('&#8369;') . number_format($amount, 2)) . '</div>';
    }

    private function paymentCell(string $paymentStatus): string
    {
        return $this->badge(ucfirst($paymentStatus), match (strtolower($paymentStatus)) {
            'paid' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'refunded' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-700',
        });
    }

    private function statusCell(string $status, string $monitoringStatus, bool $isPending): string
    {
        $badges = [
            $this->badge(ucfirst($status), match (strtolower($status)) {
                'confirmed' => 'bg-emerald-100 text-emerald-800',
                'completed' => 'bg-blue-100 text-blue-800',
                'cancelled' => 'bg-rose-100 text-rose-800',
                'pending' => 'bg-yellow-100 text-yellow-800',
                default => 'bg-gray-100 text-gray-700',
            }),
        ];

        if (in_array($monitoringStatus, ['today', 'upcoming', 'pending'], true)) {
            $badges[] = $this->badge($this->monitoringLabel($monitoringStatus), $this->monitoringBadgeClass($monitoringStatus));
        }

        if ($isPending && $monitoringStatus !== 'pending') {
            $badges[] = $this->badge('Pending', 'bg-yellow-100 text-yellow-800');
        }

        return '<div class="flex flex-wrap gap-2">' . implode('', $badges) . '</div>';
    }

    private function actionsCell(string $route): string
    {
        return '<a href="' . e($route) . '" class="inline-flex items-center gap-1 text-sm font-medium text-[#8f5d54] hover:opacity-90">'
            . '<i data-lucide="eye" class="w-4 h-4"></i><span>View</span></a>';
    }

    private function badge(string $label, string $classes): string
    {
        return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ' . e($classes) . '">' . e($label) . '</span>';
    }

    private function monitoringLabel(string $status): string
    {
        return match ($status) {
            'today' => 'Today',
            'upcoming' => 'Upcoming',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Upcoming',
        };
    }

    private function monitoringBadgeClass(string $status): string
    {
        return match ($status) {
            'today' => 'bg-amber-100 text-amber-800',
            'upcoming' => 'bg-blue-100 text-blue-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-emerald-100 text-emerald-800',
            'cancelled' => 'bg-rose-100 text-rose-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    private function resolveMonitoringStatus(Booking $booking, ?Carbon $scheduleDate): string
    {
        $status = strtolower((string) ($booking->status ?? ''));

        if ($status === 'cancelled') {
            return 'cancelled';
        }

        if ($status === 'completed') {
            return 'completed';
        }

        if ($scheduleDate instanceof Carbon && $scheduleDate->isToday()) {
            return 'today';
        }

        if ($scheduleDate instanceof Carbon && $scheduleDate->isFuture()) {
            return 'upcoming';
        }

        return 'upcoming';
    }

    private function resolvePackageMonitoringStatus(PackageBooking $booking, ?Carbon $scheduleDate): string
    {
        $status = strtolower((string) ($booking->status ?? ''));

        if ($status === 'cancelled') {
            return 'cancelled';
        }

        if ($status === 'completed') {
            return 'completed';
        }

        if ($scheduleDate instanceof Carbon && $scheduleDate->isToday()) {
            return 'today';
        }

        if ($scheduleDate instanceof Carbon && $scheduleDate->isFuture()) {
            return 'upcoming';
        }

        return 'upcoming';
    }

    private function resolveScheduleDate(Booking $booking, string $fallbackColumn): ?Carbon
    {
        $candidates = [
            data_get($booking->booking_data, 'selected_foods.0.date'),
            data_get($booking->booking_data, 'selected_foods.0.serving_date'),
            data_get($booking->booking_data, 'selected_activities.0.date'),
            data_get($booking->booking_data, 'selected_activity_packages.0.date'),
            data_get($booking->booking_data, 'booking_date'),
            $booking->{$fallbackColumn} ?? null,
            $booking->created_at,
        ];

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }

            try {
                return $candidate instanceof Carbon ? $candidate : Carbon::parse($candidate);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    private function resolveServingDate(Booking $booking, array $foodItems): ?Carbon
    {
        $candidates = [
            collect($foodItems)->pluck('date')->first(fn ($value) => ! empty($value)),
            collect($foodItems)->pluck('serving_date')->first(fn ($value) => ! empty($value)),
            data_get($booking->booking_data, 'booking_date'),
            $booking->checkin_date,
            $booking->created_at,
        ];

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }

            try {
                return $candidate instanceof Carbon ? $candidate : Carbon::parse($candidate);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    private function resolveActivityDate(Booking $booking, array $activities, array $packages): ?Carbon
    {
        $candidates = [
            collect($activities)->pluck('date')->first(fn ($value) => ! empty($value)),
            collect($activities)->pluck('schedule_date')->first(fn ($value) => ! empty($value)),
            collect($packages)->pluck('date')->first(fn ($value) => ! empty($value)),
            data_get($booking->booking_data, 'booking_date'),
            $booking->checkin_date,
            $booking->created_at,
        ];

        foreach ($candidates as $candidate) {
            if (! $candidate) {
                continue;
            }

            try {
                return $candidate instanceof Carbon ? $candidate : Carbon::parse($candidate);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    private function resolveAccommodationLabel(Booking $booking): string
    {
        $accommodations = data_get($booking->booking_data, 'accommodations', []);
        if (! is_array($accommodations) || empty($accommodations)) {
            return $booking->display_type_label ?? ucfirst((string) $booking->booking_type);
        }

        $first = reset($accommodations);
        if (! is_array($first)) {
            return $booking->display_type_label ?? ucfirst((string) $booking->booking_type);
        }

        $embeddedName = trim((string) (
            $first['staycation']['name']
            ?? $first['name']
            ?? $first['accommodation_name']
            ?? ''
        ));

        if ($embeddedName !== '') {
            return $embeddedName;
        }

        $staycationId = $first['room_id'] ?? $first['id'] ?? null;
        if ($staycationId) {
            $staycation = Staycation::find($staycationId);
            if ($staycation) {
                return $staycation->name;
            }
        }

        return $booking->display_type_label ?? ucfirst((string) $booking->booking_type);
    }

    private function extractFoodItems(array $bookingData): array
    {
        return collect($bookingData['selected_foods'] ?? [])
            ->filter(fn ($food) => is_array($food) && ! empty($food['name']))
            ->map(function (array $food) {
                return [
                    'name' => (string) $food['name'],
                    'quantity' => (int) ($food['quantity'] ?? 1),
                    'date' => $food['date'] ?? null,
                    'serving_date' => $food['serving_date'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    private function extractActivities(array $bookingData): array
    {
        $activities = collect($bookingData['selected_activities'] ?? [])
            ->filter(fn ($activity) => is_array($activity) && ! empty($activity['name']))
            ->map(function (array $activity) {
                return [
                    'name' => (string) $activity['name'],
                    'guests' => (int) ($activity['guests'] ?? $activity['quantity'] ?? 1),
                    'date' => $activity['date'] ?? null,
                    'schedule_date' => $activity['schedule_date'] ?? null,
                ];
            })
            ->values()
            ->all();

        $packages = collect($bookingData['selected_activity_packages'] ?? [])
            ->filter(fn ($package) => is_array($package) && ! empty($package['name']))
            ->map(function (array $package) {
                return [
                    'name' => (string) $package['name'],
                    'quantity' => (int) ($package['quantity'] ?? 1),
                    'date' => $package['date'] ?? null,
                    'schedule_date' => $package['schedule_date'] ?? null,
                ];
            })
            ->values()
            ->all();

        return [$activities, $packages];
    }

    private function foodOrderMeta(array $foodItems): ?string
    {
        $items = collect($foodItems);
        $count = $items->count();
        $quantity = (int) $items->sum('quantity');

        return $count > 0
            ? "{$count} item" . ($count > 1 ? 's' : '') . ' | ' . $quantity . ' pax'
            : null;
    }

    private function activityOrderMeta(array $activities, array $packages): ?string
    {
        $activityCount = count($activities);
        $packageCount = count($packages);

        if ($activityCount === 0 && $packageCount === 0) {
            return null;
        }

        $parts = [];

        if ($activityCount > 0) {
            $parts[] = $activityCount . ' ' . ($activityCount === 1 ? 'activity' : 'activities');
        }

        if ($packageCount > 0) {
            $parts[] = $packageCount . ' package' . ($packageCount > 1 ? 's' : '');
        }

        return implode(' | ', $parts);
    }

    private function isPendingRow(Booking $booking): bool
    {
        $status = strtolower((string) ($booking->status ?? ''));
        $paymentStatus = strtolower((string) ($booking->payment_status ?? ''));

        return $status === 'pending' || in_array($paymentStatus, ['pending', 'failed'], true);
    }

    private function isPendingPackageRow(PackageBooking $booking): bool
    {
        $status = strtolower((string) ($booking->status ?? ''));
        $paymentStatus = strtolower((string) ($booking->payment_status ?? ''));

        return $status === 'pending_payment' || in_array($paymentStatus, ['pending', 'failed'], true);
    }

    private function matchesStatusFilter(object $row, string $statusFilter): bool
    {
        $scheduleDate = $row->schedule_date ?? null;
        $status = strtolower((string) ($row->status ?? ''));
        $isPending = (bool) ($row->is_pending ?? false);

        return match ($statusFilter) {
            'today' => $scheduleDate instanceof Carbon && $scheduleDate->isToday(),
            'upcoming' => $scheduleDate instanceof Carbon && $scheduleDate->isFuture(),
            'pending' => $isPending,
            'completed' => $status === 'completed',
            'cancelled' => $status === 'cancelled',
            default => true,
        };
    }

    private function formatDateLabel(?Carbon $date): string
    {
        return $date instanceof Carbon ? $date->format('M d, Y') : 'N/A';
    }
}
