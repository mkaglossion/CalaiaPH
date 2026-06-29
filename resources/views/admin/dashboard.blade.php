@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
@php
  $fmtMoney = fn($v) => html_entity_decode('&#8369;') . number_format((float) $v, 2);
  $fmtInt   = fn($v) => number_format((int) $v);

  $from = request('from');
  $to = request('to');
  $dateRangeParams = [];

  if (!empty($start) && !empty($end)) {
    $dateRangeParams = [
      'date_from' => optional($start)->toDateString(),
      'date_to' => optional($end)->toDateString(),
    ];
  }

  $rangeShortLabel = $range === 'custom' ? 'Custom' : ($range === 'today' ? 'Today' : ($range === '7d' ? '7 Days' : '30 Days'));
  $completedRevenueChangePercent = (float) ($completedRevenueChangePercent ?? 0);
  $completedRevenueTrendUp = $completedRevenueChangePercent >= 0;
  $completedRevenueTrendClass = $completedRevenueTrendUp ? 'text-emerald-600' : 'text-rose-600';
  $completedRevenueTrendIcon = $completedRevenueTrendUp ? 'arrow-up' : 'arrow-down';

  $revenueTiles = [
    ['label' => 'Completed Revenue', 'value' => $fmtMoney($completedRevenue), 'description' => 'From completed bookings', 'bg' => 'bg-blue-50/70', 'labelClass' => 'text-blue-600'],
    ['label' => 'Pending Revenue',   'value' => $fmtMoney($attemptAmount),   'description' => 'Potential from pending, abandoned & expired', 'bg' => 'bg-orange-50/70', 'labelClass' => 'text-orange-600'],
    ['label' => 'Refund Amount',     'value' => $fmtMoney($refundedRevenue), 'description' => 'From refunded bookings', 'bg' => 'bg-purple-50/70', 'labelClass' => 'text-purple-600'],
  ];

  $userTiles = [
    ['label' => 'Active Customers', 'value' => $fmtInt($clientUsers),  'description' => 'Users with active bookings', 'icon' => 'user', 'valueClass' => 'text-blue-600', 'iconBg' => 'bg-blue-100', 'iconClass' => 'text-blue-600'],
    ['label' => 'Verified Users',   'value' => $fmtInt($verifiedUsers), 'description' => $verifiedUsers === 0 ? 'Email verification needed' : 'Email verification completed', 'icon' => 'shield-check', 'valueClass' => 'text-emerald-600', 'iconBg' => 'bg-emerald-100', 'iconClass' => 'text-emerald-600'],
    ['label' => 'Admin Users',      'value' => $fmtInt($adminUsers),    'description' => 'System administrators', 'icon' => 'shield', 'valueClass' => 'text-purple-600', 'iconBg' => 'bg-purple-100', 'iconClass' => 'text-purple-600'],
  ];

  $bookingOverviewSeries = $bookingOverviewSeries ?? collect();
  $revenueSummarySeries = $revenueSummarySeries ?? collect();
  $bookingOverviewMax = max(1, (int) $bookingOverviewSeries->map(fn ($row) => max(
    (int) ($row->upcoming ?? 0),
    (int) ($row->recent ?? 0),
    (int) ($row->pending ?? 0),
    (int) ($row->refund ?? 0)
  ))->max());
  $bookingTickStep = max(1, (int) ceil($bookingOverviewMax / 4));
  $bookingCountTicks = $bookingOverviewMax <= 5
    ? collect(range($bookingOverviewMax, 0))
    : collect(range(0, (int) ceil($bookingOverviewMax / $bookingTickStep)))
        ->map(fn ($index) => max(0, $bookingOverviewMax - ($bookingTickStep * $index)))
        ->push(0)
        ->unique()
        ->sortDesc()
        ->values();
  $bookingAmountOverviewMax = max(1, (float) $revenueSummarySeries->map(fn ($row) => max(
    (float) ($row->completed ?? 0),
    (float) ($row->pending ?? 0),
    (float) ($row->refund ?? 0)
  ))->max());
  $bookingOverviewCount = max(1, $bookingOverviewSeries->count());
  $bookingLabelStep = max(1, (int) ceil($bookingOverviewCount / 10));
  $bookingChartWidth = 1120;
  $bookingChartHeight = 360;
  $bookingChartLeft = 64;
  $bookingChartRight = 28;
  $bookingChartTop = 28;
  $bookingChartBottom = 48;
  $bookingChartPlotHeight = $bookingChartHeight - $bookingChartTop - $bookingChartBottom;
  $bookingAxisLabel = function (float|int $value) {
    if ($value >= 1000) {
      return rtrim(rtrim(number_format($value / 1000, 2), '0'), '.') . 'K';
    }

    return number_format((int) $value);
  };
  $bookingMoneyAxisLabel = function (float|int $value) {
    $peso = html_entity_decode('&#8369;');

    if ($value >= 1000000) {
      return $peso . rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.') . 'M';
    }

    if ($value >= 1000) {
      return $peso . rtrim(rtrim(number_format($value / 1000, 1), '0'), '.') . 'K';
    }

    return $peso . number_format((int) $value);
  };
  $bookingChartLine = function (string $key) use ($bookingOverviewSeries, $bookingOverviewCount, $bookingOverviewMax, $bookingChartWidth, $bookingChartHeight, $bookingChartLeft, $bookingChartRight, $bookingChartTop, $bookingChartBottom, $bookingChartPlotHeight) {
    $points = [];
    foreach ($bookingOverviewSeries as $index => $row) {
      $value = (int) ($row->{$key} ?? 0);
      $x = $bookingOverviewCount === 1
        ? $bookingChartLeft
        : $bookingChartLeft + (($bookingChartWidth - $bookingChartLeft - $bookingChartRight) * ($index / ($bookingOverviewCount - 1)));
      $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($value / $bookingOverviewMax));
      $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    }

    return implode(' ', $points);
  };
  $bookingAmountChartLine = function (string $key) use ($revenueSummarySeries, $bookingOverviewCount, $bookingAmountOverviewMax, $bookingChartWidth, $bookingChartHeight, $bookingChartLeft, $bookingChartRight, $bookingChartTop, $bookingChartBottom, $bookingChartPlotHeight) {
    $points = [];
    foreach ($revenueSummarySeries as $index => $row) {
      $value = (float) ($row->{$key} ?? 0);
      $x = $bookingOverviewCount === 1
        ? $bookingChartLeft
        : $bookingChartLeft + (($bookingChartWidth - $bookingChartLeft - $bookingChartRight) * ($index / ($bookingOverviewCount - 1)));
      $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($value / $bookingAmountOverviewMax));
      $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    }

    return implode(' ', $points);
  };

  $salesReportCards = collect($salesReportCards ?? []);
  $salesReportSeries = collect($salesReportSeries ?? []);
  $salesDistributionSeries = collect($salesDistributionSeries ?? []);
  $salesDistributionItems = $salesDistributionSeries->map(fn ($item) => [
    'label' => $item->label,
    'amount' => (float) $item->amount,
    'count' => (int) $item->count,
    'percent' => (float) $item->percent,
    'color' => $item->color,
  ])->values();
  $salesDistributionTotal = max(1, (float) $salesDistributionSeries->sum('amount'));
  $salesDistributionRadius = 72;
  $salesDistributionCircumference = 2 * pi() * $salesDistributionRadius;
  $salesDistributionTotalAmount = (float) $salesDistributionSeries->sum('amount');
  $salesReportMax = max(1, (float) $salesReportSeries->map(fn ($row) => max(
    (float) ($row->staycation ?? 0),
    (float) ($row->food ?? 0),
    (float) ($row->activities ?? 0),
    (float) ($row->total ?? 0)
  ))->max());
  $salesReportCount = max(1, $salesReportSeries->count());
  $salesLabelStep = max(1, (int) ceil($salesReportCount / 10));
  $salesChartWidth = 1120;
  $salesChartHeight = 360;
  $salesChartLeft = 64;
  $salesChartRight = 28;
  $salesChartTop = 28;
  $salesChartBottom = 48;
  $salesChartPlotHeight = $salesChartHeight - $salesChartTop - $salesChartBottom;
  $salesMoneyAxisLabel = $bookingMoneyAxisLabel;
  $salesChartLine = function (string $key) use ($salesReportSeries, $salesReportCount, $salesReportMax, $salesChartWidth, $salesChartHeight, $salesChartLeft, $salesChartRight, $salesChartTop, $salesChartBottom, $salesChartPlotHeight) {
    $points = [];
    foreach ($salesReportSeries as $index => $row) {
      $value = (float) ($row->{$key} ?? 0);
      $x = $salesReportCount === 1
        ? $salesChartLeft
        : $salesChartLeft + (($salesChartWidth - $salesChartLeft - $salesChartRight) * ($index / ($salesReportCount - 1)));
      $y = $salesChartHeight - $salesChartBottom - ($salesChartPlotHeight * ($value / $salesReportMax));
      $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
    }

    return implode(' ', $points);
  };

  $actions = [
    [
      'href' => route('admin.bookings.index'),
      'title' => 'Manage Bookings',
      'desc' => 'View and manage all bookings',
      'bg' => 'bg-gray-50 hover:bg-gray-100',
      'iconClass' => 'text-gray-900',
      'bg' => 'bg-blue-50 hover:bg-blue-100',
      'iconClass' => 'text-blue-600',
      'path' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    ],
    [
      'href' => route('admin.users.index'),
      'title' => 'Manage Users',
      'desc' => 'View and manage user accounts',
      'bg' => 'bg-gray-50 hover:bg-gray-100',
      'iconClass' => 'text-gray-900',
      'bg' => 'bg-emerald-50 hover:bg-emerald-100',
      'iconClass' => 'text-emerald-600',
      'path' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
    ],
    [
      'href' => route('admin.bookings.inventory'),
      'title' => 'Inventory',
      'desc' => 'Check inventory levels',
      'bg' => 'bg-gray-50 hover:bg-gray-100',
      'iconClass' => 'text-gray-900',
      'bg' => 'bg-purple-50 hover:bg-purple-100',
      'iconClass' => 'text-purple-600',
      'path' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    ],
  ];
@endphp

<div class="space-y-6 lg:space-y-8 min-w-0">

  {{-- Header + Date Filter --}}
  <header x-data="dashboard(@js($range))" x-init="init()" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div class="min-w-0 flex-1">
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900">Dashboard Overview</h1>
      <p class="text-sm text-gray-500 mt-1">
        Monitor bookings, revenue, and operations performance
        @if(!empty($rangeLabel)) <span class="text-gray-400">&middot;</span> <span class="font-medium">{{ $rangeLabel }}</span> @endif
      </p>
      <p class="mt-2 text-xs text-gray-400">
        Booking and revenue metrics follow the selected date range. Users and inventory stay global.
      </p>
      <div class="mt-3">
        <a href="{{ route('admin.dashboard.export', request()->query()) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0l-4-4m4 4l4-4M5 21h14"/>
          </svg>
          <span>Export Excel</span>
        </a>
      </div>
      
      {{-- Keyboard shortcut notification --}}
      <div x-show="shortcutUsed" 
           x-cloak
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 transform translate-y-2"
           x-transition:enter-end="opacity-100 transform translate-y-0"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="opacity-100 transform translate-y-0"
           x-transition:leave-end="opacity-0 transform -translate-y-2"
           class="mt-2 text-sm text-green-600 font-medium">
        Shortcut applied!
      </div>
    </div>

    {{-- Filter (UI) --}}
    <form method="GET"
          @submit="isLoading = true"
          class="w-full lg:w-auto">
      <input type="hidden" name="sales_view" value="{{ $salesView }}">
      <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:items-center justify-start lg:justify-end">
      <div class="inline-flex max-w-full rounded-xl border border-gray-200 bg-white p-1 shadow-sm overflow-x-auto">
        @foreach([
          'today' => 'Today',
          '7d' => '7 Days',
          '30d' => '30 Days',
          'custom' => 'Custom',
        ] as $k => $label)
          <button
            type="{{ $k === 'custom' ? 'button' : 'submit' }}"
            name="range"
            value="{{ $k }}"
            @click="{{ $k === 'custom' ? 'range=\''.$k.'\'' : '' }}"
            class="px-3 py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap
              {{ $range === $k ? 'bg-[#8f5d54] text-white' : 'text-gray-600 hover:bg-gray-50' }}"
          >
            <span x-show="!isLoading">{{ $label }}</span>
            <span x-show="isLoading && range === '{{ $k }}'" x-cloak>
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
          </button>
        @endforeach
      </div>

      {{-- Custom dates show only when custom is chosen --}}
      <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center" 
           x-show="range === 'custom'" 
           x-cloak
           x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="opacity-0 transform scale-95"
           x-transition:enter-end="opacity-100 transform scale-100"
           x-transition:leave="transition ease-in duration-200"
           x-transition:leave-start="opacity-100 transform scale-100"
           x-transition:leave-end="opacity-0 transform scale-95">
        <input type="date" name="from" value="{{ $from }}"
               class="rounded-xl border-gray-200 text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54] w-full sm:w-auto" />
        <span class="text-gray-400 text-sm hidden sm:block">to</span>
        <input type="date" name="to" value="{{ $to }}"
               class="rounded-xl border-gray-200 text-sm focus:ring-[#8f5d54] focus:border-[#8f5d54] w-full sm:w-auto" />
        <button
          type="submit"
          :disabled="isLoading"
          class="rounded-xl bg-gray-900 text-white px-4 py-2 text-sm font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center gap-2 w-full sm:w-auto"
        >
          <span x-show="!isLoading">Apply</span>
          <span x-show="isLoading" x-cloak>
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
        </button>
      </div>

      {{-- Auto-refresh toggle --}}
      <div class="relative flex items-center gap-2 ml-0 sm:ml-4">
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
          <input type="checkbox" 
                 x-model="autoRefresh" 
                 @change="toggleAutoRefresh()"
                 class="rounded border-gray-300 text-[#8f5d54] focus:ring-[#8f5d54]">
          <span>Auto-refresh</span>
        </label>
        <span x-show="autoRefresh" x-cloak class="text-xs text-gray-500">(every 30s)</span>
        <button type="button" 
                class="relative text-sm text-gray-600 hover:text-[#8f5d54] ml-2 p-1 rounded hover:bg-gray-100 transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-opacity-50"
                :class="{ 'bg-[#8f5d54] text-white scale-110': shortcutUsed }"
                title="Click for keyboard shortcuts"
                @click.stop="showShortcuts = !showShortcuts"
                @mousedown="keyboardEnabled = false"
                @mouseup="keyboardEnabled = true"
                @mouseleave="keyboardEnabled = true">
          <kbd class="px-1 py-0.5 text-xs bg-gray-100 border border-gray-300 rounded text-gray-700">&#8984;</kbd>
        </button>
        <div x-show="showShortcuts" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.away="showShortcuts = false"
             class="absolute top-full left-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-xs text-gray-700 z-50 min-w-max max-w-xs">
          <div class="font-semibold mb-2 text-gray-900">Keyboard Shortcuts:</div>
          <div class="space-y-1">
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
              <div class="flex items-center gap-1">
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">1</kbd>
              </div>
              <span class="text-gray-600">&rarr; Today</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
              <div class="flex items-center gap-1">
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">2</kbd>
              </div>
              <span class="text-gray-600">&rarr; 7 Days</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
              <div class="flex items-center gap-1">
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">3</kbd>
              </div>
              <span class="text-gray-600">&rarr; 30 Days</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
              <div class="flex items-center gap-1">
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">4</kbd>
              </div>
              <span class="text-gray-600">&rarr; Custom</span>
            </div>
          </div>
        </div>
      </div>

      </div>
    </form>
  </header>

  {{-- Key Metrics --}}
  <section aria-label="Key metrics">
    @php
      $overviewCards = [
        [
          'label' => 'Active Bookings',
          'value' => $fmtInt($activeBookings),
          'description' => 'Confirmed reservations',
          'icon' => 'calendar-days',
          'href' => route('admin.bookings.index', ['status' => 'confirmed']),
          'iconBg' => 'bg-blue-50',
          'iconClass' => 'text-blue-600',
          'footerLabel' => 'View details',
          'footerHref' => route('admin.bookings.index', ['status' => 'confirmed']),
          'footerTone' => 'text-blue-600',
          'footerBg' => 'bg-blue-50/60',
        ],
        [
          'label' => 'Completed Bookings',
          'value' => $fmtInt($completedBookings),
          'description' => 'Checked-out reservations',
          'icon' => 'clipboard-check',
          'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['status' => 'completed'])),
          'iconBg' => 'bg-emerald-50',
          'iconClass' => 'text-emerald-600',
          'footerLabel' => 'View details',
          'footerHref' => route('admin.bookings.index', array_merge($dateRangeParams, ['status' => 'completed'])),
          'footerTone' => 'text-emerald-600',
          'footerBg' => 'bg-emerald-50/60',
        ],
        [
          'label' => 'Pending Booking',
          'value' => $fmtInt($paymentAttempts),
          'description' => 'Abandoned & expired',
          'icon' => 'clock-3',
          'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['payment_scope' => 'attempts'])),
          'iconBg' => 'bg-orange-50',
          'iconClass' => 'text-orange-500',
          'footerLabel' => 'View details',
          'footerHref' => route('admin.bookings.index', array_merge($dateRangeParams, ['payment_scope' => 'attempts'])),
          'footerTone' => 'text-orange-600',
          'footerBg' => 'bg-orange-50/60',
        ],
        [
          'label' => "Today's Check-ins",
          'value' => $fmtInt($todayCheckIns),
          'description' => 'Reservations scheduled today',
          'icon' => 'calendar-check-2',
          'href' => route('admin.bookings.index', array_merge($dateRangeParams, ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()])),
          'iconBg' => 'bg-blue-50',
          'iconClass' => 'text-blue-600',
          'footerLabel' => 'View details',
          'footerHref' => route('admin.bookings.index', array_merge($dateRangeParams, ['date_from' => now()->toDateString(), 'date_to' => now()->toDateString()])),
          'footerTone' => 'text-blue-600',
          'footerBg' => 'bg-blue-50/60',
        ],
      ];
    @endphp
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      @foreach($overviewCards as $card)
        <x-admin.stat-card
          :label="$card['label']"
          :value="$card['value']"
          :description="$card['description']"
          :icon="$card['icon']"
          :href="$card['href']"
          :icon-bg="$card['iconBg']"
          :icon-class="$card['iconClass']"
          :footer-label="$card['footerLabel']"
          :footer-href="$card['footerHref']"
          :footer-tone="$card['footerTone']"
          :footer-bg="$card['footerBg']"
          bg="bg-white"
        />
      @endforeach
    </div>
  </section>


  {{-- Inventory Alerts --}}
  @if($lowStockProducts > 0 || $lowStockFoods > 0)
    <section aria-label="Inventory alert">
      <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="shrink-0 mt-0.5">
            <svg class="h-5 w-5 text-yellow-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="min-w-0">
            <p class="text-sm text-yellow-800">
              <span class="font-semibold">Low Stock Alert:</span>
              @if($lowStockProducts > 0) {{ $lowStockProducts }} product(s) @endif
              @if($lowStockProducts > 0 && $lowStockFoods > 0) and @endif
              @if($lowStockFoods > 0) {{ $lowStockFoods }} food item(s) @endif
              have low stock (&le;10 units).
            </p>
            <p class="text-xs text-yellow-700 mt-1">Consider restocking soon to avoid order issues.</p>
          </div>
        </div>
      </div>
    </section>
  @endif

  <section aria-label="Revenue, user and booking overview" class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 min-w-0">
      <x-admin.section-card title="Revenue Summary" icon="banknote" :action-text="$rangeShortLabel" class="h-full">
        <div class="flex h-full flex-col justify-between gap-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($revenueTiles as $tile)
              <div class="flex min-h-50 flex-col justify-start rounded-xl {{ $tile['bg'] }} p-4 shadow-[0_1px_0_rgba(15,23,42,0.02)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] {{ $tile['labelClass'] }}">{{ $tile['label'] }}</p>
                <p class="mt-1.5 text-2xl font-semibold tracking-tight text-gray-900">{{ $tile['value'] }}</p>
                <p class="mt-2 text-sm leading-6 text-gray-500">{{ $tile['description'] }}</p>
              </div>
            @endforeach
          </div>

          <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 flex min-h-18 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
              <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-700 ring-1 ring-gray-200">
                <i data-lucide="badge-dollar-sign" class="h-5 w-5"></i>
              </span>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Total Revenue (Completed)</p>
                <p class="text-xl font-semibold tracking-tight text-gray-900">{{ $fmtMoney($completedRevenue) }}</p>
              </div>
            </div>
            <div class="text-left sm:text-right">
              <p class="inline-flex items-center gap-1 text-sm font-semibold {{ $completedRevenueTrendClass }}">
                <i data-lucide="{{ $completedRevenueTrendIcon }}" class="h-4 w-4"></i>
                <span>{{ number_format(abs($completedRevenueChangePercent), 1) }}%</span>
              </p>
              <p class="text-xs text-gray-500">{{ $completedRevenueComparisonLabel ?? 'vs previous period' }}</p>
            </div>
          </div>
        </div>
      </x-admin.section-card>

      <x-admin.section-card title="User Summary" icon="users" class="h-full">
        <div class="flex h-full flex-col justify-between gap-4">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($userTiles as $tile)
              <div class="flex min-h-50 flex-col items-center justify-start rounded-xl bg-gray-50/80 p-4 text-center shadow-[0_1px_0_rgba(15,23,42,0.02)]">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">{{ $tile['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight {{ $tile['valueClass'] }}">{{ $tile['value'] }}</p>
                <p class="mt-2 min-h-10 text-sm leading-5 text-gray-500">{{ $tile['description'] }}</p>
                <div class="mt-4 mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full {{ $tile['iconBg'] }}">
                  <i data-lucide="{{ $tile['icon'] }}" class="h-6 w-6 {{ $tile['iconClass'] }}"></i>
                </div>
              </div>
            @endforeach
          </div>

          <div class="rounded-xl border border-gray-100 bg-blue-50/30 px-4 py-3 flex min-h-18 items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                <i data-lucide="users" class="h-5 w-5"></i>
              </span>
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500">Total Users</p>
                <p class="text-xl font-semibold tracking-tight text-gray-900">{{ $fmtInt($totalUsers) }}</p>
              </div>
            </div>
          </div>
        </div>
      </x-admin.section-card>
    </div>

    <section aria-label="Sales report summary and trend" class="space-y-6">
      <x-admin.section-card
        title="Sales Report"
        subtitle="Revenue performance by category for the selected period."
        icon="chart-column"
        :action-text="$rangeShortLabel"
        class="h-full"
      >
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
          @foreach($salesReportCards as $card)
            <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500">{{ $card['label'] }}</p>
                  <p class="mt-2 text-2xl font-semibold tracking-tight text-gray-900">{{ number_format($card['count']) }}</p>
                  <p class="mt-1 text-sm text-gray-500">Count in selected range</p>
                </div>
                <div class="text-right">
                  <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-400">Total Amount</p>
                  <p class="mt-2 text-lg font-semibold text-gray-900">{{ $fmtMoney($card['amount']) }}</p>
                </div>
              </div>
              <p class="mt-4 text-xs text-gray-400">{{ $card['subtitle'] }}. Based on selected date range.</p>
            </div>
          @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3 text-sm">
          <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 font-semibold text-amber-700">
            <i data-lucide="clock-3" class="h-4 w-4"></i>
            <span>Pending revenue: {{ $fmtMoney($salesPendingRevenue) }}</span>
          </span>
          <span class="text-xs text-gray-400">
            Potential from pending, abandoned &amp; expired bookings.
          </span>
        </div>
      </x-admin.section-card>

      <x-admin.section-card
        title="{{ $salesTrendLabel }}"
      subtitle="Compare category sales across the selected date range."
        icon="trending-up"
        :action-text="$rangeShortLabel"
        class="h-full"
      >
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1">
            @foreach($salesModeOptions as $value => $label)
              <a
                href="{{ request()->fullUrlWithQuery(['sales_view' => $value]) }}"
                class="rounded-md px-3 py-1.5 text-sm font-medium transition {{ $salesView === $value ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}"
              >
                {{ $label }}
              </a>
            @endforeach
          </div>

          <p class="text-xs text-gray-500">Based on completed sales only</p>
        </div>

        @if($salesReportSeries->count() > 0)
          @php
            $salesSeriesMeta = collect([
              ['key' => 'staycation', 'label' => 'Staycation', 'stroke' => '#2563eb'],
              ['key' => 'food', 'label' => 'Food', 'stroke' => '#059669'],
              ['key' => 'activities', 'label' => 'Activities', 'stroke' => '#14b8a6'],
            ]);
          @endphp
          <div class="flex flex-wrap items-center justify-center gap-8 text-xs text-gray-600">
            @foreach($salesSeriesMeta as $series)
              <div class="inline-flex items-center gap-2">
                <span class="h-1.5 w-5 rounded-full" style="background-color: {{ $series['stroke'] }};"></span>
                <span>{{ $series['label'] }}: {{ $fmtMoney($salesReportSeries->sum($series['key'])) }}</span>
              </div>
            @endforeach
          </div>

          <div
            x-data="chartHoverCard({{ $salesChartWidth }}, {{ $salesChartHeight }})"
            @mouseleave="clearActive()"
            class="mt-5 space-y-3"
          >
            <div class="overflow-x-auto rounded-2xl bg-gray-50/40 p-3 ring-1 ring-gray-100">
              <div class="relative min-w-[640px]">
              <svg viewBox="0 0 {{ $salesChartWidth }} {{ $salesChartHeight }}" class="h-80 w-full text-gray-300 lg:h-88" role="img" aria-label="Sales trend chart">
                <defs>
                  <linearGradient id="sales-grid-line" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#e5e7eb" stop-opacity="1" />
                    <stop offset="100%" stop-color="#e5e7eb" stop-opacity="0.35" />
                  </linearGradient>
                </defs>

                @foreach([1, .75, .5, .25, 0] as $fraction)
                  @php
                    $gridValue = $salesReportMax * $fraction;
                    $y = $salesChartHeight - $salesChartBottom - ($salesChartPlotHeight * $fraction);
                  @endphp
                  <text x="0" y="{{ number_format($y + 4, 2, '.', '') }}" class="fill-gray-500 text-[12px]">{{ $salesMoneyAxisLabel($gridValue) }}</text>
                  <line x1="{{ $salesChartLeft }}" y1="{{ number_format($y, 2, '.', '') }}" x2="{{ $salesChartWidth - $salesChartRight }}" y2="{{ number_format($y, 2, '.', '') }}" stroke="url(#sales-grid-line)" stroke-width="1" />
                @endforeach

                @foreach($salesSeriesMeta as $series)
                  <polyline
                    fill="none"
                    stroke="{{ $series['stroke'] }}"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    points="{{ $salesChartLine($series['key']) }}"
                  />
                @endforeach

                @foreach($salesReportSeries as $index => $row)
                  @php
                    $x = $salesReportCount === 1
                      ? $salesChartLeft
                      : $salesChartLeft + (($salesChartWidth - $salesChartLeft - $salesChartRight) * ($index / ($salesReportCount - 1)));
                    $points = $salesSeriesMeta->map(function (array $series) use ($row) {
                      return [
                        'value' => (float) ($row->{$series['key']} ?? 0),
                        'stroke' => $series['stroke'],
                        'label' => $series['label'],
                        'key' => $series['key'],
                      ];
                    })->values();
                  @endphp
                  @foreach($points as $point)
                    @php
                      $y = $salesChartHeight - $salesChartBottom - ($salesChartPlotHeight * ($point['value'] / $salesReportMax));
                      $payload = [
                        'label' => $row->label,
                        'amount' => (float) $point['value'],
                        'stroke' => $point['stroke'],
                        'series' => $point['label'],
                        'x' => (float) number_format($x, 2, '.', ''),
                        'y' => (float) number_format($y, 2, '.', ''),
                      ];
                    @endphp
                    <circle
                      cx="{{ number_format($x, 2, '.', '') }}"
                      cy="{{ number_format($y, 2, '.', '') }}"
                      r="3.75"
                      fill="{{ $point['stroke'] }}"
                      fill-opacity="0.95"
                      class="cursor-pointer transition-transform duration-150 hover:scale-125 focus:outline-none"
                      @mouseenter="setActive(@js($payload))"
                      @focus="setActive(@js($payload))"
                      @click="setActive(@js($payload))"
                      tabindex="0"
                      role="button"
                      aria-label="{{ $row->label }} {{ $point['label'] }} {{ number_format($point['value'], 0) }}"
                    />
                  @endforeach
                @endforeach

                @foreach($salesReportSeries as $index => $row)
                  @if($index % $salesLabelStep === 0 || $index === $salesReportSeries->count() - 1)
                    @php
                      $x = $salesReportCount === 1
                        ? $salesChartLeft
                        : $salesChartLeft + (($salesChartWidth - $salesChartLeft - $salesChartRight) * ($index / ($salesReportCount - 1)));
                    @endphp
                    <text x="{{ number_format($x, 2, '.', '') }}" y="{{ $salesChartHeight - 10 }}" text-anchor="middle" class="fill-gray-500 text-[12px]">{{ $row->label }}</text>
                  @endif
                @endforeach
              </svg>
              <div
                x-show="activePoint"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="pointer-events-none absolute z-20 hidden min-w-24 max-w-[calc(100vw-1rem)] -translate-x-1/2 -translate-y-full rounded-lg border border-gray-200 bg-white/95 px-3 py-2 shadow-md backdrop-blur md:block"
                :style="tooltipStyle"
              >
                <p class="text-base font-bold leading-none text-gray-900" x-text="formatMoney(activePoint?.amount ?? 0)"></p>
                <p class="mt-1 text-xs text-gray-500" x-text="activePoint?.label ?? 'Selected day'"></p>
              </div>
            </div>
          </div>
          <div
            x-show="activePoint"
            x-cloak
            class="rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm md:hidden"
          >
            <p class="text-base font-bold leading-none text-gray-900" x-text="formatMoney(activePoint?.amount ?? 0)"></p>
            <p class="mt-1 text-xs text-gray-500" x-text="activePoint?.label ?? 'Selected day'"></p>
          </div>
        @else
          <p class="py-10 text-center text-sm text-gray-500">No sales data available</p>
        @endif
      </x-admin.section-card>
    </section>

  <x-admin.section-card
    title="Bookings Overview"
    subtitle="Booking counts by status for the selected period."
    icon="bar-chart-3"
    :action-text="$rangeShortLabel"
  >
    <div
      x-data="chartHoverCard({{ $bookingChartWidth }}, {{ $bookingChartHeight }})"
      @mouseleave="clearActive()"
      class="space-y-4"
    >
        @php
          $bookingSeriesMeta = collect([
            ['key' => 'upcoming', 'label' => 'Upcoming', 'stroke' => '#2563eb'],
            ['key' => 'recent', 'label' => 'Recent', 'stroke' => '#059669'],
            ['key' => 'pending', 'label' => 'Pending', 'stroke' => '#f59e0b'],
            ['key' => 'refund', 'label' => 'Refund', 'stroke' => '#a78bfa'],
          ]);
        @endphp

      <div class="flex flex-wrap items-center justify-center gap-3">
      <div class="flex flex-wrap items-center justify-center gap-8 text-xs text-gray-600">
          @foreach($bookingSeriesMeta as $series)
            <div class="inline-flex items-center gap-2">
              <span class="h-1.5 w-5 rounded-full" style="background-color: {{ $series['stroke'] }};"></span>
              <span>{{ $series['label'] }}: {{ number_format($bookingOverviewChartItems->sum($series['key'])) }}</span>
            </div>
          @endforeach
        </div>

      </div>

        @if($bookingOverviewChartItems->count() > 0)
          <div class="relative overflow-x-auto rounded-2xl bg-gray-50/40 p-3 ring-1 ring-gray-100">
            <div class="relative min-w-[640px]">
              <svg viewBox="0 0 {{ $bookingChartWidth }} {{ $bookingChartHeight }}" class="h-80 w-full text-gray-300 lg:h-88" role="img" aria-label="Bookings overview chart">
                <defs>
                  <linearGradient id="booking-grid-line" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#e5e7eb" stop-opacity="1" />
                    <stop offset="100%" stop-color="#e5e7eb" stop-opacity="0.35" />
                  </linearGradient>
                </defs>

                @foreach($bookingCountTicks as $gridValue)
                  @php
                    $fraction = $bookingOverviewMax > 0 ? ((int) $gridValue / $bookingOverviewMax) : 0;
                    $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * $fraction);
                  @endphp
                  <text x="0" y="{{ number_format($y + 4, 2, '.', '') }}" class="fill-gray-500 text-[12px]">{{ $bookingAxisLabel($gridValue) }}</text>
                  <line x1="{{ $bookingChartLeft }}" y1="{{ number_format($y, 2, '.', '') }}" x2="{{ $bookingChartWidth - $bookingChartRight }}" y2="{{ number_format($y, 2, '.', '') }}" stroke="url(#booking-grid-line)" stroke-width="1" />
                @endforeach

                @foreach($bookingSeriesMeta as $series)
                  <polyline
                    fill="none"
                    stroke="{{ $series['stroke'] }}"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    points="{{ $bookingChartLine($series['key']) }}"
                  />
                @endforeach

                @foreach($bookingOverviewChartItems as $index => $row)
                  @php
                    $x = $bookingOverviewCount === 1
                      ? $bookingChartLeft
                      : $bookingChartLeft + (($bookingChartWidth - $bookingChartLeft - $bookingChartRight) * ($index / ($bookingOverviewCount - 1)));
                    $points = $bookingSeriesMeta->map(function (array $series) use ($row) {
                      return [
                        'value' => (int) ($row->{$series['key']} ?? 0),
                        'stroke' => $series['stroke'],
                        'label' => $series['label'],
                      ];
                    })->values();
                  @endphp
                @foreach($points as $point)
                  @php
                    $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($point['value'] / $bookingOverviewMax));
                    $payload = [
                      'label' => $row->label,
                      'amount' => (float) ($row->total_amount ?? 0),
                      'stroke' => $point['stroke'],
                      'x' => (float) number_format($x, 2, '.', ''),
                      'y' => (float) number_format($y, 2, '.', ''),
                    ];
                  @endphp
                  <circle
                    cx="{{ number_format($x, 2, '.', '') }}"
                    cy="{{ number_format($y, 2, '.', '') }}"
                    r="3.75"
                    fill="{{ $point['stroke'] }}"
                    fill-opacity="0.95"
                    class="cursor-pointer transition-transform duration-150 hover:scale-125 focus:outline-none"
                    @mouseenter="setActive(@js($payload))"
                    @focus="setActive(@js($payload))"
                    @click="setActive(@js($payload))"
                    tabindex="0"
                    role="button"
                    aria-label="{{ $row->label }} {{ $point['label'] }} {{ $point['value'] }}"
                  />
                @endforeach
                @endforeach

                @foreach($bookingOverviewChartItems as $index => $row)
                  @if($index % $bookingLabelStep === 0 || $index === $bookingOverviewSeries->count() - 1)
                    @php
                      $x = $bookingOverviewCount === 1
                        ? $bookingChartLeft
                        : $bookingChartLeft + (($bookingChartWidth - $bookingChartLeft - $bookingChartRight) * ($index / ($bookingOverviewCount - 1)));
                    @endphp
                    <text x="{{ number_format($x, 2, '.', '') }}" y="{{ $bookingChartHeight - 10 }}" text-anchor="middle" class="fill-gray-500 text-[12px]">{{ $row->label }}</text>
                  @endif
                @endforeach
              </svg>
              <div
                x-show="activePoint"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="pointer-events-none absolute z-20 hidden min-w-24 max-w-[calc(100vw-1rem)] -translate-x-1/2 -translate-y-full rounded-lg border border-gray-200 bg-white/95 px-3 py-2 shadow-md backdrop-blur md:block"
                :style="tooltipStyle"
              >
                <p class="text-base font-bold leading-none text-gray-900" x-text="formatMoney(activePoint?.amount ?? 0)"></p>
                <p class="mt-1 text-xs text-gray-500" x-text="activePoint?.label ?? 'Selected day'"></p>
              </div>
            </div>
          </div>
          <div
            x-show="activePoint"
            x-cloak
            class="mt-3 rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm md:hidden"
          >
            <p class="text-base font-bold leading-none text-gray-900" x-text="formatMoney(activePoint?.amount ?? 0)"></p>
            <p class="mt-1 text-xs text-gray-500" x-text="activePoint?.label ?? 'Selected day'"></p>
          </div>
        @else
          <p class="py-10 text-center text-sm text-gray-500">No booking data available</p>
        @endif
      </div>

  </x-admin.section-card>

    <x-admin.section-card
      title="Sales Distribution"
      subtitle="Completed sales mix by category."
      icon="pie-chart"
      :action-text="$rangeShortLabel"
    >
        @php
          $salesDistributionVisibleItems = $salesDistributionSeries;
        @endphp
        @if($salesDistributionVisibleItems->count() > 0)
        <div x-data="salesDistributionChart(@js($salesDistributionVisibleItems))" class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,260px)_minmax(0,1fr)] lg:items-start">
          <div class="space-y-4">
            <div class="flex justify-center">
              <svg viewBox="0 0 200 200" class="h-52 w-52 sm:h-60 sm:w-60" role="img" aria-label="Sales distribution donut chart">
                <circle cx="100" cy="100" r="{{ $salesDistributionRadius }}" fill="none" stroke="#e5e7eb" stroke-width="26" />
                @php $salesDistributionOffset = 0; @endphp
                @foreach($salesDistributionVisibleItems as $index => $item)
                  @php
                    $segmentLength = ($item->amount / $salesDistributionTotal) * $salesDistributionCircumference;
                  @endphp
                  <circle
                    cx="100"
                    cy="100"
                    r="{{ $salesDistributionRadius }}"
                    fill="none"
                    stroke="{{ $item->color }}"
                    stroke-width="26"
                    stroke-linecap="round"
                    stroke-dasharray="{{ number_format($segmentLength, 2, '.', '') }} {{ number_format($salesDistributionCircumference, 2, '.', '') }}"
                    stroke-dashoffset="{{ number_format(-$salesDistributionOffset, 2, '.', '') }}"
                    transform="rotate(-90 100 100)"
                    class="cursor-pointer transition-opacity duration-200 focus:outline-none"
                    :class="activeIndex !== null && activeIndex !== {{ $index }} ? 'opacity-35' : 'opacity-100'"
                    @mouseenter="setActive({{ $index }})"
                    @mouseleave="clearActive()"
                    @focus="setActive({{ $index }})"
                    @blur="clearActive()"
                    @click="setActive({{ $index }})"
                    tabindex="0"
                    role="button"
                    aria-label="{{ $item->label }}, {{ $fmtMoney($item->amount) }}, {{ number_format($item->percent, 1) }} percent of completed sales"
                  >
                    <title>{{ $item->label }} - {{ $fmtMoney($item->amount) }} - {{ number_format($item->percent, 1) }}% of completed sales</title>
                  </circle>
                  @php $salesDistributionOffset += $segmentLength; @endphp
                @endforeach
                <text x="100" y="92" text-anchor="middle" class="fill-gray-500 text-[11px] font-semibold uppercase tracking-[0.14em]">Completed</text>
                <text x="100" y="115" text-anchor="middle" class="fill-gray-900 text-xl font-semibold">{{ $fmtMoney($salesDistributionTotalAmount) }}</text>
                <text x="100" y="135" text-anchor="middle" class="fill-gray-500 text-[11px]">Total sales</text>
              </svg>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-gray-50/80 p-4 shadow-sm">
              <div x-show="activeItem" x-cloak class="space-y-2">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400">Amount</p>
                <p class="text-base font-bold leading-none text-gray-900" x-text="formatMoney(activeItem?.amount ?? 0)"></p>
                <p class="text-xs text-gray-500" x-text="activeItem?.label ?? 'Sales mix'"></p>
              </div>

              <div x-show="!activeItem" x-cloak class="rounded-xl border border-dashed border-gray-200 bg-white p-4 text-sm text-gray-500">
                Hover a donut segment to preview the amount.
              </div>
            </div>
          </div>

          <div class="space-y-4">
            @foreach($salesDistributionVisibleItems as $index => $item)
              <button
                type="button"
                class="w-full rounded-xl border p-4 text-left transition"
                :class="activeIndex === {{ $index }} ? 'border-blue-200 bg-blue-50 shadow-sm' : 'border-gray-100 bg-gray-50/50 hover:border-gray-200 hover:bg-white'"
                @mouseenter="setActive({{ $index }})"
                @mouseleave="clearActive()"
                @focus="setActive({{ $index }})"
                @blur="clearActive()"
                @click="setActive({{ $index }})"
                :aria-pressed="activeIndex === {{ $index }} ? 'true' : 'false'"
              >
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div class="flex items-start gap-3">
                    <span class="mt-1 h-3 w-3 rounded-full shrink-0" style="background-color: {{ $item->color }};"></span>
                    <div>
                      <p class="text-sm font-semibold text-gray-900">{{ $item->label }}</p>
                      <p class="mt-1 text-xs text-gray-500">{{ number_format($item->count) }} transactions in the selected range</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">{{ $fmtMoney($item->amount) }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($item->percent, 1) }}% of completed sales</p>
                  </div>
                </div>

                <div class="mt-3 h-3 rounded-full bg-gray-200 overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-300"
                    style="width: {{ max(0, min(100, $item->percent)) }}%; background-color: {{ $item->color }};"
                  ></div>
                </div>

                <div class="mt-2 flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.12em] text-gray-400">
                  <span>Share of completed sales</span>
                  <span>{{ number_format($item->percent, 1) }}%</span>
                </div>
              </button>
            @endforeach
          </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-500">
          @foreach($salesDistributionVisibleItems as $item)
            <span class="inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1.5">
              <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $item->color }};"></span>
              <span>{{ $item->label }}: {{ $fmtMoney($item->amount) }}</span>
            </span>
          @endforeach
        </div>
      @else
        <p class="py-10 text-center text-sm text-gray-500">No completed sales available for distribution.</p>
      @endif
    </x-admin.section-card>

  </section>

  {{-- Recent Bookings + Monthly Bookings --}}
  <section aria-label="Bookings overview details">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 min-w-0">
      <x-admin.section-card title="Recent Bookings" action-text="View all" :action-href="route('admin.bookings.index')">
        @if($recentBookings->count() > 0)
          <div class="divide-y divide-gray-100">
            @foreach($recentBookings as $booking)
              <div class="py-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 sm:gap-4">
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ $booking->display_customer_name }}
                  </p>
                  <p class="text-sm text-gray-500">
                    {{ $booking->booking_type }} &middot; {{ $booking->checkin_date->format('M d, Y') }}
                  </p>
                </div>

                <div class="text-left sm:text-right shrink-0">
                  <p class="text-sm font-semibold text-gray-900">{{ $fmtMoney($booking->total_amount) }}</p>
                  <x-admin.badge :status="$booking->status" class="mt-1">
                    {{ ucfirst($booking->status) }}
                  </x-admin.badge>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-gray-500 text-center py-10">No recent bookings</p>
        @endif
      </x-admin.section-card>

      <x-admin.section-card title="Monthly Bookings (Last 12 Months)">
        @if($monthlyBookings->count() > 0)
          @php $max = (int) $monthlyBookings->max('count'); @endphp

          <div class="space-y-3">
            @foreach($monthlyBookings as $month)
              @php
                $label = \Carbon\Carbon::create($month->year, $month->month)->format('M Y');
                $pct = $max > 0 ? ($month->count / $max) * 100 : 0;
              @endphp

              <div class="flex items-center justify-between gap-4">
                <span class="text-sm text-gray-600 w-16 sm:w-24 shrink-0">{{ $label }}</span>

                <div class="flex items-center gap-3 flex-1 min-w-0">
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-[#8f5d54] h-2 rounded-full transition-all duration-300" style="width: {{ $pct }}%"></div>
                  </div>
                  <span class="text-sm font-semibold text-gray-900 w-8 text-right shrink-0">{{ $month->count }}</span>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-gray-500 text-center py-10">No booking data available</p>
        @endif
      </x-admin.section-card>
    </div>
  </section>

  {{-- System Alerts & Activity --}}
  <section aria-label="System alerts and recent activity" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    {{-- System Alerts --}}
    <x-admin.section-card
      title="System Alerts"
      subtitle="Operational notices and warnings that may need attention."
      class="lg:col-span-1 h-full"
    >
      @if($systemAlerts->count() > 0)
        <div class="space-y-4">
          @foreach($systemAlerts as $alert)
            <div class="flex items-start gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
              :style="{
                'border-color': '{{ in_array($alert->severity, ['critical']) ? '#dc2626' : '#f59e0b' }}',
                'background-color': '{{ in_array($alert->severity, ['critical']) ? '#fef2f2' : '#fffbeb' }}'
              }">
              <div class="w-2 h-2 rounded-full mt-1.5 shrink-0"
                :style="{ backgroundColor: '{{ in_array($alert->severity, ['critical']) ? '#dc2626' : '#f59e0b' }}' }"></div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $alert->type)) }}</p>
                <p class="mt-1 text-xs leading-5 text-gray-600">{{ $alert->message }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
              </div>
            </div>
          @endforeach
        </div>
        <div class="mt-5 border-t border-gray-100 pt-4 flex justify-end">
          <a href="{{ route('admin.settings.alerts') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
             View All Alerts &rarr;
          </a>
        </div>
      @else
        <div class="flex min-h-40 items-center justify-center py-8 text-center">
          <div>
            <i data-lucide="check-circle" class="mx-auto mb-2 h-8 w-8 text-green-500"></i>
            <p class="text-sm text-gray-600">All systems operational</p>
          </div>
        </div>
      @endif
    </x-admin.section-card>

    {{-- Recent Admin Activity --}}
    <x-admin.section-card
      title="Recent Admin Activity"
      subtitle="Latest admin actions recorded in the system."
      class="lg:col-span-1 h-full"
    >
      @if($recentActivity->count() > 0)
        <div class="space-y-4 text-sm">
          @foreach($recentActivity as $activity)
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <div class="mb-1 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :style="{
                        'background-color': '{{ in_array($activity->action, ['create', 'store']) ? '#dcfce7' : (in_array($activity->action, ['update', 'edit']) ? '#dbeafe' : '#fee2e2') }}',
                        'color': '{{ in_array($activity->action, ['create', 'store']) ? '#166534' : (in_array($activity->action, ['update', 'edit']) ? '#1e40af' : '#991b1b') }}'
                      }">
                      {{ ucfirst($activity->action) }}
                    </span>
                    <p class="font-semibold text-gray-900">{{ $activity->user->first_name ?? 'Admin' }}</p>
                  </div>
                  <p class="leading-5 text-gray-600">{{ $activity->description ?? ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                  <p class="mt-1 text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <div class="mt-5 border-t border-gray-100 pt-4 flex justify-end">
          <a href="{{ route('admin.settings.activity-log') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
             View Full Log &rarr;
          </a>
        </div>
      @else
        <div class="flex min-h-40 items-center justify-center py-8 text-center">
          <p class="text-sm text-gray-600">No recent activity</p>
        </div>
      @endif
    </x-admin.section-card>

  </section>
  <section aria-label="Quick actions">
    <x-admin.section-card title="Quick Actions">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($actions as $a)
          <x-admin.quick-action
            :href="$a['href']"
            :title="$a['title']"
            :desc="$a['desc']"
            :bg="$a['bg']"
            :icon-class="$a['iconClass']"
            :path="$a['path']"
          />
        @endforeach
      </div>
    </x-admin.section-card>
  </section>

</div>
@endsection
