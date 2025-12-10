@php
    $testimonials = $testimonials ?? collect();
    $hasManyTestimonials = $testimonials->count() > 3;
@endphp

@if($testimonials->isEmpty())
    <div class="text-center py-12">
        <p class="text-gray-500">No testimonials available yet. Be the first to share your experience!</p>
    </div>
@else
    <section class="relative py-12 min-h-[500px]" style="background: linear-gradient(rgba(143, 93, 84, 0.5), rgba(143, 93, 84, 0.5)), url('/assets/images/carousel/our-farm.jpg'); background-size: cover; background-position: center; background-attachment: fixed;">

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="mt-8 text-3xl sm:text-4xl font-bold tracking-tight text-white" style="font-size: 25px;">WHAT OUR GUESTS SAY</h2>
                <p class="mt-3 text-lg text-white" style="font-style: italic; font-size: 13px;">Hear from those who've experienced Calaia</p>
                <div class="mt-6 mx-auto h-px w-80 bg-white"></div>
            </div>

            @if($hasManyTestimonials)
                <!-- Carousel for many testimonials -->
                <div class="relative">
                    <div class="overflow-hidden">
                        <div class="flex transition-transform duration-500 ease-in-out" id="testimonial-carousel">
                            @foreach($testimonials as $testimonial)
                            <div class="w-full shrink-0 px-4">
                                <div class="w-full max-w-2xl mx-auto rounded-2xl bg-white shadow-sm p-6 md:p-10 text-center min-h-[250px] flex flex-col justify-center">
                                    <div class="flex justify-center mb-4">
                                        <div class="flex text-yellow-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <blockquote class="text-lg text-[#8f5d54] mb-4 italic" style="font-size: 13px;">"{!! nl2br(e($testimonial->content)) !!}"</blockquote>
                                    <div class="flex items-center justify-center">
                                        <div>
                                            <p class="font-semibold text-[#8f5d54]">{{ $testimonial->name }}</p>
                                            <p class="text-sm text-[#97655A]" style="font-size: 13px;">{{ $testimonial->position }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Carousel Navigation -->
                    <button id="prev-btn" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white/50 hover:bg-white/80 rounded-full p-2 text-[#8f5d54] transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button id="next-btn" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white/50 hover:bg-white/80 rounded-full p-2 text-[#8f5d54] transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            @else
                <!-- Grid for few testimonials -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($testimonials as $testimonial)
                    <div class="w-full max-w-2xl mx-auto rounded-2xl bg-white shadow-sm p-6 md:p-10 text-center min-h-[250px] flex flex-col justify-center">
                        <div class="flex justify-center mb-4">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <blockquote class="text-lg text-[#8f5d54] mb-4 italic" style="font-size: 13px;">"{!! nl2br(e($testimonial->content)) !!}"</blockquote>
                        <div class="flex items-center justify-center">
                            <div>
                                <p class="font-semibold text-[#8f5d54]">{{ $testimonial->name }}</p>
                                <p class="text-sm text-[#97655A]" style="font-size: 13px;">{{ $testimonial->position }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- Carousel JavaScript -->
    @if($hasManyTestimonials)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('testimonial-carousel');
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const testimonials = carousel.querySelectorAll('.w-full');
            const totalTestimonials = testimonials.length;
            let currentIndex = 0;

            // Set initial position
            updateCarousel();

            // Navigation buttons
            prevBtn.addEventListener('click', function() {
                currentIndex = (currentIndex > 0) ? currentIndex - 1 : totalTestimonials - 1;
                updateCarousel();
            });

            nextBtn.addEventListener('click', function() {
                currentIndex = (currentIndex < totalTestimonials - 1) ? currentIndex + 1 : 0;
                updateCarousel();
            });

            function updateCarousel() {
                const offset = -currentIndex * 100;
                carousel.style.transform = `translateX(${offset}%)`;
            }

            // Auto-advance carousel
            setInterval(function() {
                currentIndex = (currentIndex < totalTestimonials - 1) ? currentIndex + 1 : 0;
                updateCarousel();
            }, 5000);
        });
    </script>
    @endif
@endif
