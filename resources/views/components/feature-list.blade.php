{{-- resources/views/components/feature-list.blade.php --}}
@props([
    'features' => [],
    'icon' => 'check',
    'iconColor' => 'text-[#8f5d54]',
    'textColor' => 'text-gray-700',
    'fontSize' => 'font-size: 13px; font-family: \'Gotham Book\', sans-serif;'
])

@if($features)
    <ul class="space-y-4" role="list">
        @foreach($features as $feature)
            <li class="flex items-start">
                <div class="shrink-0 w-6 h-6 bg-[#8f5d54]/10 rounded-full flex items-center justify-center mt-0.5 mr-4">
                    <i class="fas fa-{{ $icon }} {{ $iconColor }} text-xs" aria-hidden="true"></i>
                </div>
                <span class="{{ $textColor }}" style="{{ $fontSize }}">{{ $feature }}</span>
            </li>
        @endforeach
    </ul>
@endif