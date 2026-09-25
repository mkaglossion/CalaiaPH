@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
<div x-data="dashboard(@js($range), @js($salesView), @js($bookingView))" x-init="init()" class="space-y-4 lg:space-y-5">
  <section class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm sm:px-5 lg:px-6 lg:py-5">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
      <div class="max-w-2xl">
        <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">Dashboard Overview</h1>
        <p class="mt-1 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">The date filter applies to dated reports; Upcoming, Live Pending, and Today remain current snapshots.</p>
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
        <button
          type="button"
          @click="isLoading = true; window.location.reload()"
          class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:text-sm"
          aria-label="Refresh dashboard data"
        >
          <i data-lucide="refresh-cw" class="h-4 w-4"></i>
          <span>Refresh</span>
        </button>
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

  <section aria-label="Booking highlights" class="grid grid-cols-1 items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
    @foreach($overviewCards as $card)
      <x-admin.dashboard.overview-card :card="$card" />
    @endforeach
  </section>

  @if($lowStockProducts > 0 || $lowStockFoods > 0)
    <section class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[0.72rem] text-amber-900 shadow-sm sm:px-5 sm:text-xs lg:text-xs">
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

  <x-admin.dashboard.section-card
    title="Booking Revenue Summary"
    subtitle="Revenue and refunds use selected service dates; Live Pending is a current 60-minute snapshot. Shop and standalone dining are excluded."
    icon="circle-dollar-sign"
    icon-class="h-4 w-4"
    icon-wrap-class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500"
    :meta-label="$rangeShortLabel"
    body-class="pt-0"
  >
    <div class="grid grid-cols-1 gap-3 px-4 py-3.5 sm:grid-cols-2 sm:px-5 lg:grid-cols-4 2xl:gap-4 2xl:px-6 2xl:py-5">
      @foreach($revenueTiles as $index => $tile)
        <x-admin.dashboard.revenue-tile
          :tile="$tile"
          :show-change="$index === 0"
          :change-up="$revenueChangeUp"
          :change-label="$revenueChangeLabel"
        />
      @endforeach
    </div>

    <div class="px-4 pb-4 sm:px-5 sm:pb-5">
        <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">
        <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-500">
          <i data-lucide="info" class="h-3.5 w-3.5"></i>
        </span>
        <span>Net booking revenue follows payment state: paid value remains counted after a booking cancellation until its payment is marked refunded. Gross equals net plus refunded booking value; live pending remains separate.</span>
      </div>
    </div>
  </x-admin.dashboard.section-card>

  <x-admin.dashboard.section-card
    title="Booking Sales by Category"
    subtitle="Paid booking value by category and service date, with refunded booking value reported separately."
    icon="sparkles"
    icon-class="h-5 w-5 text-slate-500"
    :meta-label="$rangeShortLabel"
    body-class="pt-0"
  >
    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5 xl:grid-cols-4">
      @foreach($salesCategoryCards as $card)
        <x-admin.dashboard.sales-category-card :card="$card" :amount="$fmtMoney($card->amount)" />
      @endforeach
    </div>
  </x-admin.dashboard.section-card>

  <section aria-label="Revenue analytics" class="grid grid-cols-1 gap-4 2xl:grid-cols-[minmax(0,1.35fr)_minmax(360px,0.65fr)] 2xl:gap-5">
    <x-admin.dashboard.section-card
      title="Net Booking Revenue Trend"
      subtitle="Bookings currently marked paid, attributed to their reservation service date."
      body-class="px-4 py-4 sm:px-5"
    >
      <x-slot:actions>
        <button
          type="button"
          data-revenue-trend-download
          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
          aria-label="Download revenue trend chart"
          title="Download chart"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
        </button>
      </x-slot:actions>

        <div x-data="chartHoverCard({{ $revenueChartWidth }}, {{ $revenueChartHeight }}, 'money')" @mouseleave="clearActive()">
          @if($revenueSummarySeries->count() > 0)
            <div class="mb-2.5 flex items-center gap-2 text-[0.72rem] font-medium text-slate-600 sm:text-xs lg:text-xs">
              <span class="h-1.5 w-5 rounded-full bg-blue-600"></span>
              <span class="sm:whitespace-nowrap">Net booking revenue ({{ $peso }})</span>
            </div>
            <div class="overflow-x-auto rounded-2xl bg-slate-50 p-2.5">
              <div class="relative" data-revenue-trend-chart style="min-width: {{ $revenueChartWidth }}px;">
                <svg viewBox="0 0 {{ $revenueChartWidth }} {{ $revenueChartHeight }}" class="h-68 w-full sm:h-72 2xl:h-80" role="img" aria-label="Net booking revenue trend chart">
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

                  <polygon fill="url(#revenue-fill)" points="{{ $revenueChartArea('net') }}" />
                  <polyline fill="none" stroke="#2563eb" stroke-width="3.25" stroke-linecap="round" stroke-linejoin="round" points="{{ $revenueChartLine('net') }}" />

                  @foreach($revenueSummarySeries as $index => $row)
                    @php
                      $x = $revenueChartCount === 1
                        ? ($revenueChartWidth / 2)
                        : $revenueChartLeft + (($revenueChartWidth - $revenueChartLeft - $revenueChartRight) * ($index / ($revenueChartCount - 1)));
                      $value = (float) ($row->net ?? 0);
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
              No booking revenue data available.
            </div>
          @endif
        </div>

    </x-admin.dashboard.section-card>

    <x-admin.dashboard.section-card
      title="Paid Booking Revenue by Category"
      subtitle="Paid booking revenue only; refunds are reported separately."
      body-class="min-w-0 grid grid-cols-1 justify-items-center gap-5 px-4 py-4 sm:px-5 md:grid-cols-[minmax(180px,220px)_minmax(0,1fr)] md:items-center md:justify-items-stretch 2xl:grid-cols-1 2xl:justify-items-center"
    >
      <x-slot:actions>
        <button
          type="button"
          data-revenue-by-category-download
          class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
          aria-label="Download paid booking revenue by category chart"
          title="Download chart"
        >
          <i data-lucide="download" class="h-4 w-4"></i>
        </button>
      </x-slot:actions>

        <div class="flex w-full min-w-0 justify-center md:justify-self-center" data-revenue-by-category-chart>
          <svg viewBox="0 0 200 200" class="h-auto w-full max-w-48 sm:max-w-52 2xl:max-w-60" role="img" aria-label="Paid booking revenue by category">
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
            <text x="100" y="118" text-anchor="middle" textLength="104" lengthAdjust="spacingAndGlyphs" class="fill-slate-500 text-[12px]">Paid Booking Revenue</text>
          </svg>
        </div>

        <div class="mx-auto w-full max-w-md min-w-0 space-y-4 md:max-w-none md:pl-2 2xl:max-w-md 2xl:pl-0">
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
    </x-admin.dashboard.section-card>
  </section>

  <x-admin.dashboard.section-card
    title="Paid Booking Revenue Trend by Category"
    subtitle="Paid booking revenue grouped by category; refunds are shown separately below zero."
    body-class="px-4 py-4 sm:px-5"
  >
    <x-slot:actions>
      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        <span class="hidden text-[11px] font-medium uppercase tracking-[0.16em] text-slate-400 lg:inline">Group by</span>
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
    </x-slot:actions>

        <div class="relative h-75 lg:h-75 2xl:h-80">
          <canvas id="sales-trend-chart" class="h-full w-full"></canvas>
        </div>
      <div class="mt-4 flex items-start gap-2 text-sm text-slate-500">
        <i data-lucide="info" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400"></i>
        <p>Bars include bookings currently marked paid. Refunds are shown separately below zero; live pending and failed payment attempts are excluded.</p>
      </div>
  </x-admin.dashboard.section-card>

  <x-admin.dashboard.section-card
    title="Booking Records Overview"
    subtitle="Counts booking records created in the selected period, grouped by their current booking and payment state."
    body-class="grid grid-cols-1 gap-4 px-4 py-4 sm:px-5 xl:grid-cols-[minmax(0,1fr)_260px] 2xl:grid-cols-[minmax(0,1fr)_300px] xl:items-start"
  >
    <x-slot:actions>
      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
        <span class="hidden text-[11px] font-medium uppercase tracking-[0.16em] text-slate-400 lg:inline">Group by</span>
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
    </x-slot:actions>

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
          <div class="overflow-x-auto rounded-2xl bg-slate-50 p-2 sm:p-2.5">
            <div class="relative" style="min-width: {{ $bookingChartWidth }}px;">
              <svg viewBox="0 0 {{ $bookingChartWidth }} {{ $bookingChartHeight }}" class="h-68 w-full sm:h-72 2xl:h-80" role="img" aria-label="Bookings overview chart">
                @foreach(range(4, 0) as $step)
                  @php
                    $gridValue = $bookingTickStep * $step;
                    $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($gridValue / $bookingAxisMax));
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
                      $y = $bookingChartHeight - $bookingChartBottom - ($bookingChartPlotHeight * ($value / $bookingAxisMax));
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
          <p class="text-[11px] font-medium text-slate-500 sm:text-xs sm:whitespace-nowrap">Classified Records</p>
          <p class="text-2xl font-semibold leading-none text-slate-900 sm:text-[1.8rem] 2xl:text-[2rem] sm:whitespace-nowrap">{{ $fmtInt($bookingSummaryTotal) }}</p>
        </div>

        @foreach($bookingSummaryBreakdown as $card)
          <div class="flex items-center justify-between border-t border-slate-100 px-3.5 py-3">
            <p class="text-[11px] font-medium text-slate-600 sm:text-xs 2xl:text-sm sm:whitespace-nowrap">{{ $card['label'] }}</p>
            <p class="text-[11px] font-semibold {{ $card['tone'] }} sm:text-sm 2xl:text-base sm:whitespace-nowrap">{{ $card['count'] }} ({{ $card['percent'] }}%)</p>
          </div>
        @endforeach

        <div class="border-t border-slate-100 px-3.5 py-3 text-[11px] text-slate-500 sm:text-xs 2xl:px-4 2xl:py-4 2xl:text-sm">
          <p>Live Pending = active payment attempts within the current payment window.</p>
          <p class="mt-1">Other / Closed = every remaining state, including cancelled, refunded, failed, stale, abandoned, or expired records.</p>
        </div>
      </div>
  </x-admin.dashboard.section-card>

  <section aria-label="Recent dashboard activity" class="grid grid-cols-1 gap-4 xl:grid-cols-2 xl:gap-5">
    <x-admin.dashboard.section-card
      title="Recent Bookings"
      action-text="View all"
      :action-href="route('admin.bookings.index')"
      body-class="space-y-2 px-4 py-3.5 sm:px-5 lg:px-5 lg:py-3.5"
    >
      @forelse($recentBookingCards as $booking)
        <x-admin.dashboard.recent-booking-row :booking="$booking" />
      @empty
        <div class="flex min-h-72 items-center justify-center text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">No recent bookings to show.</div>
      @endforelse
    </x-admin.dashboard.section-card>

    <x-admin.dashboard.section-card
      title="Recent Admin Activity"
      action-text="View all"
      :action-href="route('admin.settings.activity-log')"
      body-class="space-y-2.5 px-4 py-3.5 sm:px-5 lg:px-5 lg:py-3.5"
    >
      @forelse($activityCards as $activity)
        <x-admin.dashboard.activity-row :activity="$activity" />
      @empty
        <div class="flex min-h-72 items-center justify-center text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">No recent admin activity recorded.</div>
      @endforelse
    </x-admin.dashboard.section-card>
  </section>

  <x-admin.dashboard.section-card title="Quick Actions" body-class="pt-0">
    <div class="grid grid-cols-1 gap-3 px-4 py-4 sm:grid-cols-2 sm:px-5 xl:grid-cols-3">
      @foreach($quickActions as $action)
        <x-admin.dashboard.quick-action :action="$action" />
      @endforeach
    </div>
  </x-admin.dashboard.section-card>

  <footer class="flex flex-col gap-2 px-1 text-[0.72rem] text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:text-xs lg:text-xs">
    <p class="sm:truncate sm:whitespace-nowrap">Revenue/refunds use selected service dates; Live Pending and Upcoming are current snapshots. Booking revenue only; shop and standalone dining are excluded.</p>
    <p class="sm:whitespace-nowrap">Data generated: {{ now()->format('M j, Y g:i A') }}</p>
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
        context.fillText('Paid Booking Revenue by Category', 56, titleY);
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
        context.fillText('Paid booking revenue', donutCenterX, donutCenterY + 18);
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
