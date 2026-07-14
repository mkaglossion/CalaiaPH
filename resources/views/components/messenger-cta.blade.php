@props([
    'sectionClass' => 'container mx-auto px-4 sm:px-6 lg:px-8 pb-16 md:pb-20',
    'sectionStyle' => '',
    'wrapperClass' => 'max-w-4xl mx-auto',
    'cardClass' => 'bg-linear-to-r from-[#97655A] to-[#8f5d54] rounded-2xl p-6 sm:p-8 md:p-12 text-white text-center shadow-xl',
    'iconClass' => 'fab fa-facebook-messenger text-4xl md:text-5xl opacity-80 mb-4 block',
    'titleId' => null,
    'title' => '',
    'intro' => '',
    'buttonUrl' => '#',
    'buttonLabel' => 'Message Us on Messenger',
    'buttonClass' => 'inline-flex w-full sm:w-auto items-center justify-center px-6 md:px-8 py-4 bg-[#0084ff] hover:bg-[#0073e0] text-white font-semibold rounded-xl transition-all duration-300 gap-3 shadow-lg text-base md:text-lg',
    'buttonIconClass' => 'fab fa-facebook-messenger text-xl md:text-2xl',
    'note' => '',
    'noteClass' => 'text-xs sm:text-sm mt-6 opacity-75',
])

<section {{ $attributes->merge(['class' => $sectionClass]) }} @if(filled($sectionStyle)) style="{{ $sectionStyle }}" @endif @if(filled($titleId)) aria-labelledby="{{ $titleId }}" @endif>
    <div class="{{ $wrapperClass }}">
        <div class="{{ $cardClass }}">
            <i class="{{ $iconClass }}"></i>

            <h2 @if(filled($titleId)) id="{{ $titleId }}" @endif class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4">
                {{ $title }}
            </h2>

            <p class="text-base md:text-lg mb-8 opacity-90 max-w-2xl mx-auto">
                {{ $intro }}
            </p>

            <a href="{{ $buttonUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="{{ $buttonClass }}">
                <i class="{{ $buttonIconClass }}"></i>
                {{ $buttonLabel }}
            </a>

            <p class="{{ $noteClass }}">
                {{ $note }}
            </p>
        </div>
    </div>
</section>
