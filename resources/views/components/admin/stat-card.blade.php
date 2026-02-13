@props([
  'label' => '',
  'value' => '',
  'accent' => 'border-blue-500',
  'valueClass' => 'text-blue-600',
  'bg' => 'bg-white',
  'borderColor' => 'border-gray-100',
  'meta' => null,
  'metaInline' => null,
])

<div {{ $attributes->merge([
  'class' => "{$bg} rounded-2xl shadow-sm border {$borderColor} p-4 sm:p-6 border-l-4 {$accent} min-w-0"
]) }}>
  <h3 class="text-sm font-semibold text-gray-700 truncate">{{ $label }}</h3>

  <p class="mt-2 font-extrabold {{ $valueClass }}
            text-2xl sm:text-3xl 2xl:text-4xl
            wrap-break-word">
    {{ $value }}
  </p>

  @if($metaInline)
    <p class="mt-3 text-sm text-gray-500 wrap-break-word">
      {{ $metaInline }}
    </p>
  @endif

  @if(is_array($meta) && count($meta))
    <dl class="mt-3 space-y-1 text-sm text-gray-500">
      @foreach($meta as $row)
        @php
          $k = $row[0] ?? '';
          $v = $row[1] ?? '';
          $cls = $row[2] ?? 'text-gray-500';
        @endphp
        <div class="flex items-center justify-between gap-3 min-w-0">
          <dt class="truncate">{{ $k }}</dt>
          <dd class="font-medium {{ $cls }} shrink-0">
            {{ $v }}
          </dd>
        </div>
      @endforeach
    </dl>
  @endif
</div>