@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
  $fmtMoney = fn($v) => '₱' . number_format((float) $v, 2);
  $fmtInt   = fn($v) => number_format((int) $v);

  $from = request('from');
  $to = request('to');

  $statCards = [
    [
      'label' => 'Users',
      'value' => $fmtInt($clientUsers),
      'accent' => 'border-blue-600',
      'valueClass' => 'text-blue-600',
      'bg' => 'bg-gray-50',
      'borderColor' => 'border-blue-600',
      'meta' => [
        ['Total', $fmtInt($totalUsers)],
        ['Verified', $fmtInt($verifiedUsers)],
        ['Admins', $fmtInt($adminUsers)],
      ],
    ],
    [
      'label' => 'Total Bookings',
      'value' => $fmtInt($totalBookings),
      'accent' => 'border-green-500',
      'valueClass' => 'text-green-600',
      'metaInline' => 'Active: '.$fmtInt($activeBookings).' · Completed: '.$fmtInt($completedBookings),
    ],
    [
      'label' => 'Net Revenue',
      'value' => $fmtMoney($netRevenue),
      'accent' => 'border-yellow-500',
      'valueClass' => 'text-yellow-600',
      'meta' => array_values(array_filter([
        ['Total', $fmtMoney($totalRevenue)],
        $refundedRevenue > 0 ? ['Refunds', '-'.$fmtMoney($refundedRevenue), 'text-red-600'] : null,
        $pendingRevenue > 0 ? ['Pending', $fmtMoney($pendingRevenue), 'text-blue-600'] : null,
      ])),
    ],
    [
      'label' => 'Products & Foods',
      'value' => $fmtInt($totalProducts + $totalFoods),
      'accent' => 'border-purple-500',
      'valueClass' => 'text-purple-600',
      'metaInline' => 'Products: '.$fmtInt($totalProducts).' · Foods: '.$fmtInt($totalFoods),
    ],
  ];

  $revenueTiles = [
    ['label' => 'Completed Bookings', 'value' => $fmtMoney($completedRevenue), 'class' => 'text-green-600'],
    ['label' => 'Pending Payments',   'value' => $fmtMoney($pendingRevenue),   'class' => 'text-blue-600'],
    ['label' => 'Total Refunds',      'value' => $fmtMoney($refundedRevenue),  'class' => 'text-red-600'],
  ];

  $userTiles = [
    ['label' => 'Active Customers', 'value' => $fmtInt($clientUsers),  'class' => 'text-blue-600'],
    ['label' => 'Verified Users',   'value' => $fmtInt($verifiedUsers),'class' => 'text-green-600', 'hint' => $verifiedUsers === 0 ? 'Email verification needed' : null],
    ['label' => 'Admin Users',      'value' => $fmtInt($adminUsers),   'class' => 'text-purple-600'],
  ];

  $actions = [
    [
      'href' => route('admin.bookings.index'),
      'title' => 'Manage Bookings',
      'desc' => 'View and manage all bookings',
      'bg' => 'bg-blue-50 hover:bg-blue-100',
      'iconClass' => 'text-blue-600',
      'path' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    ],
    [
      'href' => route('admin.users.index'),
      'title' => 'Manage Users',
      'desc' => 'View and manage user accounts',
      'bg' => 'bg-green-50 hover:bg-green-100',
      'iconClass' => 'text-green-600',
      'path' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z',
    ],
    [
      'href' => route('admin.bookings.inventory'),
      'title' => 'Inventory',
      'desc' => 'Check inventory levels',
      'bg' => 'bg-purple-50 hover:bg-purple-100',
      'iconClass' => 'text-purple-600',
      'path' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    ],
  ];
@endphp

<div class="space-y-6 lg:space-y-8 min-w-0">

  {{-- Header + Date Filter --}}
  <header x-data="dashboard()" x-init="init()" class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div class="min-w-0 flex-1">
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900">Admin Dashboard</h1>
      <p class="text-sm text-gray-500 mt-1">
        Overview of users, bookings, revenue, and inventory
        @if(!empty($rangeLabel)) <span class="text-gray-400">·</span> <span class="font-medium">{{ $rangeLabel }}</span> @endif
      </p>
      
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
        ⚡ Keyboard shortcut applied!
      </div>
    </div>

    {{-- Filter (UI) --}}
    <form method="GET"
          @submit="isLoading = true"
          class="w-full lg:w-auto">
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
          <kbd class="px-1 py-0.5 text-xs bg-gray-100 border border-gray-300 rounded text-gray-700">⌘</kbd>
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
              <span class="text-gray-600">→ Today</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
              <div class="flex items-center gap-1">
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">2</kbd>
              </div>
              <span class="text-gray-600">→ 7 Days</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
              <div class="flex items-center gap-1">
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">3</kbd>
              </div>
              <span class="text-gray-600">→ 30 Days</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
              <div class="flex items-center gap-1">
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">Ctrl</kbd> + 
                <kbd class="px-1 py-0.5 text-xs bg-gray-100 border rounded">4</kbd>
              </div>
              <span class="text-gray-600">→ Custom</span>
            </div>
          </div>
        </div>
      </div>

      </div>
    </form>
  </header>

  {{-- Auto-refresh functionality --}}
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('dashboard', () => ({
        range: '{{ $range }}',
        isLoading: false,
        autoRefresh: false,
        refreshInterval: null,
        showShortcuts: false,
        shortcutUsed: false,
        keyboardEnabled: true,

        toggleAutoRefresh() {
          if (this.autoRefresh) {
            this.startAutoRefresh();
          } else {
            this.stopAutoRefresh();
          }
        },

        startAutoRefresh() {
          this.refreshInterval = setInterval(() => {
            // Only auto-refresh if not currently loading
            if (!this.isLoading) {
              window.location.reload();
            }
          }, 30000); // 30 seconds
        },

        stopAutoRefresh() {
          if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
          }
        },

        init() {
          // Keyboard shortcuts for quick filtering
          document.addEventListener('keydown', (e) => {
            // Don't trigger shortcuts if disabled or if user is typing in inputs
            if (!this.keyboardEnabled || e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.contentEditable === 'true') {
              return;
            }
            
            if (e.ctrlKey || e.metaKey) {
              let shortcutUsed = false;
              switch(e.key) {
                case '1':
                  e.preventDefault();
                  this.setRange('today');
                  shortcutUsed = true;
                  break;
                case '2':
                  e.preventDefault();
                  this.setRange('7d');
                  shortcutUsed = true;
                  break;
                case '3':
                  e.preventDefault();
                  this.setRange('30d');
                  shortcutUsed = true;
                  break;
                case '4':
                  e.preventDefault();
                  this.range = 'custom';
                  this.flashShortcut();
                  break;
              }
              
              if (shortcutUsed) {
                this.flashShortcut();
              }
            }
          });
        },

        flashShortcut() {
          this.shortcutUsed = true;
          setTimeout(() => {
            this.shortcutUsed = false;
          }, 200);
        },

        setRange(newRange) {
          this.range = newRange;
          this.isLoading = true;
          
          // Submit form for non-custom ranges
          if (newRange !== 'custom') {
            const form = document.querySelector('form');
            const input = form.querySelector('input[name="range"]') || document.createElement('input');
            input.type = 'hidden';
            input.name = 'range';
            input.value = newRange;
            if (!form.querySelector('input[name="range"]')) {
              form.appendChild(input);
            } else {
              form.querySelector('input[name="range"]').value = newRange;
            }
            form.submit();
          }
        }
      }));
    });
  </script>

  {{-- Key Metrics --}}
  <section aria-label="Key metrics">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-4 gap-4 lg:gap-6 min-w-0">
      @foreach($statCards as $c)
        <x-admin.stat-card
          :label="$c['label']"
          :value="$c['value']"
          :accent="$c['accent']"
          :value-class="$c['valueClass']"
          :bg="$c['bg'] ?? 'bg-white'"
          :border-color="$c['borderColor'] ?? 'border-gray-100'"
          :meta="$c['meta'] ?? null"
          :meta-inline="$c['metaInline'] ?? null"
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
              have low stock (≤10 units).
            </p>
            <p class="text-xs text-yellow-700 mt-1">Consider restocking soon to avoid order issues.</p>
          </div>
        </div>
      </div>
    </section>
  @endif

  {{-- Analytics --}}
  <section aria-label="Analytics tiles">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 min-w-0">

      <x-admin.section-card title="Revenue Analytics">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          @foreach($revenueTiles as $t)
            <div class="rounded-2xl bg-gray-50 p-4 text-center">
              <p class="text-xs uppercase tracking-wide text-gray-500">{{ $t['label'] }}</p>
              <p class="mt-2 text-xl font-extrabold {{ $t['class'] }}">{{ $t['value'] }}</p>
            </div>
          @endforeach
        </div>
      </x-admin.section-card>

      <x-admin.section-card title="User Analytics">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          @foreach($userTiles as $t)
            <div class="rounded-2xl bg-gray-50 p-4 text-center">
              <p class="text-xs uppercase tracking-wide text-gray-500">{{ $t['label'] }}</p>
              <p class="mt-2 text-xl font-extrabold {{ $t['class'] }}">{{ $t['value'] }}</p>
              @if(!empty($t['hint']))
                <p class="mt-1 text-xs text-orange-600">{{ $t['hint'] }}</p>
              @endif
            </div>
          @endforeach
        </div>
      </x-admin.section-card>

    </div>
  </section>

  {{-- Recent Bookings + Monthly Bookings --}}
  <section aria-label="Bookings overview">
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
                    {{ $booking->booking_type }} · {{ $booking->checkin_date->format('M d, Y') }}
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
    <x-admin.section-card title="System Alerts" class="lg:col-span-1">
      @if($systemAlerts->count() > 0)
        <div class="space-y-3">
          @foreach($systemAlerts as $alert)
            <div class="p-3 rounded-lg border-l-4 bg-white flex items-start gap-3"
              :style="{
                'border-color': '{{ in_array($alert->severity, ['critical']) ? '#dc2626' : '#f59e0b' }}',
                'background-color': '{{ in_array($alert->severity, ['critical']) ? '#fef2f2' : '#fffbeb' }}'
              }">
              <div class="w-2 h-2 rounded-full mt-1.5 shrink-0"
                :style="{ backgroundColor: '{{ in_array($alert->severity, ['critical']) ? '#dc2626' : '#f59e0b' }}' }"></div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $alert->type)) }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $alert->message }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $alert->created_at->diffForHumans() }}</p>
              </div>
            </div>
          @endforeach
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100">
          <a href="{{ route('admin.settings.alerts') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
            View All Alerts →
          </a>
        </div>
      @else
        <div class="py-8 text-center">
          <i data-lucide="check-circle" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
          <p class="text-sm text-gray-600">All systems operational</p>
        </div>
      @endif
    </x-admin.section-card>

    {{-- Recent Admin Activity --}}
    <x-admin.section-card title="Recent Activity" class="lg:col-span-1">
      @if($recentActivity->count() > 0)
        <div class="space-y-3 text-sm">
          @foreach($recentActivity as $activity)
            <div class="flex items-start justify-between pb-3 border-b border-gray-100 last:border-0">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                    :style="{
                      'background-color': '{{ in_array($activity->action, ['create', 'store']) ? '#dcfce7' : (in_array($activity->action, ['update', 'edit']) ? '#dbeafe' : '#fee2e2') }}',
                      'color': '{{ in_array($activity->action, ['create', 'store']) ? '#166534' : (in_array($activity->action, ['update', 'edit']) ? '#1e40af' : '#991b1b') }}'
                    }">
                    {{ ucfirst($activity->action) }}
                  </span>
                  <p class="text-gray-900 font-medium">{{ $activity->user->first_name ?? 'Admin' }}</p>
                </div>
                <p class="text-gray-600">{{ $activity->description ?? ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
              </div>
            </div>
          @endforeach
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100">
          <a href="{{ route('admin.settings.activity-log') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
            View Full Log →
          </a>
        </div>
      @else
        <div class="py-8 text-center">
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
