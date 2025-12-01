{{-- resources/views/components/section-header.blade.php --}}
@props([
    'title' => '',
    'subtitle' => '',
    'id' => null,
    'centered' => true,
    'titleSize' => 'text-3xl md:text-4xl',
    'subtitleSize' => 'text-lg md:text-xl'
])

<header class="{{ $centered ? 'text-center' : '' }} mb-12" {{ $id ? 'id="' . $id . '"' : '' }}>
    @if($title)
        <h2 class="{{ $titleSize }} font-bold text-[#8f5d54] mb-4" style="font-family: 'Gotham Book', sans-serif;">
            {{ $title }}
        </h2>
    @endif

    @if($subtitle)
        <p class="{{ $subtitleSize }} text-gray-600 max-w-2xl {{ $centered ? 'mx-auto' : '' }} leading-relaxed" style="font-family: 'Gotham Book', sans-serif;">
            {{ $subtitle }}
        </p>
    @endif
</header>