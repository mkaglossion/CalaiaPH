@props([
  'title' => '',
  'subtitle' => null,
  'icon' => null,
  'iconClass' => 'h-5 w-5 text-slate-500',
  'iconWrapClass' => null,
  'actionText' => null,
  'actionHref' => null,
  'metaLabel' => null,
  'bodyClass' => 'p-4 sm:p-5',
])

<section {{ $attributes->merge(['class' => 'rounded-3xl border border-slate-200 bg-white shadow-sm']) }}>
  <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
    <div class="min-w-0">
      <div class="flex min-w-0 flex-wrap items-center gap-2">
        @if($icon)
          @if($iconWrapClass)
            <span class="{{ $iconWrapClass }}">
              <i data-lucide="{{ $icon }}" class="{{ $iconClass }} shrink-0"></i>
            </span>
          @else
            <i data-lucide="{{ $icon }}" class="{{ $iconClass }} shrink-0"></i>
          @endif
        @endif

        <h2 class="text-base font-semibold leading-tight text-slate-900 sm:text-lg lg:text-xl 2xl:text-xl sm:whitespace-nowrap">{{ $title }}</h2>
      </div>

      @if($subtitle)
        <p class="mt-1 text-[0.72rem] text-slate-500 sm:text-xs lg:text-xs">{{ $subtitle }}</p>
      @endif
    </div>

    @isset($actions)
      {{ $actions }}
    @elseif($actionText && $actionHref)
      <a href="{{ $actionHref }}" class="text-[0.72rem] font-medium text-[#8f5d54] sm:text-xs lg:text-xs">{{ $actionText }}</a>
    @elseif($metaLabel)
      <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm sm:text-sm 2xl:text-base">
        <span class="sm:whitespace-nowrap">{{ $metaLabel }}</span>
        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-400"></i>
      </span>
    @endif
  </div>

  <div class="{{ $bodyClass }}">
    {{ $slot }}
  </div>
</section>
