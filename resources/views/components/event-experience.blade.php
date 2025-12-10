<section class="py-12 bg-[#f5efe7]">
    <div class="max-w-7xl mx-auto px-3 px-lg-5">
        @php
        // Sections are now passed from the component
        @endphp

        @foreach($sections as $i => $section)
            <div class="flex flex-col {{ $i % 2 === 1 ? 'lg:flex-row-reverse' : 'lg:flex-row' }} items-center gap-8 lg:gap-16 mb-4 lg:mb-8">
                <div class="w-full lg:w-1/2">
                    <img src="{{ asset($section->image_path) }}" alt="{{ $section->title }}" class="w-full h-80 object-cover rounded-lg shadow-lg">
                </div>
                <div class="w-full lg:w-1/2 lg:text-left text-center flex flex-col justify-center py-6 px-4">
                    <h2 class="text-4xl font-bold text-[#8f5d54] mb-4 uppercase text-center lg:text-left" style=" font-size: 25px;">{{ $section->title }}</h2>
                    <p class="text-[13px] text-[#8f5d54] mb-4 leading-relaxed text-justify" style="">{{ $section->description1 }}</p>
                    <p class="text-[13px] text-[#8f5d54] mb-6 leading-relaxed text-justify" style="">{{ $section->description2 }}</p>
                    <a href="{{ $section->link_path }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#8f5d54] hover:bg-[#8f5d54]/90 transition-colors duration-300 self-center lg:self-start">
                       LEARN MORE
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>