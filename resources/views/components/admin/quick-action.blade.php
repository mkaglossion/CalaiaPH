@props(['href','title','desc','bg','iconClass','path'])

<a
  href="{{ $href }}"
  class="group block w-full min-w-0 rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 transition
         hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2 {{ $bg }}"
>
  <div class="flex items-start gap-3 min-w-0">
    <div class="shrink-0">
      <svg class="h-6 w-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
      </svg>
    </div>

    <div class="min-w-0 flex-1">
      <p class="font-extrabold text-gray-900 truncate group-hover:text-gray-950">
        {{ $title }}
      </p>
      <p class="mt-1 text-sm text-gray-600 line-clamp-2">
        {{ $desc }}
      </p>
    </div>
  </div>
</a>