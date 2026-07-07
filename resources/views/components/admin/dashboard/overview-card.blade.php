@props(['card'])

<a
  href="{{ $card['href'] }}"
  class="group flex h-full w-full flex-col rounded-3xl border border-slate-200 bg-white p-3 shadow-sm transition hover:border-slate-300 hover:shadow-md xl:p-3 2xl:p-4"
>
  <div class="flex flex-1 items-start gap-3 xl:gap-2.5 2xl:gap-4">
    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $card['iconWrap'] }} xl:h-10 xl:w-10 2xl:h-14 2xl:w-14">
      <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5 xl:h-4.5 xl:w-4.5 2xl:h-6 2xl:w-6"></i>
    </span>
    <div class="flex min-w-0 flex-1 flex-col justify-start xl:min-h-18 2xl:min-h-24">
      <p class="text-[0.72rem] font-semibold leading-4 text-slate-900 sm:text-xs lg:text-xs xl:text-xs 2xl:text-sm 2xl:leading-5 wrap-break-word">{{ $card['label'] }}</p>
      <p class="mt-1 text-[1.1rem] font-bold leading-none text-slate-900 sm:text-[1.25rem] lg:text-[1.15rem] xl:text-[1.15rem] 2xl:text-[1.5rem]">{{ $card['value'] }}</p>
      <p class="mt-1 text-[0.72rem] leading-4 text-slate-500 sm:text-xs lg:text-xs xl:text-xs 2xl:text-sm 2xl:leading-5 wrap-break-word">{{ $card['description'] }}</p>
    </div>
  </div>

  <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-2.5 text-xs font-semibold sm:text-sm xl:mt-2.5 xl:pt-1.5 2xl:mt-4 2xl:pt-3 {{ $card['linkTone'] }}">
    <span>View details</span>
    <i data-lucide="chevron-right" class="h-4 w-4 transition group-hover:translate-x-1"></i>
  </div>
</a>
