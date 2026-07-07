@props(['action'])

<a href="{{ $action['href'] }}" class="group flex min-h-22 rounded-2xl border border-slate-200 bg-white px-5 py-4 transition hover:border-slate-300 hover:bg-slate-50 2xl:min-h-24 2xl:px-6 2xl:py-5">
  <div class="flex w-full items-center gap-3">
    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 {{ $action['tone'] }} 2xl:h-11 2xl:w-11">
      <i data-lucide="{{ $action['icon'] }}" class="h-4.5 w-4.5"></i>
    </span>
    <div class="min-w-0 flex-1">
      <p class="text-sm font-semibold leading-tight text-slate-900 lg:text-sm 2xl:text-base">{{ $action['title'] }}</p>
      <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-xs lg:text-xs 2xl:text-sm">{{ $action['description'] }}</p>
    </div>
    <i data-lucide="chevron-right" class="h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-1"></i>
  </div>
</a>
