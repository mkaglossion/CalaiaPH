@props(['booking'])

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
