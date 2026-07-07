@props(['activity'])

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
