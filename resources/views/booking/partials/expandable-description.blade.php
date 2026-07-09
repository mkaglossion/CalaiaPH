@php
    $description = trim((string) ($description ?? ''));
    $fallback = trim((string) ($fallback ?? ''));
    $text = $description !== '' ? $description : $fallback;
    $normalizedText = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    $wrapperClass = $wrapperClass ?? 'mb-4';
    $textClass = $textClass ?? 'text-gray-600';
    $buttonClass = $buttonClass ?? 'text-[#8f5d54]';
    $clampLines = max(1, (int) ($clampLines ?? 3));
@endphp

@if($normalizedText !== '')
    <div
        x-data="{ expanded: false, canToggle: false, collapsedHeight: 0 }"
        x-init="
            const measure = () => {
                const el = $refs.content;
                const computed = getComputedStyle(el);
                const lineHeight = parseFloat(computed.lineHeight) || (parseFloat(computed.fontSize || '16') * 1.5);
                collapsedHeight = Math.ceil(lineHeight * {{ $clampLines }});
                canToggle = el.scrollHeight > collapsedHeight + 1;
            };
            measure();
            requestAnimationFrame(measure);
            window.addEventListener('resize', measure);
        "
        class="{{ $wrapperClass }}"
    >
        <p
            x-ref="content"
            class="{{ $textClass }} leading-relaxed text-xs sm:text-sm whitespace-pre-line wrap-break-word"
            style="text-align: justify;"
            :style="expanded ? '' : 'max-height: ' + collapsedHeight + 'px; overflow: hidden; transition: max-height 0.2s ease;'"
        >{{ $text }}</p>

        <button
            type="button"
            class="mt-1 inline-flex items-center text-xs font-semibold {{ $buttonClass }} hover:underline"
            x-show="canToggle"
            x-cloak
            @click.stop="expanded = !expanded"
            x-text="expanded ? 'See less' : 'See more'"
        ></button>
    </div>
@endif
