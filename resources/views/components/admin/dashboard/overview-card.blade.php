@props(['card'])

<a
  href="{{ $card['href'] }}"
  class="group flex h-full w-full flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md"
>
  <div class="flex flex-1 items-start gap-3">
    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $card['iconWrap'] }}">
      <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
    </span>
    <div class="flex min-w-0 flex-1 flex-col justify-start">
      <p class="text-xs font-semibold leading-4 text-slate-900">{{ $card['label'] }}</p>
      <p class="mt-1 text-xl font-bold leading-none tracking-tight text-slate-900">{{ $card['value'] }}</p>
      <p class="mt-1.5 text-xs leading-4 text-slate-500">{{ $card['description'] }}</p>
    </div>
  </div>

  <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs font-semibold {{ $card['linkTone'] }}">
    <span>View details</span>
    <i data-lucide="chevron-right" class="h-4 w-4 transition group-hover:translate-x-1"></i>
  </div>
</a>
