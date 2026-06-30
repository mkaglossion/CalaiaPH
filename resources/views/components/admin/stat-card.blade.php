@props([
  'label' => '',
  'value' => '',
  'valueClass' => 'text-gray-900',
  'bg' => 'bg-white',
  'icon' => null,
  'iconClass' => 'text-gray-900',
  'iconBg' => 'bg-gray-100',
  'description' => null,
  'href' => null,
  'footerLabel' => null,
  'footerHref' => null,
  'footerTone' => 'text-gray-700',
  'footerBg' => 'bg-gray-50',
])

@php
  $footerHref = $footerHref ?? $href;
  $tag = $href ? 'a' : 'div';
  $classes = trim(implode(' ', array_filter([
    $bg,
    'rounded-xl shadow-sm border border-gray-100',
    'p-5 sm:p-6 min-w-0 transition duration-200',
    $href ? 'hover:bg-gray-50/60 hover:shadow-md' : '',
  ])));
@endphp

<{{ $tag }}
  @if($href) href="{{ $href }}" @endif
  {{ $attributes->merge(['class' => $classes]) }}
>
  <div class="flex items-start gap-4 min-w-0">
    @if($icon)
      <div class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-xl {{ $iconBg }}">
        <i data-lucide="{{ $icon }}" class="h-7 w-7 {{ $iconClass }}"></i>
      </div>
    @endif

    <div class="min-w-0 flex-1">
      <h3 class="text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-500 truncate">{{ $label }}</h3>
      <p class="mt-1.5 text-3xl font-semibold tracking-tight {{ $valueClass }} wrap-break-word">{{ $value }}</p>

      @if($description)
        <p class="mt-2 text-sm text-gray-500">{{ $description }}</p>
      @endif
    </div>
  </div>

  @if($footerLabel)
    <div class="mt-5 px-4 py-3 flex items-center justify-center gap-3 border-t border-gray-100">
      <span class="inline-flex items-center gap-2 text-sm font-medium {{ $footerTone }}">
        <span>{{ $footerLabel }}</span>
        <i data-lucide="chevron-right" class="h-4 w-4"></i>
      </span>
    </div>
  @endif
</{{ $tag }}>
