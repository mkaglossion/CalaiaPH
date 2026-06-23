{{-- resources/views/components/section-header.blade.php --}}
@props([
    'title' => '',
    'subtitle' => '',
    'id' => null,
    'centered' => true,
    'titleSize' => 'text-3xl sm:text-4xl md:text-5xl',
    'subtitleSize' => 'text-base md:text-lg',
    'dividerClass' => 'bg-brand'
])

<header class="{{ $centered ? 'text-center' : '' }} mb-12" {{ $id ? 'id="' . $id . '"' : '' }}>
    @if($title)
        <h2 class="{{ $titleSize }} font-bold text-brand mb-4 {{ $centered ? 'text-center' : '' }}">
            {{ $title }}
        </h2>
    @endif

    @if($title && $subtitle)
        <div class="w-20 md:w-24 h-1 {{ $dividerClass }} mx-auto mb-5 md:mb-6 rounded-full" aria-hidden="true"></div>
    @endif

    @if($subtitle)
        <p class="{{ $subtitleSize }} text-gray-700 max-w-3xl leading-relaxed {{ $centered ? 'mx-auto text-center' : '' }}" style="">
            {{ $subtitle }}
        </p>
    @endif
</header>
