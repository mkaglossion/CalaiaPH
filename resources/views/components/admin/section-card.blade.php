@props([
  'title' => '',
  'actionText' => null,
  'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl shadow-sm border border-gray-100 min-w-0']) }}>
  <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4 min-w-0">
    <h2 class="text-base font-semibold text-gray-900 truncate">{{ $title }}</h2>

    @if($actionText && $actionHref)
      <a href="{{ $actionHref }}" class="text-sm font-medium text-[#8f5d54] hover:opacity-90 whitespace-nowrap">
        {{ $actionText }}
      </a>
    @endif
  </div>

  <div class="p-4 sm:p-6 min-w-0">
    {{ $slot }}
  </div>
</div>