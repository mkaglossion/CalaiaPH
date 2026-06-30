@props([
  'title' => '',
  'actionText' => null,
  'actionHref' => null,
  'icon' => null,
  'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm border border-gray-200/80 min-w-0 flex flex-col']) }}>
  <div class="min-h-[4.75rem] px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4 min-w-0">
    <div class="min-w-0">
      <div class="flex items-center gap-2 min-w-0">
        @if($icon)
          <i data-lucide="{{ $icon }}" class="w-4 h-4 text-gray-700 shrink-0"></i>
        @endif
        <h2 class="text-base font-semibold text-gray-900 truncate">{{ $title }}</h2>
      </div>
      @if($subtitle)
        <p class="mt-1 text-xs text-gray-500 truncate">{{ $subtitle }}</p>
      @endif
    </div>

    @if($actionText)
      @if($actionHref)
        <a href="{{ $actionHref }}" class="text-sm font-medium text-[#8f5d54] hover:opacity-90 whitespace-nowrap">
          {{ $actionText }}
        </a>
      @else
        <span class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-700 whitespace-nowrap">
          {{ $actionText }}
        </span>
      @endif
    @endif
  </div>

  <div class="p-4 sm:p-6 min-w-0 flex-1">
    {{ $slot }}
  </div>
</div>
