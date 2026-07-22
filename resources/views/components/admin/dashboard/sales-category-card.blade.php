@props([
  'card',
  'amount',
])

<div class="h-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
  <div class="flex items-start gap-4">
    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card->tone }}">
      <i data-lucide="{{ $card->icon }}" class="h-5 w-5"></i>
    </span>
    <div class="min-w-0 flex-1">
      <p class="text-xs font-semibold text-slate-900">{{ $card->label }}</p>
      <p class="mt-2.5 text-xl font-bold leading-none tracking-tight text-slate-900">{{ $amount }}</p>
      <div class="mt-3 space-y-1 text-xs text-slate-500">
        <p>{{ number_format($card->percent, 0) }}% {{ $card->label === 'Refund Amount' ? 'of gross booking revenue' : 'of net booking revenue' }}</p>
        <p>{{ number_format($card->count) }} {{ \Illuminate\Support\Str::plural($card->countLabel, $card->count) }}</p>
      </div>
    </div>
  </div>
</div>
