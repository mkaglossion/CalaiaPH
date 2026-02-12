@props([
  'label' => '',
  'value' => '',
  'accent' => 'border-blue-500',
  'valueClass' => 'text-blue-600',
  'meta' => null,       // array of [label, value, optionalClass]
  'metaInline' => null, // string
])

<div {{ $attributes->merge(['class' => "bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-l-4 {$accent}"]) }}>
  <h3 class="text-sm font-semibold text-gray-700">{{ $label }}</h3>
  <p class="mt-2 text-3xl font-extrabold {{ $valueClass }}">{{ $value }}</p>

  @if($metaInline)
    <p class="mt-3 text-sm text-gray-500">{{ $metaInline }}</p>
  @endif

  @if(is_array($meta) && count($meta))
    <dl class="mt-3 space-y-1 text-sm text-gray-500">
      @foreach($meta as $row)
        @php
          $k = $row[0] ?? '';
          $v = $row[1] ?? '';
          $cls = $row[2] ?? 'text-gray-500';
        @endphp
        <div class="flex items-center justify-between">
          <dt>{{ $k }}</dt>
          <dd class="font-medium {{ $cls }}">{{ $v }}</dd>
        </div>
      @endforeach
    </dl>
  @endif
</div>