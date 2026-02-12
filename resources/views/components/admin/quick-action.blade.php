@props([
  'href' => '#',
  'title' => '',
  'desc' => '',
  'bg' => 'bg-gray-50 hover:bg-gray-100',
  'iconClass' => 'text-gray-700',
  'path' => '',
])

<a href="{{ $href }}" class="group flex items-center gap-4 p-4 rounded-2xl {{ $bg }} transition">
  <div class="shrink-0">
    <svg class="w-9 h-9 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"></path>
    </svg>
  </div>

  <div>
    <p class="font-semibold text-gray-900 group-hover:opacity-90">{{ $title }}</p>
    <p class="text-sm text-gray-600">{{ $desc }}</p>
  </div>
</a>