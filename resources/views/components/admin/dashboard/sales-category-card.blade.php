@props([
  'card',
  'amount',
])

<div class="rounded-2xl border border-gray-200 bg-white px-6 py-6 shadow-sm 2xl:px-7 2xl:py-7">
  <div class="flex items-start gap-4">
    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $card->tone }} 2xl:h-14 2xl:w-14">
      <i data-lucide="{{ $card->icon }}" class="h-5 w-5"></i>
    </span>
    <div class="min-w-0 flex-1">
      <p class="text-[0.72rem] font-semibold text-slate-900 sm:text-xs lg:text-xs sm:truncate sm:whitespace-nowrap xl:overflow-visible xl:whitespace-normal 2xl:text-sm">{{ $card->label }}</p>
      <p class="mt-3 text-[1.1rem] font-bold leading-none tracking-tight text-slate-900 sm:text-[1.25rem] lg:text-[1.15rem] 2xl:text-[1.45rem]">{{ $amount }}</p>
      <div class="mt-3 space-y-1 text-xs text-slate-500 sm:text-xs lg:text-xs xl:text-xs 2xl:text-sm">
        <p>{{ number_format($card->percent, 0) }}% {{ $card->label === 'Refund Amount' ? 'of completed revenue' : 'of total revenue' }}</p>
        <p>{{ number_format($card->count) }} {{ \Illuminate\Support\Str::plural($card->countLabel, $card->count) }}</p>
      </div>
    </div>
  </div>
</div>
