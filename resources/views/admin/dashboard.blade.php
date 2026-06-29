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
      'label' => 'Upcoming Bookings',
      'value' => $fmtInt($activeBookings),
      'description' => 'Reservations ahead',
      'icon' => 'calendar-days',
      'iconWrap' => 'bg-blue-50 text-blue-600',
      'linkTone' => 'text-blue-600',
      'href' => route('admin.bookings.index', ['status' => 'confirmed']),
    ],
    [
      'label' => 'Completed Bookings',
      'value' => $fmtInt($completedBookings),
      'description' => 'Checked-out reservations',
      'icon' => 'badge-check',
      'iconWrap' => 'bg-emerald-50 text-emerald-600',
      'linkTone' => 'text-emerald-600',
      'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['status' => 'completed'])),
    ],
    [
      'label' => 'Pending Bookings',
      'value' => $fmtInt($pendingBookings),
      'description' => 'Live unpaid attempts',
      'icon' => 'clock-3',
      'iconWrap' => 'bg-orange-50 text-orange-500',
      'linkTone' => 'text-orange-500',
      'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['payment_scope' => 'attempts', 'payment_status' => 'pending'])),
    ],
    [
      'label' => "Today's Check-ins",
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

  $categoryMeta = [
    'Staycation' => ['icon' => 'bed-double', 'tone' => 'bg-blue-50 text-blue-600', 'shareTone' => 'text-blue-600'],
    'Food & Beverage' => ['icon' => 'utensils-crossed', 'tone' => 'bg-emerald-50 text-emerald-600', 'shareTone' => 'text-emerald-600'],
    'Activities' => ['icon' => 'trees', 'tone' => 'bg-amber-50 text-amber-600', 'shareTone' => 'text-amber-600'],
    'Refunds' => ['icon' => 'rotate-ccw', 'tone' => 'bg-violet-50 text-violet-600', 'shareTone' => 'text-violet-600'],
  ];

  $salesDistributionItems = collect($salesDistributionSeries ?? [])->map(function ($item) {
    return (object) [
      'label' => $item->label,
      'amount' => (float) $item->amount,
      'count' => (int) $item->count,
      'percent' => (float) $item->percent,
      'color' => $item->color,
    ];
  })->values();

  $salesCategoryCards = $salesDistributionItems
    ->map(function ($item) use ($categoryMeta, $fmtMoney) {
      $meta = $categoryMeta[$item->label] ?? ['icon' => 'circle', 'tone' => 'bg-slate-50 text-slate-600', 'shareTone' => 'text-slate-600'];

      return [
        'label' => $item->label . ' Revenue',
        'value' => $fmtMoney($item->amount),
        'share' => number_format($item->percent, 0) . '% of total revenue',
        'count' => number_format($item->count) . ' transactions',
        'icon' => $meta['icon'],
        'tone' => $meta['tone'],
        'shareTone' => $meta['shareTone'],
      ];
    })
    ->push([
      'label' => 'Refunds',
      'value' => $fmtMoney($refundedRevenue),
      'share' => ($completedRevenue > 0 ? number_format(($refundedRevenue / max(1, $completedRevenue)) * 100, 0) : '0') . '% of completed revenue',
      'count' => number_format($refundedBookings) . ' refunded bookings',
      'icon' => $categoryMeta['Refunds']['icon'],
      'tone' => $categoryMeta['Refunds']['tone'],
      'shareTone' => $categoryMeta['Refunds']['shareTone'],
    ])
    ->values();

  $salesDistributionTotal = max(1, (float) $salesDistributionItems->sum('amount'));
  $salesDistributionActualTotal = (float) $salesDistributionItems->sum('amount');
  $salesDistributionRadius = 72;
  $salesDistributionCircumference = 2 * pi() * $salesDistributionRadius;

  $salesViewOptions = collect($salesModeOptions ?? [])->map(function ($label, $value) {
    return [
      'label' => $label,
      'value' => $value,
      'url' => request()->fullUrlWithQuery(['sales_view' => $value]),
    ];
  })->values();

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
  $bookingLabelStep = max(1, (int) ceil($bookingOverviewCount / 8));
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
    ['key' => 'upcoming', 'label' => 'Upcoming', 'stroke' => '#2563eb', 'tone' => 'text-blue-600'],
    ['key' => 'completed', 'label' => 'Completed', 'stroke' => '#22c55e', 'tone' => 'text-emerald-600'],
    ['key' => 'pending', 'label' => 'Live Pending', 'stroke' => '#f97316', 'tone' => 'text-orange-500'],
    ['key' => 'cancelled', 'label' => 'Closed', 'stroke' => '#ef4444', 'tone' => 'text-red-500'],
  ]);

  $bookingDistributionCounts = [
    'completed' => max(0, (int) ($summaryCompletedBookings ?? 0)),
    'pending' => max(0, (int) ($pendingBookings ?? 0)),
    'cancelled' => max(0, (int) ($summaryClosedBookings ?? 0)),
  ];
  $bookingSummaryTotal = max(0, (int) ($summaryTotalBookings ?? array_sum($bookingDistributionCounts)));
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
      'title' => 'New Booking',
      'description' => 'Create a new reservation',
      'href' => route('admin.bookings.index'),
      'icon' => 'calendar-days',
      'tone' => 'text-blue-600',
    ],
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
    [
      'title' => 'Sales Report',
      'description' => 'View detailed sales report',
      'href' => route('admin.reports.index'),
      'icon' => 'chart-column',
      'tone' => 'text-slate-700',
    ],
  ];
@endphp

<div x-data="dashboard(@js($range))" x-init="init()" class="space-y-4 lg:space-y-5">
  <section class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Dashboard Overview</h1>
      <p class="mt-1 text-sm text-slate-500">Monitor your bookings, revenue, and overall performance.</p>
    </div>

    <form method="GET" @submit="isLoading = true" class="flex w-full flex-col gap-3 xl:w-auto xl:items-end">
      <input type="hidden" name="sales_view" value="{{ $salesView }}">

      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative">
          <div class="pointer-events-none inline-flex min-w-55 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-sm">
            <i data-lucide="calendar-days" class="h-4 w-4 text-slate-500"></i>
            <span class="sm:truncate sm:whitespace-nowrap">{{ $rangeDisplay }}</span>
            <i data-lucide="chevron-down" class="ml-auto h-4 w-4 text-slate-400"></i>
          </div>
          <select
            name="range"
            x-model="range"
            @change="range === 'custom' ? null : $el.form.submit()"
            class="absolute inset-0 cursor-pointer opacity-0"
          >
            @foreach($rangeOptions as $value => $label)
              <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <a
          href="{{ route('admin.dashboard.export', request()->query()) }}"
          class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
          <span>Export</span>
        </a>
      </div>

      <div
        x-show="range === 'custom'"
        x-cloak
        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center"
      >
        <input
          type="date"
          name="from"
          value="{{ $from }}"
          class="rounded-xl border-slate-200 text-sm text-slate-700 focus:border-[#8f5d54] focus:ring-[#8f5d54]"
        >
        <input
          type="date"
          name="to"
          value="{{ $to }}"
          class="rounded-xl border-slate-200 text-sm text-slate-700 focus:border-[#8f5d54] focus:ring-[#8f5d54]"
        >
        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
        >
          Apply
        </button>
      </div>
    </form>
  </section>

  <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach($overviewCards as $card)
      <a
        href="{{ $card['href'] }}"
        class="group rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md"
      >
        <div class="flex items-start gap-4">
          <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $card['iconWrap'] }}">
            <i data-lucide="{{ $card['icon'] }}" class="h-6 w-6"></i>
          </span>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-900 sm:truncate sm:whitespace-nowrap">{{ $card['label'] }}</p>
            <p class="mt-2 whitespace-nowrap text-[1.65rem] font-bold leading-none text-slate-900 sm:text-[1.8rem]">{{ $card['value'] }}</p>
            <p class="mt-2 text-[0.95rem] text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $card['description'] }}</p>
          </div>
        </div>

        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3 text-sm font-semibold {{ $card['linkTone'] }}">
          <span>View details</span>
          <i data-lucide="chevron-right" class="h-4 w-4 transition group-hover:translate-x-1"></i>
        </div>
      </a>
    @endforeach
  </section>

  @if($lowStockProducts > 0 || $lowStockFoods > 0)
    <section class="rounded-3xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm">
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
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
      <div class="flex items-center gap-2">
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500">
          <i data-lucide="circle-dollar-sign" class="h-4 w-4"></i>
        </span>
        <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Revenue Summary</h2>
      </div>
      <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm">
        <span class="sm:whitespace-nowrap">{{ $rangeShortLabel }}</span>
        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
      </span>
    </div>

    <div class="grid grid-cols-1 gap-3 px-4 py-4 sm:grid-cols-2 sm:px-5 lg:grid-cols-4">
      @foreach($revenueTiles as $index => $tile)
        <div class="rounded-2xl bg-slate-50/70 px-4 py-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)]">
          <p class="text-sm font-semibold {{ $tile['accent'] }} sm:truncate sm:whitespace-nowrap">{{ $tile['label'] }}</p>
          <div class="mt-3 flex items-center gap-2">
            <p class="text-[1.65rem] font-bold leading-none tracking-tight text-slate-900 sm:text-[1.8rem]">{{ $tile['value'] }}</p>
            @if($index === 0)
              <span class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-semibold sm:text-sm {{ $revenueChangeUp ? 'text-emerald-600' : 'text-red-500' }}">
                <i data-lucide="{{ $revenueChangeUp ? 'trending-up' : 'trending-down' }}" class="h-3 w-3 sm:h-3.5 sm:w-3.5"></i>
                <span>{{ $revenueChangeLabel }}</span>
              </span>
            @endif
          </div>
          <p class="mt-3 text-sm text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $tile['caption'] }}</p>
          <p class="mt-1 text-[0.95rem] leading-6 text-slate-500">{{ $tile['meta'] }}</p>
        </div>
      @endforeach
    </div>

    <div class="px-4 pb-4 sm:px-5 sm:pb-5">
      <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-500">
          <i data-lucide="info" class="h-3.5 w-3.5"></i>
        </span>
        <span>Pending revenue is not included in Total Revenue.</span>
      </div>
    </div>
  </section>

  <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
      <div class="flex items-center gap-2">
        <i data-lucide="sparkles" class="h-5 w-5 text-slate-500"></i>
        <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Sales Report by Category</h2>
      </div>
      <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700">
        <span class="sm:whitespace-nowrap">{{ $rangeShortLabel }}</span>
        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
      </span>
    </div>

    <div class="grid grid-cols-1 gap-4 px-4 py-4 md:grid-cols-2 xl:grid-cols-4 sm:px-5">
      @foreach($salesCategoryCards as $card)
        <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="flex items-start gap-4">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $card['tone'] }}">
              <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
            </span>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-slate-900 sm:truncate sm:whitespace-nowrap">{{ $card['label'] }}</p>
              <p class="mt-2.5 whitespace-nowrap text-[1.65rem] font-bold leading-none text-slate-900 sm:text-[1.8rem]">{{ $card['value'] }}</p>
              <p class="mt-3 text-[0.95rem] text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $card['share'] }}</p>
              <p class="mt-1 text-[0.95rem] text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $card['count'] }}</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
        <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Revenue Trend</h2>
        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700">
          <select onchange="window.location=this.value" class="border-0 bg-transparent pr-5 text-sm font-medium text-slate-700 focus:ring-0">
            @foreach($salesViewOptions as $option)
              <option value="{{ $option['url'] }}" @selected($salesView === $option['value'])>{{ $option['label'] }}</option>
            @endforeach
          </select>
        </label>
      </div>

      <div x-data="chartHoverCard({{ $revenueChartWidth }}, {{ $revenueChartHeight }}, 'money')" @mouseleave="clearActive()" class="px-4 py-4 sm:px-5">
        @if($revenueSummarySeries->count() > 0)
          <div class="mb-2.5 flex items-center gap-2 text-xs font-medium text-slate-600">
            <span class="h-1.5 w-5 rounded-full bg-blue-600"></span>
            <span class="sm:whitespace-nowrap">Revenue ({{ $peso }})</span>
          </div>
          <div class="overflow-x-auto rounded-3xl bg-[linear-gradient(180deg,#f8fbff_0%,#f8fafc_100%)] p-2.5">
            <div class="relative" style="min-width: {{ $revenueChartWidth }}px;">
              <svg viewBox="0 0 {{ $revenueChartWidth }} {{ $revenueChartHeight }}" class="h-72 w-full" role="img" aria-label="Revenue trend chart">
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
                  <text x="0" y="{{ number_format($y + 4, 2, '.', '') }}" class="fill-slate-400 text-[13px] font-medium">{{ $fmtCompactMoney($gridValue) }}</text>
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
                    <text x="{{ number_format($x, 2, '.', '') }}" y="{{ $revenueChartHeight - 12 }}" text-anchor="middle" class="fill-slate-500 text-[13px] font-medium">{{ $row->label }}</text>
                  @endif
                @endforeach
              </svg>

              <div x-show="activePoint" x-cloak class="pointer-events-none absolute z-20 hidden min-w-28 -translate-x-1/2 -translate-y-full rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-lg md:block" :style="tooltipStyle">
                <p class="text-base font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
                <p class="text-xs text-slate-500" x-text="activePoint?.label ?? ''"></p>
              </div>
            </div>
          </div>
          <div x-show="activePoint" x-cloak class="mt-2.5 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm md:hidden">
            <p class="font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
            <p class="text-xs text-slate-500" x-text="activePoint?.label ?? ''"></p>
          </div>
        @else
          <div class="flex min-h-72 items-center justify-center text-sm text-slate-500">No revenue data available.</div>
        @endif
      </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
        <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Sales Distribution</h2>
      </div>

      <div class="grid grid-cols-1 gap-5 px-4 py-5 sm:px-5 lg:grid-cols-[208px_minmax(0,1fr)] lg:items-center">
        <div class="flex justify-center lg:justify-self-end lg:pr-1">
          <svg viewBox="0 0 200 200" class="h-50 w-50 lg:h-54 lg:w-54" role="img" aria-label="Sales distribution">
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

        <div class="space-y-5 lg:pl-2">
          @foreach($salesDistributionItems as $item)
            <div class="grid grid-cols-[16px_minmax(0,1fr)_auto] items-start gap-3">
              <span class="mt-1 h-3 w-3 rounded-full" style="background-color: {{ $item->color }};"></span>
              <div class="min-w-0">
                <p class="font-medium text-slate-800 sm:truncate sm:whitespace-nowrap">{{ $item->label }}</p>
              </div>
              <div class="text-right">
                <p class="whitespace-nowrap text-[1.4rem] font-semibold leading-none text-slate-900">{{ number_format($item->percent, 0) }}%</p>
                <p class="mt-1.5 whitespace-nowrap text-[17px] font-medium leading-none text-slate-600">{{ $fmtMoney($item->amount) }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
      <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Bookings Overview (Count)</h2>
      <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700">
        <select onchange="window.location=this.value" class="border-0 bg-transparent pr-5 text-sm font-medium text-slate-700 focus:ring-0">
          @foreach($salesViewOptions as $option)
            <option value="{{ $option['url'] }}" @selected($salesView === $option['value'])>{{ $option['label'] }}</option>
          @endforeach
        </select>
      </label>
    </div>

    <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:px-5 xl:grid-cols-[minmax(0,1fr)_240px]">
      <div x-data="chartHoverCard({{ $bookingChartWidth }}, {{ $bookingChartHeight }}, 'count')" @mouseleave="clearActive()">
        <div class="mb-4 flex flex-wrap items-center gap-5 text-xs font-medium text-slate-600">
          @foreach($bookingSeriesMeta as $series)
            <div class="inline-flex items-center gap-2">
              <span class="h-2 w-5 rounded-full" style="background-color: {{ $series['stroke'] }};"></span>
              <span class="sm:whitespace-nowrap">{{ $series['label'] }}</span>
            </div>
          @endforeach
        </div>

        @if($bookingOverviewChartItems->count() > 0)
          <div class="overflow-x-auto rounded-3xl bg-slate-50 p-3">
            <div class="relative min-w-190">
              <svg viewBox="0 0 {{ $bookingChartWidth }} {{ $bookingChartHeight }}" class="h-80 w-full" role="img" aria-label="Bookings overview chart">
                @foreach(range(4, 0) as $step)
                  @php
                    $gridValue = $bookingTickStep * $step;
                    $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($gridValue / $bookingOverviewMax));
                  @endphp
                  <text x="0" y="{{ number_format($y + 4, 2, '.', '') }}" class="fill-slate-400 text-[12px]">{{ number_format($gridValue) }}</text>
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
                    <circle cx="{{ number_format($x, 2, '.', '') }}" cy="{{ number_format($y, 2, '.', '') }}" r="3.6" fill="{{ $series['stroke'] }}" class="cursor-pointer" @mouseenter="setActive(@js($payload))" @focus="setActive(@js($payload))" @click="setActive(@js($payload))" tabindex="0" />
                  @endforeach

                  @if($index % $bookingLabelStep === 0 || $index === $bookingOverviewChartItems->count() - 1)
                    <text x="{{ number_format($x, 2, '.', '') }}" y="{{ $bookingChartHeight - 10 }}" text-anchor="middle" class="fill-slate-500 text-[12px]">{{ $row->label }}</text>
                  @endif
                @endforeach
              </svg>

              <div x-show="activePoint" x-cloak class="pointer-events-none absolute z-20 hidden min-w-32 -translate-x-1/2 -translate-y-full rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-lg md:block" :style="tooltipStyle">
                <p class="text-base font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
                <p class="text-xs text-slate-500" x-text="activePoint?.label ?? ''"></p>
              </div>
            </div>
          </div>
          <div x-show="activePoint" x-cloak class="mt-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm md:hidden">
            <p class="font-bold text-slate-900" x-text="formatValue(activePoint?.amount ?? 0)"></p>
            <p class="text-xs text-slate-500" x-text="activePoint?.label ?? ''"></p>
          </div>
        @else
          <div class="flex min-h-80 items-center justify-center text-sm text-slate-500">No booking trend data available.</div>
        @endif
      </div>

      <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between px-4 py-4">
          <p class="text-sm font-medium text-slate-500 sm:whitespace-nowrap">Total Bookings</p>
          <p class="text-[1.7rem] font-semibold leading-none text-slate-900 sm:whitespace-nowrap">{{ $fmtInt($bookingSummaryTotal) }}</p>
        </div>

        @foreach($bookingSummaryBreakdown as $card)
          <div class="flex items-center justify-between border-t border-slate-100 px-4 py-4">
            <p class="text-sm font-medium text-slate-600 sm:whitespace-nowrap">{{ $card['label'] }}</p>
            <p class="text-lg font-semibold {{ $card['tone'] }} sm:whitespace-nowrap">{{ $card['count'] }} ({{ $card['percent'] }}%)</p>
          </div>
        @endforeach

        <div class="border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
          <p>Live Pending = active unpaid attempts only.</p>
          <p class="mt-1">Closed = cancelled, expired, and abandoned bookings.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
        <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Recent Bookings</h2>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm font-medium text-[#8f5d54]">View all</a>
      </div>

      <div class="space-y-3 px-4 py-4 sm:px-5">
        @forelse($recentBookingCards as $booking)
          <a href="{{ $booking['href'] }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:bg-slate-50">
            <img
              src="{{ \App\Helpers\ImageHelper::src($booking['image'] ?? null) }}"
              alt="{{ $booking['name'] }}"
              class="h-14 w-14 shrink-0 rounded-xl object-cover"
              onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
            >
            <div class="min-w-0 flex-1">
              <p class="truncate font-semibold text-slate-900">{{ $booking['name'] }}</p>
              @if(!empty($booking['item']))
                <p class="mt-0.5 truncate text-sm font-medium text-slate-700">{{ $booking['item'] }}</p>
              @endif
              <p class="mt-0.5 text-sm text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $booking['meta'] }}</p>
            </div>
            <div class="text-right">
              <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $booking['statusTone'] }}">{{ $booking['status'] }}</span>
              <p class="mt-1.5 whitespace-nowrap text-sm font-semibold text-slate-900">{{ $booking['amount'] }}</p>
            </div>
          </a>
        @empty
          <div class="flex min-h-72 items-center justify-center text-sm text-slate-500">No recent bookings to show.</div>
        @endforelse
      </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
      <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
        <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Recent Admin Activity</h2>
        <a href="{{ route('admin.settings.activity-log') }}" class="text-sm font-medium text-[#8f5d54]">View all</a>
      </div>

      <div class="space-y-4 px-4 py-4 sm:px-5">
        @forelse($activityCards as $activity)
          <div class="flex items-start gap-3">
            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-600">{{ $activity['initials'] }}</span>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <p class="font-semibold text-slate-900 sm:truncate sm:whitespace-nowrap">{{ $activity['title'] }}</p>
                <p class="text-sm text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $activity['actor'] }}</p>
              </div>
              <p class="mt-1 text-sm text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $activity['meta'] }}</p>
            </div>
            <p class="shrink-0 whitespace-nowrap text-xs text-slate-400">{{ $activity['time'] }}</p>
          </div>
        @empty
          <div class="flex min-h-72 items-center justify-center text-sm text-slate-500">No recent admin activity recorded.</div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-4 sm:px-5">
      <h2 class="text-lg font-semibold text-slate-900 sm:whitespace-nowrap">Quick Actions</h2>
    </div>

    <div class="grid grid-cols-1 gap-4 px-4 py-4 sm:grid-cols-2 xl:grid-cols-5 sm:px-5">
      @foreach($quickActions as $action)
        <a href="{{ $action['href'] }}" class="group rounded-2xl border border-slate-200 bg-white px-4 py-4 transition hover:border-slate-300 hover:bg-slate-50">
          <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 {{ $action['tone'] }}">
              <i data-lucide="{{ $action['icon'] }}" class="h-5 w-5"></i>
            </span>
            <div class="min-w-0 flex-1">
              <p class="font-semibold text-slate-900 sm:truncate sm:whitespace-nowrap">{{ $action['title'] }}</p>
              <p class="mt-1 text-sm text-slate-500 sm:truncate sm:whitespace-nowrap">{{ $action['description'] }}</p>
            </div>
            <i data-lucide="chevron-right" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-1"></i>
          </div>
        </a>
      @endforeach
    </div>
  </section>

  <footer class="flex flex-col gap-2 px-1 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
    <p class="sm:truncate sm:whitespace-nowrap">All amounts are in PHP ({{ $peso }}) and based on completed payments only.</p>
    <p class="sm:whitespace-nowrap">Last updated: {{ $lastUpdatedAt->format('M j, Y g:i A') }}</p>
  </footer>
</div>
@endsection