@props([
  'tile',
  'showChange' => false,
  'changeUp' => true,
  'changeLabel' => '',
])

<div class="rounded-2xl bg-slate-50/70 px-3.5 py-3.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] 2xl:px-4 2xl:py-4">
  <p class="text-[0.72rem] font-semibold {{ $tile['accent'] }} sm:text-xs lg:text-xs xl:text-xs sm:truncate sm:whitespace-nowrap 2xl:text-sm">{{ $tile['label'] }}</p>
  <div class="mt-3 flex items-center gap-2">
    <p class="text-[1.1rem] font-bold leading-none tracking-tight text-slate-900 sm:text-[1.25rem] lg:text-[1.15rem] 2xl:text-[1.45rem]">{{ $tile['value'] }}</p>
    @if($showChange)
      <span class="inline-flex items-center gap-1 whitespace-nowrap text-[11px] font-semibold sm:text-xs 2xl:text-sm {{ $changeUp ? 'text-emerald-600' : 'text-red-500' }}">
        <i data-lucide="{{ $changeUp ? 'trending-up' : 'trending-down' }}" class="h-3 w-3 sm:h-3.5 sm:w-3.5"></i>
        <span>{{ $changeLabel }}</span>
      </span>
    @endif
  </div>
  <p class="mt-3 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs xl:text-xs sm:truncate sm:whitespace-nowrap 2xl:text-sm">{{ $tile['caption'] }}</p>
  <p class="mt-1 text-xs leading-5 text-slate-500 lg:text-xs xl:text-xs 2xl:text-sm">{{ $tile['meta'] }}</p>
</div>
