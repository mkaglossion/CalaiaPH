@props([
  'status' => 'default',
])

@php
  $classes = match($status) {
    'confirmed' => 'bg-green-100 text-green-800',
    'completed' => 'bg-blue-100 text-blue-800',
    'cancelled' => 'bg-red-100 text-red-800',
    default => 'bg-gray-100 text-gray-700',
  };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {$classes}"]) }}>
  {{ $slot }}
</span>