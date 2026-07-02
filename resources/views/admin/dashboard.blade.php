@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
@php
  $peso = html_entity_decode('&#8369;');
  $fmtMoney = fn ($value) => $peso . number_format((float) $value, 2);
  $fmtCompactMoney = function ($value) use ($peso) {
    $value = (float) $value;

    if ($value >= 1000000) {
      return $peso . rtrim(rtrim(number_format($value / 1000000, 2), '0'), '.') . 'M';
    }

    if ($value >= 1000) {
      return $peso . rtrim(rtrim(number_format($value / 1000, 2), '0'), '.') . 'K';
    }

    return $peso . number_format($value, 0);
  };
  $fmtInt = fn ($value) => number_format((int) $value);
  $initials = function (?string $name) {
    $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
    $letters = collect($parts)
      ->filter()
      ->take(2)
      ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
      ->implode('');

    return $letters !== '' ? $letters : 'A';
  };

  $from = request('from');
  $to = request('to');
  $dateRangeParams = [];

  if (!empty($start) && !empty($end)) {
    $dateRangeParams = [
      'date_from' => optional($start)->toDateString(),
      'date_to' => optional($end)->toDateString(),
    ];
  }

  $rangeShortLabel = match ($range) {
    'today' => 'Today',
    '30d' => '30 Days',
    'custom' => 'Custom',
    default => '7 Days',
  };

  $rangeDisplay = $start && $end
    ? ($start->isSameDay($end)
      ? $start->format('M j, Y')
      : $start->format('M j') . ' - ' . $end->format('M j, Y'))
    : $rangeShortLabel;

  $revenueChangeUp = $completedRevenueChangePercent >= 0;
  $revenueChangeLabel = ($revenueChangeUp ? '+' : '') . number_format($completedRevenueChangePercent, 1) . '%';
  $lastUpdatedAt = optional($recentActivity->first())->created_at ?? now();

  $overviewCards = [
    [
      'label' => 'Upcoming',
      'value' => $fmtInt($activeBookings),
      'description' => 'Reservations ahead',
      'icon' => 'calendar-days',
      'iconWrap' => 'bg-blue-50 text-blue-600',
      'linkTone' => 'text-blue-600',
      'href' => route('admin.bookings.index', ['status' => 'confirmed']),
    ],
    [
      'label' => 'Completed',
      'value' => $fmtInt($completedBookings),
      'description' => 'Checked-out reservations',
      'icon' => 'badge-check',
      'iconWrap' => 'bg-emerald-50 text-emerald-600',
      'linkTone' => 'text-emerald-600',
      'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['status' => 'completed'])),
    ],
    [
      'label' => 'Live Pending',
      'value' => $fmtInt($pendingBookings),
      'description' => 'Pending payments only',
      'icon' => 'clock-3',
      'iconWrap' => 'bg-orange-50 text-orange-500',
      'linkTone' => 'text-orange-500',
      'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['payment_scope' => 'attempts', 'payment_status' => 'pending'])),
    ],
    [
      'label' => 'Closed',
      'value' => $fmtInt($closedAttemptBookings),
      'description' => 'Failed or expired attempts',
      'icon' => 'x-circle',
      'iconWrap' => 'bg-rose-50 text-rose-600',
      'linkTone' => 'text-rose-600',
      'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['payment_scope' => 'attempts', 'payment_status' => 'failed'])),
    ],
    [
      'label' => 'Today',
      'value' => $fmtInt($todayCheckIns),
      'description' => 'Reservations today',
      'icon' => 'users',
      'iconWrap' => 'bg-violet-50 text-violet-600',
      'linkTone' => 'text-violet-600',
      'href' => route('admin.bookings.index', ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]),
    ],
  ];

  $revenueTiles = [
    [
      'label' => 'Total Revenue',
      'value' => $fmtMoney($completedRevenue),
      'caption' => $completedRevenueComparisonLabel,
      'meta' => 'Completed payments only',
      'accent' => 'text-slate-900',
    ],
    [
      'label' => 'Pending Revenue',
      'value' => $fmtMoney($attemptAmount),
      'caption' => 'Potential pending value',
      'meta' => 'Pending, abandoned and expired bookings',
      'accent' => 'text-orange-500',
    ],
    [
      'label' => 'Refund Amount',
      'value' => $fmtMoney($refundedRevenue),
      'caption' => 'Returned bookings',
      'meta' => 'Refunded transactions',
      'accent' => 'text-violet-600',
    ],
    [
      'label' => 'Net Revenue',
      'value' => $fmtMoney($netRevenue),
      'caption' => 'After refunds',
      'meta' => 'Completed revenue minus refunded amount',
      'accent' => 'text-slate-900',
    ],
  ];

  $salesViewOptions = collect($salesModeOptions ?? [])->map(function ($label, $value) {
    $rangeValue = match ($value) {
      'daily' => 'today',
      'weekly' => '7d',
      'monthly' => '30d',
      default => 'today',
    };

    return [
      'label' => $label,
      'value' => $value,
      'range' => $rangeValue,
      'url' => request()->fullUrlWithQuery(['range' => $rangeValue]),
    ];
  })->values();
  $bookingViewOptions = collect($salesModeOptions ?? [])->map(function ($label, $value) {
    return [
      'label' => $label,
      'value' => $value,
      'url' => request()->fullUrlWithQuery(['booking_view' => $value]),
    ];
  })->values();

  $salesDistributionItems = collect($salesDistributionSeries ?? [])->map(function ($item) {
    return (object) [
      'label' => $item->label,
      'amount' => (float) $item->amount,
      'count' => (int) $item->count,
      'percent' => (float) $item->percent,
      'color' => $item->color,
    ];
  })->values();
  $salesDistributionTotal = max(1, (float) $salesDistributionItems->sum('amount'));
  $salesDistributionActualTotal = (float) $salesDistributionItems->sum('amount');
  $salesDistributionRadius = 72;
  $salesDistributionCircumference = 2 * pi() * $salesDistributionRadius;

  $salesCategoryCards = collect($salesCategoryCards ?? []);
  $salesTrendChartSeries = collect($salesTrendChartSeries ?? []);
  $salesTrendChartPayload = [
    'labels' => $salesTrendChartSeries->pluck('label')->values()->all(),
    'datasets' => [
      [
        'label' => 'Staycation',
        'data' => $salesTrendChartSeries->pluck('staycation')->map(fn ($value) => (float) $value)->values()->all(),
        'backgroundColor' => '#3B82F6',
        'borderColor' => '#3B82F6',
        'stack' => 'sales',
      ],
      [
        'label' => 'Food & Beverage',
        'data' => $salesTrendChartSeries->pluck('food')->map(fn ($value) => (float) $value)->values()->all(),
        'backgroundColor' => '#10B981',
        'borderColor' => '#10B981',
        'stack' => 'sales',
      ],
      [
        'label' => 'Activities',
        'data' => $salesTrendChartSeries->pluck('activities')->map(fn ($value) => (float) $value)->values()->all(),
        'backgroundColor' => '#F59E0B',
        'borderColor' => '#F59E0B',
        'stack' => 'sales',
      ],
      [
        'label' => 'Refunds',
        'data' => $salesTrendChartSeries->pluck('refunds')->map(fn ($value) => (float) $value)->values()->all(),
        'backgroundColor' => '#8B5CF6',
        'borderColor' => '#8B5CF6',
        'stack' => 'sales',
      ],
    ],
  ];

  $revenueSummarySeries = collect($revenueSummarySeries ?? []);
  $revenueChartCount = max(1, $revenueSummarySeries->count());
  $revenueChartMax = max(1, (float) $revenueSummarySeries->map(fn ($row) => (float) ($row->completed ?? 0))->max());
  $revenueChartWidth = max(980, 84 + ($revenueChartCount * 42));
  $revenueChartHeight = 300;
  $revenueChartLeft = 64;
  $revenueChartRight = 24;
  $revenueChartTop = 24;
  $revenueChartBottom = 56;
  $revenueChartPlotHeight = $revenueChartHeight - $revenueChartTop - $revenueChartBottom;
  $revenueLabelStep = match (true) {
    $revenueChartCount > 28 => 4,
    $revenueChartCount > 18 => 3,
    $revenueChartCount > 12 => 2,
    default => 1,
  };
  $revenueChartLine = function ($key) use ($revenueSummarySeries, $revenueChartCount, $revenueChartMax, $revenueChartWidth, $revenueChartHeight, $revenueChartLeft, $revenueChartRight, $revenueChartBottom, $revenueChartPlotHeight) {
    $points = [];

    foreach ($revenueSummarySeries as $index => $row) {
      $value = (float) ($row->{$key} ?? 0);
      $x = $revenueChartCount === 1
        ? ($revenueChartWidth / 2)
        : $revenueChartLeft + (($revenueChartWidth - $revenueChartLeft - $revenueChartRight) * ($index / ($revenueChartCount - 1)));
      $y = $revenueChartHeight - $revenueChartBottom - ($revenueChartPlotHeight * ($value / $revenueChartMax));
      $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    }

    return implode(' ', $points);
  };
  $revenueChartArea = function ($key) use ($revenueSummarySeries, $revenueChartCount, $revenueChartMax, $revenueChartWidth, $revenueChartHeight, $revenueChartLeft, $revenueChartRight, $revenueChartBottom, $revenueChartPlotHeight) {
    $points = [];

    foreach ($revenueSummarySeries as $index => $row) {
      $value = (float) ($row->{$key} ?? 0);
      $x = $revenueChartCount === 1
        ? ($revenueChartWidth / 2)
        : $revenueChartLeft + (($revenueChartWidth - $revenueChartLeft - $revenueChartRight) * ($index / ($revenueChartCount - 1)));
      $y = $revenueChartHeight - $revenueChartBottom - ($revenueChartPlotHeight * ($value / $revenueChartMax));
      $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    }

    $startX = $revenueChartCount === 1
      ? ($revenueChartWidth / 2)
      : $revenueChartLeft;
    $endX = $revenueChartCount === 1
      ? ($revenueChartWidth / 2)
      : ($revenueChartWidth - $revenueChartRight);
    $baseY = $revenueChartHeight - $revenueChartBottom;

    array_unshift($points, number_format($startX, 2, '.', '') . ',' . number_format($baseY, 2, '.', ''));
    $points[] = number_format($endX, 2, '.', '') . ',' . number_format($baseY, 2, '.', '');

    return implode(' ', $points);
  };
  $revenueTrendExportPayload = [
    'labels' => $revenueSummarySeries->pluck('label')->values()->all(),
    'datasets' => [
      [
        'label' => 'Completed Revenue',
        'data' => $revenueSummarySeries->pluck('completed')->map(fn ($value) => (float) $value)->values()->all(),
        'borderColor' => '#2563eb',
        'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
        'fill' => true,
        'tension' => 0.35,
        'pointRadius' => 4,
        'pointHoverRadius' => 5,
        'borderWidth' => 3,
      ],
    ],
  ];

  $bookingOverviewChartItems = collect($bookingOverviewChartItems ?? []);
  $bookingOverviewCount = max(1, $bookingOverviewChartItems->count());
  $bookingOverviewMax = max(1, (int) $bookingOverviewChartItems->map(fn ($row) => max(
    (int) ($row->upcoming ?? 0),
    (int) ($row->completed ?? 0),
    (int) ($row->pending ?? 0),
    (int) ($row->cancelled ?? 0)
  ))->max());
  $bookingTickStep = max(1, (int) ceil($bookingOverviewMax / 4));
  $bookingChartWidth = 1120;
  $bookingChartHeight = 320;
  $bookingChartLeft = 56;
  $bookingChartRight = 28;
  $bookingChartTop = 28;
  $bookingChartBottom = 42;
  $bookingChartPlotHeight = $bookingChartHeight - $bookingChartTop - $bookingChartBottom;
  $bookingLabelStep = match ($bookingView ?? 'daily') {
    'daily' => $bookingOverviewCount > 20 ? 2 : 1,
    'weekly', 'monthly' => 1,
    default => max(1, (int) ceil($bookingOverviewCount / 8)),
  };
  $bookingChartLine = function ($key) use ($bookingOverviewChartItems, $bookingOverviewCount, $bookingOverviewMax, $bookingChartWidth, $bookingChartHeight, $bookingChartLeft, $bookingChartRight, $bookingChartBottom, $bookingChartPlotHeight) {
    $points = [];

    foreach ($bookingOverviewChartItems as $index => $row) {
      $value = (int) ($row->{$key} ?? 0);
      $x = $bookingOverviewCount === 1
        ? ($bookingChartWidth / 2)
        : $bookingChartLeft + (($bookingChartWidth - $bookingChartLeft - $bookingChartRight) * ($index / ($bookingOverviewCount - 1)));
      $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($value / $bookingOverviewMax));
      $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    }

    return implode(' ', $points);
  };

  $bookingSeriesMeta = collect([
    ['key' => 'upcoming', 'label' => 'Upcoming Bookings', 'stroke' => '#2563eb', 'tone' => 'text-blue-600'],
    ['key' => 'completed', 'label' => 'Completed', 'stroke' => '#22c55e', 'tone' => 'text-emerald-600'],
    ['key' => 'active', 'label' => 'Today Active', 'stroke' => '#f97316', 'tone' => 'text-orange-500'],
    ['key' => 'cancelled', 'label' => 'Closed', 'stroke' => '#ef4444', 'tone' => 'text-red-500'],
  ]);

  $latestBookingOverview = $bookingOverviewChartItems->last();
  $bookingDistributionCounts = [
    'completed' => max(0, (int) ($summaryCompletedBookings ?? 0)),
    'active' => max(0, (int) ($latestBookingOverview->active ?? 0)),
    'cancelled' => max(0, (int) ($summaryClosedBookings ?? 0)),
  ];
  $bookingSummaryTotal = max(0, (int) array_sum($bookingDistributionCounts));
  $bookingSummaryBaseRows = $bookingSeriesMeta
    ->reject(fn (array $series) => $series['key'] === 'upcoming')
    ->values()
    ->map(function (array $series) use ($bookingDistributionCounts, $bookingSummaryTotal, $fmtInt) {
      $count = $bookingDistributionCounts[$series['key']] ?? 0;
      $rawPercent = $bookingSummaryTotal > 0 ? (($count / $bookingSummaryTotal) * 100) : 0;

      return [
        'key' => $series['key'],
        'label' => $series['label'],
        'count' => $fmtInt($count),
        'count_value' => $count,
        'raw_percent' => $rawPercent,
        'floor_percent' => (int) floor($rawPercent),
        'remainder' => $rawPercent - floor($rawPercent),
        'tone' => $series['tone'],
      ];
    });
  $bookingOverviewExportPayload = [
    'labels' => $bookingOverviewChartItems->pluck('label')->values()->all(),
    'datasets' => $bookingSeriesMeta->map(function (array $series) use ($bookingOverviewChartItems, $fmtInt) {
      $color = $series['stroke'];
      $values = $bookingOverviewChartItems->pluck($series['key'])->map(fn ($value) => (int) $value)->values();
      $seriesTotal = (int) $values->sum();

      return [
        'label' => $series['label'] . ' (' . $fmtInt($seriesTotal) . ')',
        'data' => $values->all(),
        'borderColor' => $color,
        'backgroundColor' => $color,
        'fill' => false,
        'tension' => 0.35,
        'pointRadius' => 4,
        'pointHoverRadius' => 5,
        'borderWidth' => 3,
      ];
    })->values()->all(),
  ];
  $bookingPercentRemainder = $bookingSummaryTotal > 0
    ? max(0, 100 - $bookingSummaryBaseRows->sum('floor_percent'))
    : 0;
  $bookingPercentByKey = [];
  $bookingSummaryBaseRows
    ->sortByDesc(fn (array $row) => $row['remainder'])
    ->values()
    ->each(function (array $row, int $index) use (&$bookingPercentByKey, $bookingPercentRemainder) {
      $bookingPercentByKey[$row['key']] = $row['floor_percent'] + ($index < $bookingPercentRemainder ? 1 : 0);
    });
  $bookingSummaryBreakdown = $bookingSummaryBaseRows
    ->map(function (array $row) use ($bookingPercentByKey) {
      return [
        'label' => $row['label'],
        'count' => $row['count'],
        'percent' => $bookingPercentByKey[$row['key']] ?? 0,
        'tone' => $row['tone'],
      ];
    })->values();

  $recentBookingCards = collect($recentBookings ?? [])->map(function ($booking) use ($fmtMoney, $initials) {
    $isPackage = $booking instanceof \App\Models\PackageBooking;
    $detailHref = $isPackage
      ? route('admin.package_bookings.show', $booking->id)
      : route('admin.bookings.show', $booking->id);
    $statusTone = match (strtolower((string) $booking->status)) {
      'completed' => 'bg-emerald-50 text-emerald-700',
      'confirmed' => 'bg-blue-50 text-blue-700',
      'cancelled' => 'bg-red-50 text-red-700',
      default => 'bg-orange-50 text-orange-700',
    };

    return [
      'name' => $booking->display_customer_name,
      'initials' => $initials($booking->display_customer_name),
      'item' => $booking->recent_booking_item,
      'meta' => (string) ($booking->recent_booking_meta ?? 'No date'),
      'image' => $booking->recent_booking_image,
      'amount' => $fmtMoney($booking->total_amount),
      'status' => ucfirst((string) $booking->status),
      'statusTone' => $statusTone,
      'href' => $detailHref,
    ];
  })->values();

  $activityCards = collect($recentActivity ?? [])->take(5)->map(function ($activity) use ($initials) {
    $actor = data_get($activity, 'user.first_name') ?: 'Admin';
    $description = $activity->description ?: ucfirst(str_replace('_', ' ', (string) $activity->action));

    return [
      'actor' => $actor,
      'initials' => $initials($actor),
      'title' => $description,
      'meta' => ucfirst((string) $activity->action),
      'time' => optional($activity->created_at)->diffForHumans(),
    ];
  })->values();

@endphp

@php
  $rangeOptions = [
    'today' => 'Today',
    '7d' => 'Last 7 Days',
    '30d' => 'Last 30 Days',
    'custom' => 'Custom Range',
  ];
  $quickActions = [
    [
      'title' => 'Manage Bookings',
      'description' => 'View and manage all bookings',
      'href' => route('admin.bookings.index'),
      'icon' => 'clipboard-check',
      'tone' => 'text-sky-600',
    ],
    [
      'title' => 'Manage Users',
      'description' => 'Add or manage users',
      'href' => route('admin.users.index'),
      'icon' => 'users',
      'tone' => 'text-emerald-600',
    ],
    [
      'title' => 'Manage Inventory',
      'description' => 'Update rooms, items and stock',
      'href' => route('admin.bookings.inventory'),
      'icon' => 'boxes',
      'tone' => 'text-violet-600',
    ],
  ];
@endphp

<div x-data="dashboard(@js($range), @js($salesView), @js($bookingView))" x-init="init()" class="space-y-3 lg:space-y-4">
  <section class="rounded-3xl border border-slate-200 bg-white px-4 py-4 shadow-sm xl:px-5 xl:py-5">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
      <div class="max-w-2xl">
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Dashboard Overview</h1>
        <p class="mt-1 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">Monitor your bookings, revenue, and overall performance.</p>
      </div>

      <form x-ref="filterForm" method="GET" @submit="isLoading = true" class="flex w-full flex-col gap-2.5 xl:w-auto xl:items-end">
      <input type="hidden" name="sales_view" value="{{ $salesView }}">
      <input type="hidden" name="booking_view" value="{{ $bookingView }}">

      <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
        <div class="relative w-full sm:w-auto">
          <div class="pointer-events-none inline-flex w-full min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-medium text-slate-700 shadow-sm sm:min-w-55 sm:text-sm">
            <i data-lucide="calendar-days" class="h-4 w-4 text-slate-500"></i>
            <span class="sm:truncate sm:whitespace-nowrap">{{ $rangeDisplay }}</span>
            <i data-lucide="chevron-down" class="ml-auto h-4 w-4 text-slate-400"></i>
          </div>
          <select
            name="range"
            x-model="range"
            @change="range === 'custom' ? null : submitFilterForm()"
            class="absolute inset-0 cursor-pointer opacity-0"
          >
            @foreach($rangeOptions as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <a
          href="{{ route('admin.dashboard.export', request()->query()) }}"
          class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:text-sm"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
          <span>Export</span>
        </a>
      </div>

      <div
        x-show="range === 'custom'"
        x-cloak
        class="flex flex-col gap-2.5 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center"
      >
        <input
          type="date"
          name="from"
          value="{{ $from }}"
          class="rounded-xl border-slate-200 text-xs text-slate-700 focus:border-[#8f5d54] focus:ring-[#8f5d54] sm:text-sm"
        >
        <input
          type="date"
          name="to"
          value="{{ $to }}"
          class="rounded-xl border-slate-200 text-xs text-slate-700 focus:border-[#8f5d54] focus:ring-[#8f5d54] sm:text-sm"
        >
        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-slate-800 sm:text-sm"
        >
          Apply
        </button>
      </div>
      </form>
    </div>
  </section>

  <section class="grid items-stretch grid-cols-1 gap-3.5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    @foreach($overviewCards as $card)
      <a
        href="{{ $card['href'] }}"
        class="group flex h-full w-full flex-col rounded-3xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-slate-300 hover:shadow-md xl:p-3 2xl:p-4"
      >
        <div class="flex flex-1 items-start gap-3 xl:gap-2.5 2xl:gap-4">
          <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $card['iconWrap'] }} xl:h-10 xl:w-10 2xl:h-14 2xl:w-14">
            <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5 xl:h-4.5 xl:w-4.5 2xl:h-6 2xl:w-6"></i>
          </span>
          <div class="flex min-w-0 flex-1 flex-col justify-start xl:min-h-18 2xl:min-h-24">
            <p class="text-[0.72rem] font-semibold leading-4 text-slate-900 sm:text-xs lg:text-xs xl:text-xs 2xl:text-sm 2xl:leading-5 wrap-break-word">{{ $card['label'] }}</p>
            <p class="mt-1 text-[1.1rem] font-bold leading-none text-slate-900 sm:text-[1.25rem] lg:text-[1.15rem] xl:text-[1.15rem] 2xl:text-[1.5rem]">{{ $card['value'] }}</p>
            <p class="mt-1 text-[0.72rem] leading-4 text-slate-500 sm:text-xs lg:text-xs xl:text-xs 2xl:text-sm 2xl:leading-5 wrap-break-word">{{ $card['description'] }}</p>
          </div>
        </div>

        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-2.5 text-xs font-semibold sm:text-sm xl:mt-2.5 xl:pt-1.5 2xl:mt-4 2xl:pt-3 {{ $card['linkTone'] }}">
          <span>View details</span>
          <i data-lucide="chevron-right" class="h-4 w-4 transition group-hover:translate-x-1"></i>
        </div>
      </a>
    @endforeach
  </section>

  @if($lowStockProducts > 0 || $lowStockFoods > 0)
    <section class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-3 text-[0.72rem] text-amber-900 shadow-sm sm:text-xs lg:text-xs">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <i data-lucide="triangle-alert" class="h-5 w-5 text-amber-600"></i>
          <p>
            Inventory alert:
            @if($lowStockProducts > 0) {{ $lowStockProducts }} product(s) @endif
            @if($lowStockProducts > 0 && $lowStockFoods > 0) and @endif
            @if($lowStockFoods > 0) {{ $lowStockFoods }} food item(s) @endif
            are currently low on stock.
          </p>
        </div>
        <a href="{{ route('admin.bookings.inventory') }}" class="font-semibold text-amber-700 hover:text-amber-900">Open inventory</a>
      </div>
    </section>
  @endif

  <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
      <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500">
          <i data-lucide="circle-dollar-sign" class="h-4 w-4"></i>
        </span>
        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Revenue Summary</h2>
      </div>
      <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm sm:text-sm 2xl:text-base">
        <span class="sm:whitespace-nowrap">{{ $rangeShortLabel }}</span>
        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
      </span>
    </div>

    <div class="grid grid-cols-1 gap-3 px-4 py-3.5 sm:grid-cols-2 sm:px-5 lg:grid-cols-4 2xl:gap-4 2xl:px-6 2xl:py-5">
      @foreach($revenueTiles as $index => $tile)
        <div class="rounded-2xl bg-slate-50/70 px-3.5 py-3.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] 2xl:px-4 2xl:py-4">
          <p class="text-[0.72rem] font-semibold {{ $tile['accent'] }} sm:text-xs lg:text-xs xl:text-xs sm:truncate sm:whitespace-nowrap 2xl:text-sm">{{ $tile['label'] }}</p>
          <div class="mt-3 flex items-center gap-2">
            <p class="text-[1.1rem] font-bold leading-none tracking-tight text-slate-900 sm:text-[1.25rem] lg:text-[1.15rem] 2xl:text-[1.45rem]">{{ $tile['value'] }}</p>
            @if($index === 0)
              <span class="inline-flex items-center gap-1 whitespace-nowrap text-[11px] font-semibold sm:text-xs 2xl:text-sm {{ $revenueChangeUp ? 'text-emerald-600' : 'text-red-500' }}">
                <i data-lucide="{{ $revenueChangeUp ? 'trending-up' : 'trending-down' }}" class="h-3 w-3 sm:h-3.5 sm:w-3.5"></i>
                <span>{{ $revenueChangeLabel }}</span>
              </span>
            @endif
          </div>
          <p class="mt-3 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs xl:text-xs sm:truncate sm:whitespace-nowrap 2xl:text-sm">{{ $tile['caption'] }}</p>
          <p class="mt-1 text-xs leading-5 text-slate-500 lg:text-xs xl:text-xs 2xl:text-sm">{{ $tile['meta'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="px-4 pb-4 sm:px-5 sm:pb-5">
        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">
        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-500">
          <i data-lucide="info" class="h-3.5 w-3.5"></i>
        </span>
        <span>Pending revenue is not included in Total Revenue.</span>
      </div>
    </div>
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:py-6">
      <div class="flex flex-wrap items-center gap-2">
        <i data-lucide="sparkles" class="h-5 w-5 text-slate-500"></i>
        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Sales Report by Category</h2>
      </div>
      <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm sm:text-sm 2xl:text-base">
        <span class="sm:whitespace-nowrap">{{ $rangeShortLabel }}</span>
        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
      </span>
    </div>

    <div class="grid grid-cols-1 gap-3 p-6 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 2xl:gap-4 2xl:p-6">
      @foreach($salesCategoryCards as $card)
        <div class="rounded-2xl border border-gray-200 bg-white px-6 py-6 shadow-sm 2xl:px-7 2xl:py-7">
          <div class="flex items-start gap-4">
            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $card->tone }} 2xl:h-14 2xl:w-14">
              <i data-lucide="{{ $card->icon }}" class="h-5 w-5"></i>
            </span>
            <div class="min-w-0 flex-1">
              <p class="text-[0.72rem] font-semibold text-slate-900 sm:text-xs lg:text-xs sm:truncate sm:whitespace-nowrap xl:overflow-visible xl:whitespace-normal 2xl:text-sm">{{ $card->label }}</p>
              <p class="mt-3 text-[1.1rem] font-bold leading-none tracking-tight text-slate-900 sm:text-[1.25rem] lg:text-[1.15rem] 2xl:text-[1.45rem]">{{ $fmtMoney($card->amount) }}</p>
              <div class="mt-3 space-y-1 text-xs text-slate-500 sm:text-xs lg:text-xs xl:text-xs 2xl:text-sm">
                <p>{{ number_format($card->percent, 0) }}% {{ $card->label === 'Refund Amount' ? 'of completed revenue' : 'of total revenue' }}</p>
                <p>{{ number_format($card->count) }} {{ \Illuminate\Support\Str::plural($card->countLabel, $card->count) }}</p>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <section class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-4 py-3.5 sm:px-5">
        <div class="min-w-0">
          <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Revenue Trend</h2>
        </div>

        <button
          type="button"
          data-revenue-trend-download
          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
          aria-label="Download revenue trend chart"
          title="Download chart"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
        </button>
      </div>

      <div class="px-4 py-4 sm:px-5">
        <div x-data="chartHoverCard({{ $revenueChartWidth }}, {{ $revenueChartHeight }}, 'money')" @mouseleave="clearActive()">
          @if($revenueSummarySeries->count() > 0)
            <div class="mb-2.5 flex items-center gap-2 text-[0.72rem] font-medium text-slate-600 sm:text-xs lg:text-xs">
              <span class="h-1.5 w-5 rounded-full bg-blue-600"></span>
              <span class="sm:whitespace-nowrap">Revenue ({{ $peso }})</span>
            </div>
            <div class="overflow-x-auto rounded-3xl bg-slate-50 p-2.5">
              <div class="relative" data-revenue-trend-chart style="min-width: {{ $revenueChartWidth }}px;">
                <svg viewBox="0 0 {{ $revenueChartWidth }} {{ $revenueChartHeight }}" class="h-68 w-full sm:h-72 2xl:h-80" role="img" aria-label="Revenue trend chart">
                  <defs>
                    <linearGradient id="revenue-fill" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#2563eb" stop-opacity="0.18" />
                      <stop offset="100%" stop-color="#2563eb" stop-opacity="0.02" />
                    </linearGradient>
                  </defs>
                  @foreach([1, 0.75, 0.5, 0.25, 0] as $fraction)
                    @php
                      $gridValue = $revenueChartMax * $fraction;
                      $y = $revenueChartHeight - $revenueChartBottom - ($revenueChartPlotHeight * $fraction);
                    @endphp
                    <text x="0" y="{{ number_format($y + 4, 2, '.', '') }}" class="fill-slate-400 text-[10px] font-medium sm:text-[11px] lg:text-[11px]">{{ $fmtCompactMoney($gridValue) }}</text>
                    <line x1="{{ $revenueChartLeft }}" y1="{{ number_format($y, 2, '.', '') }}" x2="{{ $revenueChartWidth - $revenueChartRight }}" y2="{{ number_format($y, 2, '.', '') }}" stroke="#e2e8f0" stroke-dasharray="4 6" />
                  @endforeach

                  <polygon fill="url(#revenue-fill)" points="{{ $revenueChartArea('completed') }}" />
                  <polyline fill="none" stroke="#2563eb" stroke-width="3.25" stroke-linecap="round" stroke-linejoin="round" points="{{ $revenueChartLine('completed') }}" />

                  @foreach($revenueSummarySeries as $index => $row)
                    @php
                      $x = $revenueChartCount === 1
                        ? ($revenueChartWidth / 2)
                        : $revenueChartLeft + (($revenueChartWidth - $revenueChartLeft - $revenueChartRight) * ($index / ($revenueChartCount - 1)));
                      $value = (float) ($row->completed ?? 0);
                      $y = $revenueChartHeight - $revenueChartBottom - ($revenueChartPlotHeight * ($value / $revenueChartMax));
                      $payload = [
                        'label' => $row->label,
                        'amount' => $value,
                        'stroke' => '#2563eb',
                        'x' => (float) number_format($x, 2, '.', ''),
                        'y' => (float) number_format($y, 2, '.', ''),
                      ];
                    @endphp
                    <circle cx="{{ number_format($x, 2, '.', '') }}" cy="{{ number_format($y, 2, '.', '') }}" r="5" fill="#2563eb" stroke="#ffffff" stroke-width="2" class="cursor-pointer" @mouseenter="setActive(@js($payload))" @focus="setActive(@js($payload))" @click="setActive(@js($payload))" tabindex="0" />
                    @if($index % $revenueLabelStep === 0 || $index === $revenueSummarySeries->count() - 1)
                      <text x="{{ number_format($x, 2, '.', '') }}" y="{{ $revenueChartHeight - 12 }}" text-anchor="middle" class="fill-slate-500 text-[10px] font-medium sm:text-[11px] lg:text-[11px]">{{ $row->label }}</text>
                    @endif
                  @endforeach
                </svg>

                <div x-show="activePoint" x-cloak class="pointer-events-none absolute z-20 hidden min-w-28 -translate-x-1/2 -translate-y-full rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-lg md:block" :style="tooltipStyle">
                  <p class="text-sm font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
                  <p class="text-[11px] text-slate-500" x-text="activePoint?.label ?? ''"></p>
                </div>
              </div>
            </div>
            <div x-show="activePoint" x-cloak class="mt-2.5 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-[11px] shadow-sm md:hidden">
              <p class="text-sm font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
              <p class="text-[11px] text-slate-500" x-text="activePoint?.label ?? ''"></p>
            </div>
          @else
            <div class="flex min-h-68 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs 2xl:min-h-80">
              No revenue data available.
            </div>
          @endif
        </div>

      </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3.5 sm:px-5">
          <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Revenue by Category</h2>
        <button
          type="button"
          data-revenue-by-category-download
          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
          aria-label="Download revenue by category chart"
          title="Download chart"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
        </button>
      </div>

      <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:px-5 lg:grid-cols-[208px_minmax(0,1fr)] lg:items-center">
        <div class="flex justify-center lg:justify-self-end lg:pr-1" data-revenue-by-category-chart>
          <svg viewBox="0 0 200 200" class="h-48 w-48 lg:h-52 lg:w-52 2xl:h-60 2xl:w-60" role="img" aria-label="Revenue by category">
            <circle cx="100" cy="100" r="{{ $salesDistributionRadius }}" fill="none" stroke="#e5e7eb" stroke-width="22"></circle>
            @php $revenueDistributionOffset = 0; @endphp
            @foreach($salesDistributionItems as $item)
              @php
                $segmentLength = ($item->amount / $salesDistributionTotal) * $salesDistributionCircumference;
              @endphp
              <circle
                cx="100"
                cy="100"
                r="{{ $salesDistributionRadius }}"
                fill="none"
                stroke="{{ $item->color }}"
                stroke-width="22"
                stroke-dasharray="{{ number_format($segmentLength, 2, '.', '') }} {{ number_format($salesDistributionCircumference, 2, '.', '') }}"
                stroke-dashoffset="{{ number_format(-$revenueDistributionOffset, 2, '.', '') }}"
                transform="rotate(-90 100 100)"
              ></circle>
              @php $revenueDistributionOffset += $segmentLength; @endphp
            @endforeach
            <text x="100" y="96" text-anchor="middle" textLength="108" lengthAdjust="spacingAndGlyphs" class="fill-slate-900 text-[20px] font-bold">{{ $fmtMoney($salesDistributionActualTotal) }}</text>
            <text x="100" y="118" text-anchor="middle" textLength="84" lengthAdjust="spacingAndGlyphs" class="fill-slate-500 text-[12px]">Total Revenue</text>
          </svg>
        </div>

        <div class="space-y-4 lg:pl-2">
          @foreach($salesDistributionItems as $item)
            <div class="grid grid-cols-[16px_minmax(0,1fr)_auto] items-start gap-3">
              <span class="mt-1 h-3 w-3 rounded-full" style="background-color: {{ $item->color }};"></span>
              <div class="min-w-0">
                <p class="text-xs font-medium text-slate-800 sm:text-sm sm:truncate sm:whitespace-nowrap 2xl:text-base">{{ $item->label }}</p>
              </div>
              <div class="text-right">
                <p class="whitespace-nowrap text-[1.05rem] font-semibold leading-none text-slate-900 sm:text-[1.2rem] 2xl:text-[1.35rem]">{{ number_format($item->percent, 0) }}%</p>
                <p class="mt-1.5 whitespace-nowrap text-xs font-medium leading-none text-slate-600 sm:text-sm 2xl:text-base">{{ $fmtMoney($item->amount) }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <section class="mt-4 rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Sales Trend by Category</h2>
        <p class="mt-1 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">Revenue breakdown for the selected period. Defaults follow the dashboard period.</p>
      </div>

      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        <span class="hidden text-[11px] font-medium uppercase tracking-[0.16em] text-slate-400 lg:inline">Chart period</span>
        <div class="inline-flex flex-wrap rounded-2xl border border-gray-200 bg-slate-50 p-1">
          @foreach($salesViewOptions as $option)
            <a
              href="{{ $option['url'] }}"
              @click="setSalesView('{{ $option['value'] }}', $event)"
              class="rounded-xl px-4 py-2 text-sm font-medium transition {{ $salesView === $option['value'] ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-900' }} 2xl:px-5 2xl:py-2.5 2xl:text-base"
            >
              {{ $option['label'] }}
            </a>
          @endforeach
        </div>

        <button
          type="button"
          data-sales-trend-download
          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
          aria-label="Download sales trend chart"
          title="Download chart"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
        </button>
      </div>
    </div>

    <div class="px-4 py-4 sm:px-5">
        <div class="relative h-75 lg:h-75 2xl:h-80">
          <canvas id="sales-trend-chart" class="h-full w-full"></canvas>
        </div>
      <div class="mt-4 flex items-start gap-2 text-sm text-slate-500">
        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"></i>
        <p>Chart displays completed sales only. Pending, abandoned and expired bookings are excluded.</p>
      </div>
    </div>
  </section>

  <section class="mt-4 rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3.5 sm:px-5 lg:flex-row lg:items-center lg:justify-between">
      <div class="min-w-0">
        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Bookings Overview (Count)</h2>
        <p class="mt-1 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">Bookings breakdown for the selected period.</p>
      </div>

      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        <span class="hidden text-[11px] font-medium uppercase tracking-[0.16em] text-slate-400 lg:inline">Chart period</span>
        <div class="inline-flex flex-wrap rounded-2xl border border-gray-200 bg-slate-50 p-1">
          @foreach($bookingViewOptions as $option)
            <a
              href="{{ $option['url'] }}"
              @click="setBookingView('{{ $option['value'] }}', $event)"
              class="rounded-xl px-4 py-2 text-sm font-medium transition {{ $bookingView === $option['value'] ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-900' }} 2xl:px-5 2xl:py-2.5 2xl:text-base"
            >
              {{ $option['label'] }}
            </a>
          @endforeach
        </div>

        <button
          type="button"
          data-bookings-overview-download
          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
          aria-label="Download bookings overview chart"
          title="Download chart"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:px-5 xl:grid-cols-[minmax(0,1fr)_260px] 2xl:grid-cols-[minmax(0,1fr)_300px] xl:items-start">
      <div data-bookings-overview-chart x-data="chartHoverCard({{ $bookingChartWidth }}, {{ $bookingChartHeight }}, 'count')" @mouseleave="clearActive()">
        <div class="mb-3 flex flex-wrap items-center gap-3 text-[0.72rem] font-medium text-slate-600 sm:text-xs lg:text-xs">
          @foreach($bookingSeriesMeta as $series)
            <div class="inline-flex items-center gap-2">
              <span class="h-1.5 w-4 rounded-full" style="background-color: {{ $series['stroke'] }};"></span>
              <span class="sm:whitespace-nowrap">{{ $series['label'] }}</span>
            </div>
          @endforeach
        </div>

        @if($bookingOverviewChartItems->count() > 0)
          <div class="overflow-x-auto rounded-3xl bg-slate-50 p-2 sm:p-2.5">
            <div class="relative" style="min-width: {{ $bookingChartWidth }}px;">
              <svg viewBox="0 0 {{ $bookingChartWidth }} {{ $bookingChartHeight }}" class="h-68 w-full sm:h-72 2xl:h-80" role="img" aria-label="Bookings overview chart">
                @foreach(range(4, 0) as $step)
                  @php
                    $gridValue = $bookingTickStep * $step;
                    $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($gridValue / $bookingOverviewMax));
                  @endphp
                  <text x="0" y="{{ number_format($y + 4, 2, '.', '') }}" class="fill-slate-400 text-[10px] font-medium sm:text-[11px] lg:text-[11px]">{{ number_format($gridValue) }}</text>
                  <line x1="{{ $bookingChartLeft }}" y1="{{ number_format($y, 2, '.', '') }}" x2="{{ $bookingChartWidth - $bookingChartRight }}" y2="{{ number_format($y, 2, '.', '') }}" stroke="#e2e8f0" stroke-dasharray="4 6" />
                @endforeach

                @foreach($bookingSeriesMeta as $series)
                  <polyline fill="none" stroke="{{ $series['stroke'] }}" stroke-width="3.25" stroke-linecap="round" stroke-linejoin="round" points="{{ $bookingChartLine($series['key']) }}" />
                @endforeach

                @foreach($bookingOverviewChartItems as $index => $row)
                  @php
                    $x = $bookingOverviewCount === 1
                      ? ($bookingChartWidth / 2)
                      : $bookingChartLeft + (($bookingChartWidth - $bookingChartLeft - $bookingChartRight) * ($index / ($bookingOverviewCount - 1)));
                  @endphp
                  @foreach($bookingSeriesMeta as $series)
                    @php
                      $value = (int) ($row->{$series['key']} ?? 0);
                      $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($value / $bookingOverviewMax));
                      $payload = [
                        'label' => $row->label . ' - ' . $series['label'],
                        'amount' => $value,
                        'stroke' => $series['stroke'],
                        'x' => (float) number_format($x, 2, '.', ''),
                        'y' => (float) number_format($y, 2, '.', ''),
                      ];
                    @endphp
                    <circle cx="{{ number_format($x, 2, '.', '') }}" cy="{{ number_format($y, 2, '.', '') }}" r="3.25" fill="{{ $series['stroke'] }}" class="cursor-pointer" @mouseenter="setActive(@js($payload))" @focus="setActive(@js($payload))" @click="setActive(@js($payload))" tabindex="0" />
                  @endforeach

                  @if($index % $bookingLabelStep === 0 || $index === $bookingOverviewChartItems->count() - 1)
                    <text x="{{ number_format($x, 2, '.', '') }}" y="{{ $bookingChartHeight - 10 }}" text-anchor="middle" class="fill-slate-500 text-[10px] font-medium sm:text-[11px] lg:text-[11px]">{{ $row->label }}</text>
                  @endif
                @endforeach
              </svg>

              <div x-show="activePoint" x-cloak class="pointer-events-none absolute z-20 hidden min-w-32 -translate-x-1/2 -translate-y-full rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-lg md:block" :style="tooltipStyle">
                <p class="text-sm font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
                <p class="text-[11px] text-slate-500" x-text="activePoint?.label ?? ''"></p>
              </div>
            </div>
          </div>
          <div x-show="activePoint" x-cloak class="mt-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-[11px] shadow-sm md:hidden">
            <p class="text-sm font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
            <p class="text-[11px] text-slate-500" x-text="activePoint?.label ?? ''"></p>
          </div>
        @else
          <div class="flex min-h-72 items-center justify-center text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs 2xl:min-h-80">No booking trend data available.</div>
        @endif
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white xl:self-start xl:justify-self-end">
        <div class="flex items-center justify-between px-3.5 py-3">
          <p class="text-[11px] font-medium text-slate-500 sm:text-xs sm:whitespace-nowrap">Overview Total</p>
          <p class="text-2xl font-semibold leading-none text-slate-900 sm:text-[1.8rem] 2xl:text-[2rem] sm:whitespace-nowrap">{{ $fmtInt($bookingSummaryTotal) }}</p>
        </div>

        @foreach($bookingSummaryBreakdown as $card)
          <div class="flex items-center justify-between border-t border-slate-100 px-3.5 py-3">
            <p class="text-[11px] font-medium text-slate-600 sm:text-xs 2xl:text-sm sm:whitespace-nowrap">{{ $card['label'] }}</p>
            <p class="text-[11px] font-semibold {{ $card['tone'] }} sm:text-sm 2xl:text-base sm:whitespace-nowrap">{{ $card['count'] }} ({{ $card['percent'] }}%)</p>
          </div>
        @endforeach

        <div class="border-t border-slate-100 px-3.5 py-3 text-[11px] text-slate-500 sm:text-xs 2xl:px-4 2xl:py-4 2xl:text-sm">
          <p class="mt-1">Closed = cancelled, expired, and abandoned bookings.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-1 2xl:grid-cols-2 2xl:gap-5">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5 sm:px-5">
        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Recent Bookings</h2>
        <a href="{{ route('admin.bookings.index') }}" class="text-[0.72rem] font-medium text-[#8f5d54] sm:text-xs lg:text-xs">View all</a>
      </div>

      <div class="space-y-2 px-4 py-3.5 sm:px-5 lg:px-5 lg:py-3.5">
        @forelse($recentBookingCards as $booking)
          <a href="{{ $booking['href'] }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:bg-slate-50 lg:gap-4 lg:p-4 2xl:gap-4 2xl:p-4">
            <img
              src="{{ \App\Helpers\ImageHelper::src($booking['image'] ?? null) }}"
              alt="{{ $booking['name'] }}"
              class="h-14 w-14 shrink-0 rounded-xl object-cover lg:h-14 lg:w-14 2xl:h-16 2xl:w-16"
              onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
            >
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-semibold text-slate-900 sm:text-base lg:text-base lg:overflow-visible lg:whitespace-normal lg:text-clip 2xl:text-base">{{ $booking['name'] }}</p>
              @if(!empty($booking['item']))
                <p class="mt-0.5 truncate text-xs font-medium text-slate-700 sm:text-sm lg:text-sm lg:overflow-visible lg:whitespace-normal lg:text-clip 2xl:text-sm">{{ $booking['item'] }}</p>
              @endif
              <p class="mt-0.5 text-xs text-slate-500 sm:text-sm lg:text-sm lg:whitespace-normal lg:overflow-visible lg:text-clip 2xl:text-sm sm:truncate sm:whitespace-nowrap">{{ $booking['meta'] }}</p>
            </div>
            <div class="text-right">
              <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold 2xl:text-xs {{ $booking['statusTone'] }}">{{ $booking['status'] }}</span>
              <p class="mt-1.5 whitespace-nowrap text-xs font-semibold text-slate-900 sm:text-sm lg:text-sm 2xl:text-base">{{ $booking['amount'] }}</p>
            </div>
          </a>
        @empty
          <div class="flex min-h-72 items-center justify-center text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">No recent bookings to show.</div>
        @endforelse
      </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5 sm:px-5">
        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Recent Admin Activity</h2>
        <a href="{{ route('admin.settings.activity-log') }}" class="text-[0.72rem] font-medium text-[#8f5d54] sm:text-xs lg:text-xs">View all</a>
      </div>

      <div class="space-y-2.5 px-4 py-3.5 sm:px-5 lg:px-5 lg:py-3.5">
        @forelse($activityCards as $activity)
          <div class="flex items-start gap-3 lg:gap-4 2xl:gap-4">
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600 lg:h-8 lg:w-8 lg:text-xs 2xl:h-9 2xl:w-9 2xl:text-sm">{{ $activity['initials'] }}</span>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <p class="text-sm font-semibold text-slate-900 sm:text-base lg:text-base 2xl:text-base lg:whitespace-normal lg:overflow-visible lg:text-clip">{{ $activity['title'] }}</p>
                <p class="text-xs text-slate-500 sm:text-sm lg:text-sm 2xl:text-sm lg:whitespace-normal lg:overflow-visible lg:text-clip">{{ $activity['actor'] }}</p>
              </div>
              <p class="mt-1 text-xs text-slate-500 sm:text-sm lg:text-sm 2xl:text-sm lg:whitespace-normal lg:overflow-visible lg:text-clip">{{ $activity['meta'] }}</p>
            </div>
            <p class="shrink-0 whitespace-nowrap pt-0.5 text-[11px] text-slate-400 sm:text-xs lg:text-xs 2xl:text-sm">{{ $activity['time'] }}</p>
          </div>
        @empty
          <div class="flex min-h-72 items-center justify-center text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">No recent admin activity recorded.</div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-3.5 sm:px-5">
        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">Quick Actions</h2>
    </div>

    <div class="grid grid-cols-1 gap-3 px-4 py-3.5 md:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3 sm:px-5 2xl:gap-4 2xl:px-6 2xl:py-5">
      @foreach($quickActions as $action)
        <a href="{{ $action['href'] }}" class="group flex min-h-22 rounded-2xl border border-slate-200 bg-white px-5 py-4 transition hover:border-slate-300 hover:bg-slate-50 2xl:min-h-24 2xl:px-6 2xl:py-5">
          <div class="flex w-full items-center gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 {{ $action['tone'] }} 2xl:h-11 2xl:w-11">
              <i data-lucide="{{ $action['icon'] }}" class="h-4.5 w-4.5"></i>
            </span>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold leading-tight text-slate-900 lg:text-sm 2xl:text-base">{{ $action['title'] }}</p>
              <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-xs lg:text-xs 2xl:text-sm">{{ $action['description'] }}</p>
            </div>
            <i data-lucide="chevron-right" class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-1"></i>
          </div>
        </a>
      @endforeach
    </div>
  </section>

  <footer class="flex flex-col gap-2 px-1 text-[0.72rem] text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-xs lg:text-xs">
    <p class="sm:truncate sm:whitespace-nowrap">All amounts are in PHP ({{ $peso }}) and based on completed payments only.</p>
    <p class="sm:whitespace-nowrap">Last updated: {{ $lastUpdatedAt->format('M j, Y g:i A') }}</p>
  </footer>
</div>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
  <script>
    (() => {
      const svgStyleProps = [
        'fill',
        'stroke',
        'stroke-width',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-dasharray',
        'stroke-dashoffset',
        'font-family',
        'font-size',
        'font-weight',
        'letter-spacing',
        'opacity',
        'text-anchor',
        'dominant-baseline',
      ];

      const inlineSvgStyles = (sourceSvg, targetSvg) => {
        const sourceElements = [sourceSvg, ...sourceSvg.querySelectorAll('*')];
        const targetElements = [targetSvg, ...targetSvg.querySelectorAll('*')];

        sourceElements.forEach((sourceElement, index) => {
          const targetElement = targetElements[index];
          if (!targetElement) {
            return;
          }

          const computed = window.getComputedStyle(sourceElement);
          const styleParts = [];

          svgStyleProps.forEach((property) => {
            const value = computed.getPropertyValue(property);
            if (value) {
              styleParts.push(`${property}:${value}`);
            }
          });

          if (styleParts.length > 0) {
            targetElement.setAttribute('style', styleParts.join(';'));
          }
        });
      };

      const triggerDownload = (blob, filename) => {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
      };

      const dataURLToBlob = (dataUrl) => {
        const [header, data] = dataUrl.split(',');
        const mime = header.match(/data:(.*?);base64/)?.[1] || 'image/jpeg';
        const binary = atob(data || '');
        const bytes = new Uint8Array(binary.length);

        for (let i = 0; i < binary.length; i += 1) {
          bytes[i] = binary.charCodeAt(i);
        }

        return new Blob([bytes], { type: mime });
      };

      const downloadCanvasAsJpeg = (sourceCanvas, filename) => {
        if (!sourceCanvas) {
          return;
        }

        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = sourceCanvas.width;
        exportCanvas.height = sourceCanvas.height;

        const context = exportCanvas.getContext('2d');
        if (!context) {
          return;
        }

        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, exportCanvas.width, exportCanvas.height);
        context.drawImage(sourceCanvas, 0, 0);

        const finalizeDownload = (blob) => {
          if (blob) {
            triggerDownload(blob, filename);
            return;
          }

          triggerDownload(dataURLToBlob(exportCanvas.toDataURL('image/jpeg', 0.95)), filename);
        };

        if (exportCanvas.toBlob) {
          exportCanvas.toBlob(finalizeDownload, 'image/jpeg', 0.95);
        } else {
          finalizeDownload(null);
        }
      };

      const revenueExportData = @json($revenueTrendExportPayload);
      const bookingsExportData = @json($bookingOverviewExportPayload);
      const revenueExportCurrency = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
      const bookingsExportCount = new Intl.NumberFormat('en-PH');
      const revenueCategoryCurrency = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });

      if (window.Chart) {
        const revenueExportCanvas = document.createElement('canvas');
        revenueExportCanvas.width = {{ $revenueChartWidth }};
        revenueExportCanvas.height = {{ $revenueChartHeight }};

        if (revenueExportCanvas && revenueExportData.labels.length > 0) {
          if (window.__revenueTrendExportChartInstance) {
            window.__revenueTrendExportChartInstance.destroy();
          }

          window.__revenueTrendExportChartInstance = new Chart(revenueExportCanvas.getContext('2d'), {
            type: 'line',
            data: revenueExportData,
            options: {
              responsive: false,
              maintainAspectRatio: false,
              animation: false,
              plugins: {
                legend: {
                  position: 'bottom',
                  labels: {
                    color: '#334155',
                    usePointStyle: false,
                    boxWidth: 12,
                    boxHeight: 12,
                    padding: 20,
                  },
                },
                tooltip: {
                  callbacks: {
                    label(context) {
                      return `${context.dataset.label}: ${revenueExportCurrency.format(context.parsed.y ?? 0)}`;
                    },
                  },
                },
              },
              scales: {
                x: {
                  grid: {
                    display: false,
                  },
                  ticks: {
                    color: '#64748b',
                    maxRotation: 0,
                    autoSkip: true,
                  },
                },
                y: {
                  beginAtZero: true,
                  grid: {
                    color: '#e5e7eb',
                  },
                  ticks: {
                    color: '#64748b',
                    callback(value) {
                      return revenueExportCurrency.format(value);
                    },
                  },
                },
              },
              elements: {
                line: {
                  borderWidth: 3,
                },
                point: {
                  radius: 4,
                  hoverRadius: 5,
                },
              },
            },
          });
        }

        const bookingsExportCanvas = document.createElement('canvas');
        bookingsExportCanvas.width = {{ $bookingChartWidth }};
        bookingsExportCanvas.height = {{ $bookingChartHeight }};

        if (bookingsExportCanvas && bookingsExportData.labels.length > 0) {
          if (window.__bookingsOverviewExportChartInstance) {
            window.__bookingsOverviewExportChartInstance.destroy();
          }

          window.__bookingsOverviewExportChartInstance = new Chart(bookingsExportCanvas.getContext('2d'), {
            type: 'line',
            data: bookingsExportData,
            options: {
              responsive: false,
              maintainAspectRatio: false,
              animation: false,
              plugins: {
                legend: {
                  position: 'bottom',
                  labels: {
                    color: '#334155',
                    usePointStyle: true,
                    pointStyle: 'circle',
                    boxWidth: 12,
                    boxHeight: 12,
                    padding: 20,
                  },
                },
                tooltip: {
                  callbacks: {
                    label(context) {
                      return `${context.dataset.label}: ${bookingsExportCount.format(context.parsed.y ?? 0)}`;
                    },
                  },
                },
              },
              scales: {
                x: {
                  grid: {
                    display: false,
                  },
                  ticks: {
                    color: '#64748b',
                    maxRotation: 0,
                    autoSkip: true,
                  },
                },
                y: {
                  beginAtZero: true,
                  grid: {
                    color: '#e5e7eb',
                  },
                  ticks: {
                    color: '#64748b',
                    precision: 0,
                    stepSize: 1,
                    callback(value) {
                      return bookingsExportCount.format(Math.round(value));
                    },
                  },
                },
              },
              elements: {
                line: {
                  borderWidth: 3,
                },
                point: {
                  radius: 0,
                  hoverRadius: 0,
                  hitRadius: 0,
                  borderWidth: 0,
                },
              },
            },
          });
        }
      }

      const downloadRevenueCategoryAsJpeg = (items, totalAmount, filename) => {
        if (!items || !items.length) {
          return;
        }

        const canvas = document.createElement('canvas');
        canvas.width = 1200;
        canvas.height = 560;

        const context = canvas.getContext('2d');
        if (!context) {
          return;
        }

        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, canvas.width, canvas.height);

        const titleY = 58;
        const donutCenterX = 250;
        const donutCenterY = 290;
        const donutRadius = 120;
        const ringWidth = 28;
        const lineHeight = 26;
        const startAngle = -Math.PI / 2;
        const circumference = Math.PI * 2;
        let currentAngle = startAngle;

        const drawText = (text, x, y, options = {}) => {
          context.save();
          context.fillStyle = options.color || '#0f172a';
          context.font = options.font || '600 20px Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
          context.textAlign = options.align || 'left';
          context.textBaseline = options.baseline || 'alphabetic';
          context.fillText(text, x, y);
          context.restore();
        };

        const formatPercent = (value) => `${Math.round(value)}%`;

        context.save();
        context.fillStyle = '#0f172a';
        context.font = '700 26px Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        context.fillText('Revenue by Category', 56, titleY);
        context.restore();

        context.save();
        context.lineWidth = ringWidth;
        context.lineCap = 'butt';
        context.strokeStyle = '#e5e7eb';
        context.beginPath();
        context.arc(donutCenterX, donutCenterY, donutRadius, 0, circumference);
        context.stroke();
        context.restore();

        items.forEach((item) => {
          const amount = Number(item.amount || 0);
          const fraction = totalAmount > 0 ? amount / totalAmount : 0;
          const arcLength = fraction * circumference;

          context.save();
          context.strokeStyle = item.color || '#2563eb';
          context.lineWidth = ringWidth;
          context.lineCap = 'butt';
          context.beginPath();
          context.arc(donutCenterX, donutCenterY, donutRadius, currentAngle, currentAngle + arcLength);
          context.stroke();
          context.restore();

          currentAngle += arcLength;
        });

        context.save();
        context.fillStyle = '#0f172a';
        context.font = '700 28px Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        context.textAlign = 'center';
        context.fillText(revenueCategoryCurrency.format(totalAmount), donutCenterX, donutCenterY - 8);
        context.fillStyle = '#64748b';
        context.font = '500 14px Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
        context.fillText('Total Revenue', donutCenterX, donutCenterY + 18);
        context.restore();

        const legendX = 470;
        const rowStartY = 170;
        const rowGap = 86;

        items.forEach((item, index) => {
          const rowY = rowStartY + (index * rowGap);
          const percentText = formatPercent(item.percent ?? 0);
          const amountText = revenueCategoryCurrency.format(Number(item.amount || 0));

          context.save();
          context.fillStyle = item.color || '#2563eb';
          context.beginPath();
          context.arc(legendX, rowY - 6, 6, 0, circumference);
          context.fill();
          context.restore();

          drawText(item.label || '', legendX + 24, rowY, {
            font: '600 18px Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            color: '#0f172a',
          });

          drawText(percentText, 1120, rowY - 2, {
            font: '700 24px Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            color: '#0f172a',
            align: 'right',
          });

          drawText(amountText, 1120, rowY + 22, {
            font: '500 14px Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            color: '#64748b',
            align: 'right',
          });
        });

        downloadCanvasAsJpeg(canvas, filename);
      };

      const downloadSvgAsJpeg = async (wrapper, filename) => {
        const svg = wrapper?.querySelector('svg');
        if (!svg) {
          return;
        }

        const cloned = svg.cloneNode(true);
        if (!cloned.getAttribute('xmlns')) {
          cloned.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        }

        const viewBox = svg.viewBox?.baseVal;
        if (viewBox?.width && viewBox?.height) {
          cloned.setAttribute('width', String(viewBox.width));
          cloned.setAttribute('height', String(viewBox.height));
        }

        inlineSvgStyles(svg, cloned);

        const serializedSvg = new XMLSerializer().serializeToString(cloned);
        const sourceWidth = viewBox?.width || svg.clientWidth || 1600;
        const sourceHeight = viewBox?.height || svg.clientHeight || 900;
        const scale = 2;
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(sourceWidth * scale));
        canvas.height = Math.max(1, Math.round(sourceHeight * scale));

        const context = canvas.getContext('2d');
        if (!context) {
          return;
        }

        context.setTransform(scale, 0, 0, scale, 0, 0);
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, canvas.width, canvas.height);

        const drawSvgViaImageUrl = () => new Promise((resolve, reject) => {
          const svgUrl = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(serializedSvg)}`;
          const image = new Image();

          image.onload = () => {
            context.drawImage(image, 0, 0, sourceWidth, sourceHeight);
            resolve();
          };

          image.onerror = () => {
            reject(new Error('Unable to render SVG image.'));
          };

          image.src = svgUrl;
        });

        const finalizeDownload = (blob) => {
          if (blob) {
            triggerDownload(blob, filename);
            return;
          }

          triggerDownload(dataURLToBlob(canvas.toDataURL('image/jpeg', 0.95)), filename);
        };

        try {
          await drawSvgViaImageUrl();
        } catch (error) {
          return;
        }

        if (canvas.toBlob) {
          canvas.toBlob(finalizeDownload, 'image/jpeg', 0.95);
        } else {
          finalizeDownload(null);
        }
      };

      const revenueDownloadButton = document.querySelector('[data-revenue-trend-download]');
      if (revenueDownloadButton) {
        revenueDownloadButton.addEventListener('click', () => {
          if (!window.__revenueTrendExportChartInstance) {
            return;
          }

          downloadCanvasAsJpeg(window.__revenueTrendExportChartInstance.canvas, 'revenue-trend.jpg');
        });
      }

      const revenueByCategoryDownloadButton = document.querySelector('[data-revenue-by-category-download]');
      const revenueByCategoryChartWrap = document.querySelector('[data-revenue-by-category-chart]');
      if (revenueByCategoryDownloadButton && revenueByCategoryChartWrap) {
        revenueByCategoryDownloadButton.addEventListener('click', () => {
          downloadRevenueCategoryAsJpeg(@json($salesDistributionItems), {{ $salesDistributionActualTotal }}, 'revenue-category.jpg');
        });
      }

      const bookingsDownloadButton = document.querySelector('[data-bookings-overview-download]');
      if (bookingsDownloadButton) {
        bookingsDownloadButton.addEventListener('click', () => {
          if (!window.__bookingsOverviewExportChartInstance) {
            return;
          }

          downloadCanvasAsJpeg(window.__bookingsOverviewExportChartInstance.canvas, 'bookings-overview.jpg');
        });
      }

      const salesCanvas = document.getElementById('sales-trend-chart');
      if (salesCanvas && window.Chart) {
        const salesDownloadButton = document.querySelector('[data-sales-trend-download]');
        const chartData = @json($salesTrendChartPayload);
        const currency = new Intl.NumberFormat('en-PH', {
          style: 'currency',
          currency: 'PHP',
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        });

        if (window.__salesTrendChartInstance) {
          window.__salesTrendChartInstance.destroy();
        }

        window.__salesTrendChartInstance = new Chart(salesCanvas.getContext('2d'), {
          type: 'bar',
          data: chartData,
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
              duration: 900,
              easing: 'easeOutQuart',
            },
            interaction: {
              mode: 'index',
              intersect: false,
            },
            scales: {
              x: {
                stacked: true,
                grid: {
                  display: false,
                },
                ticks: {
                  color: '#64748b',
                  maxRotation: 0,
                  autoSkip: true,
                },
              },
              y: {
                stacked: true,
                beginAtZero: true,
                grid: {
                  color: '#e5e7eb',
                },
                ticks: {
                  color: '#64748b',
                  callback(value) {
                    return currency.format(value);
                  },
                },
              },
            },
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  color: '#334155',
                  usePointStyle: true,
                  pointStyle: 'rectRounded',
                  boxWidth: 12,
                  boxHeight: 12,
                  padding: 20,
                },
              },
              tooltip: {
                callbacks: {
                  label(context) {
                    return `${context.dataset.label}: ${currency.format(context.parsed.y ?? 0)}`;
                  },
                },
              },
            },
            datasets: {
              bar: {
                borderRadius: {
                  topLeft: 8,
                  topRight: 8,
                  bottomLeft: 0,
                  bottomRight: 0,
                },
                borderSkipped: 'bottom',
                maxBarThickness: 60,
                barPercentage: 1,
                categoryPercentage: 1,
              },
            },
          },
        });

        if (salesDownloadButton) {
          salesDownloadButton.addEventListener('click', () => {
            if (!window.__salesTrendChartInstance) {
              return;
            }

            downloadCanvasAsJpeg(window.__salesTrendChartInstance.canvas, 'sales-trend.jpg');
          });
        }
      }
    })();
  </script>
@endpush
@endsection
