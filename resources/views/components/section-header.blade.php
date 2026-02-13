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
        <h2 class="{{ $titleSize }} font-bold text-brand mb-4 {{ $centered ? 'text-center' : '' }}" style="font-family: 'Gotham Book', sans-serif;">
            {{ $title }}
        </h2>
    @endif

    @if($subtitle)
        <p class="mt-6 {{ $subtitleSize }} text-brand max-w-3xl {{ $centered ? 'mx-auto text-center' : '' }}" style="">
            {{ $subtitle }}
        </p>
    @endif
</header>