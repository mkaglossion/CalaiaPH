<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <title>CalaiaPH</title> 
  </head>
  <body class="bg-gray-100">
    <x-header active="home" />

    <!-- Carousel -->
    <div class="container-fluid px-0 mt-0">
        <div class="swiper w-100">
            <div class="swiper-wrapper">
                <!-- Slide 1 -->
                <div class="swiper-slide position-relative">
                    <div class="carousel-overlay-light"></div>
                    <video class="w-100 d-block object-fit-cover carousel-video" autoplay muted loop loading="lazy">
                        <source src="{{ asset('assets/videos/horse2.mp4') }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-1 px-sm-3 carousel-content">

                        <!--<h1 class="display-8 display-md-4 fw-bold text-white text-shadow carousel-heading mb-2 mb-md-3">WELCOME TO CALAIA</h1>-->
                        <p class="text-white text-shadow mb-2 mb-md-3 carousel-lead carousel-lead-custom">
                            IMMERSE YOURSELF IN THE BEAUTY OF OUR NATURAL SURROUNDINGS <br>
                        </p>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide position-relative">
                    <div class="carousel-overlay-dark"></div>
                    <img src="{{ asset('assets/images/carousel/WELCOME TO OUR RANCH.jpg') }}" class="w-100 d-block" alt="Car camping at Calaia Farm" loading="lazy" width="1920" height="800" />
                    <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-1 px-sm-3 carousel-content">
                        <!--<h1 class="display-8 display-md-4 fw-bold text-white text-shadow carousel-heading mb-2 mb-md-3">DISCOVER OUR RANCH</h1>-->
                        <p class="text-white text-shadow mb-2 mb-md-3 carousel-lead" style="font-family: 'vila zilka seriff', serif !important; font-style: italic !important;">NATURE PARK STORY, SUSTAINABILITY, ADVENTURE, ENDEMIC TREES AND SAFE FOOD.</p>
                    </div>
                </div>

                <!-- Slide 3: Car Camping -->
                <div class="swiper-slide position-relative">
                    <div class="carousel-overlay-dark"></div>
                    <img src="{{ asset('assets/images/carousel/WELCOME TO OUR RANCH2.jpg') }}" class="w-100 d-block" alt="Car camping at Calaia Farm" loading="lazy" width="1920" height="800" />
                    <div class="position-absolute top-50 start-50 translate-middle text-center w-100 px-1 px-sm-3 carousel-content">
                        <!--<h1 class="display-8 display-md-4 fw-bold text-white text-shadow carousel-heading mb-2 mb-md-3">DISCOVER OUR RANCH</h1>-->
                        <p class="text-white text-shadow mb-2 mb-md-3 carousel-lead carousel-lead-custom">NATURE PARK STORY, SUSTAINABILITY, ADVENTURE, ENDEMIC TREES AND SAFE FOOD.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  </body>
</html>
