{{-- event-experience.blade.php --}}
@php
    $sections = $sections ?? [];

    $theme = [
        "beige_swatch" => [
            "bg" => "bg-[#EADAC3]",   // ✅ exact swatch
            "title" => "text-[#8b5a52]",
            "text" => "text-[#393a35]",
            "btn" => "bg-[#a75e3e] text-white hover:bg-[#8a4f2f]",
        ],
        "white" => [
            "bg" => "bg-white",
            "title" => "text-[#8b5a52]",
            "text" => "text-[#393a35]",
            "btn" => "bg-[#a75e3e] text-white hover:bg-[#8a4f2f]",
        ],
        "tan" => [
            "bg" => "bg-[#b57c63]",
            "title" => "text-white",
            "text" => "text-white/90",
            "btn" => "bg-[#566057] text-white hover:bg-[#4b4b4b]",
        ],
    ];
@endphp

<section class="w-full">
    @foreach ($sections as $i => $section)
        @php
            $t = $theme[$section->theme] ?? $theme["white"];
            $reverse = $i % 2 === 1; // alternate left/right on desktop
        @endphp

        <div id="{{ $section->section_id }}" class="{{ $t['bg'] }} py-14 md:py-16">
            <div class="mx-auto max-w-6xl px-6 md:px-10">
                {{-- ✅ Responsive:
                     Mobile: stacked
                     Desktop (md+): 2 columns w/ alternating order
                --}}
                <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-10 md:gap-14">
                    {{-- IMAGE --}}
                    <div class="{{ $reverse ? 'md:order-2' : 'md:order-1' }}">
                        <img
                            src="{{ \App\Helpers\ImageHelper::src($section->image_path) }}"
                            alt="{{ $section->title }}"
                            class="w-full h-80 object-cover rounded-lg shadow-lg"
                            onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';"
                        >
                    </div>

                    {{-- TEXT --}}
                    <div class="{{ $reverse ? 'md:order-1' : 'md:order-2' }}">
                        <h2
                            class="uppercase font-bold tracking-wide {{ $t['title'] }} mb-4 text-center md:text-left"
                            style="font-size:25px;"
                        >
                            {{ $section->title }}
                        </h2>

                        <div class="max-w-130 mx-auto md:mx-0">
                            <p
                                class="{{ $t['text'] }} text-[13px] leading-[1.7] mb-3 text-center md:text-left"
                            >
                                {{ $section->description1 }}
                            </p>

                            <p
                                class="{{ $t['text'] }} text-[13px] leading-[1.7] mb-6 text-center md:text-left"
                            >
                                {{ $section->description2 }}
                            </p>

                            <div class="text-center md:text-left">
                                <a
                                    href="{{ $section->link_path }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-full transition-colors duration-300 {{ $t['btn'] }}"
                                >
                                    LEARN MORE
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</section>
