<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Discover the perfect staycation experience at CalaiaPH. From luxury glamping to traditional camping, we have accommodations for every preference and budget.">
  <meta name="keywords" content="glamping, camping, staycation, CalaiaPH, farm stay, outdoor accommodation">
  <meta name="author" content="CalaiaPH">
  <title>CalaiaPH - Staycation</title>

  {{-- Stylesheets --}}
  @vite(['resources/css/app.css'])
  <link href="https://fonts.googleapis.com/css2?family=Vilaka+Modern+Serif:ital@0;1&family=Gotham:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-white">
  {{-- Success Message --}}
  @if (session('success'))
    <div class="container-fluid py-3">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <div class="d-flex align-items-center">
              <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
              <span class="fw-medium">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        </div>
      </div>
    </div>
  @endif

  <x-header active="staycation" />

  {{-- Hero Swiper --}}
  <section class="hero-section" style="position: relative;">
    <div class="swiper mySwiper w-full h-64 sm:h-80 md:h-96 lg:h-[500px] xl:h-[600px]">
      <div class="swiper-wrapper">
        @forelse ($swiperImages as $image)
          <div class="swiper-slide relative flex items-center justify-center bg-black overflow-hidden">
            <img
              src="{{ asset('storage/' . $image->image_path) }}"
              alt="{{ $image->title ?? 'Slide Image' }}"
              class="w-full h-full object-cover object-center"
              loading="lazy"
            >
            <div class="absolute inset-0 bg-black opacity-40 z-10"></div>
            <div class="absolute inset-0 flex items-center justify-center z-20 text-white">
              <div class="text-center px-4">
                @if($image->title)
                  <h2 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold mb-4 uppercase"
                      style="font-family: gotham; font-size: 3.5rem;">
                    {{ $image->title }}
                  </h2>
                @endif
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl xl:text-3xl uppercase text-center"
                  style="font-family: 'Crimson Text', serif; font-style: italic; font-size: 1.2rem;">
                  Experience the beauty of nature
                </p>
              </div>
            </div>
          </div>
        @empty
          <div class="swiper-slide flex items-center justify-center bg-gray-200 text-gray-600">
            <p>No images available</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>


  {{-- Staycation Options Section --}}
  <main class="py-12 md:py-16 lg:py-20 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      {{-- Section Header --}}
      <div class="text-center mb-12 md:mb-16">
        <h2 class="text-3xl font-bold text-[#8f5d54] mb-4 uppercase">
          Choose Your Stay
        </h2>
        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          Discover the perfect staycation experience tailored to your interests. From luxury glamping to traditional camping, we have accommodations for every preference and budget.
        </p>
      </div>

      {{-- Features Components --}}
      @php
        $glampingFeatures = [
          'Luxury Amenities',
          'Premium Linens',
          'Private Fire Pit',
          'Scenic Views',
          'Guided Farm Tour'
        ];
        $campingFeatures = [
          'ATV Ride (30 minutes)',
          'Nature Trail Walk',
          'Team Building Games',
          'Lunch Included',
          'Shared Facilities'
        ];
      @endphp

      {{-- Accommodation Cards --}}
      <section class="row g-4 g-md-5 justify-content-center" aria-labelledby="accommodation-heading">
        <div class="col-12">
          <h2 id="accommodation-heading" class="visually-hidden">Available accommodation options</h2>
        </div>

        {{-- Glamping Card --}}
        <div class="col-12 col-md-5">
          <article class="card h-100 border-0 overflow-hidden"
                    style="box-shadow:0 8px 18px rgba(0,0,0,.08);">
            {{-- Inset image --}}
            <div class="px-4 pt-4">
              <div class="position-relative overflow-hidden shadow-sm">
                <img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}"
                     alt="Luxury glamping tent exterior with comfortable amenities"
                     class="w-100" style="height:260px; object-fit:cover;">
              </div>
            </div>

            <div class="card-body px-5 pb-5 pt-4">
              <h3 class="text-center fw-bold mb-3 text-uppercase"
                  style="letter-spacing:.02em; color:#8f5d54; font-family:gotham;">
                Glamping
              </h3>

              {{-- thin divider like mock --}}
              <div class="mx-auto mb-3" style="height:1px; width:100%; background:#e7eddc;"></div>

              <ul class="list-unstyled text-center mb-4">
                @foreach($glampingFeatures as $feature)
                  <li class="mb-2" style="font-size:.82rem; color:#8f5d54; text-transform:uppercase; letter-spacing:.02em;">
                    {{ $feature }}
                  </li>
                @endforeach
              </ul>

              <div class="d-flex justify-content-center">
                <button type="button"
                        class="btn px-4 py-2 rounded-pill fw-semibold"
                        style="background:#8f5d54; color:#fff; font-size:.85rem;"
                        data-bs-toggle="modal" data-bs-target="#glampingModal"
                        onmouseover="this.style.backgroundColor='#a0675a'"
                        onmouseout="this.style.backgroundColor='#8f5d54'">
                  LEARN MORE
                </button>
              </div>
            </div>
          </article>
        </div>

        {{-- Camping Card --}}
        <div class="col-12 col-md-5">
          <article class="card h-100 border-0 overflow-hidden"
                    style="box-shadow:0 8px 18px rgba(0,0,0,.08);">
            {{-- Inset image --}}
            <div class="px-4 pt-4">
              <div class="position-relative overflow-hidden shadow-sm">
                <img src="{{ asset('assets/images/carousel/staycation.png') }}"
                     alt="Traditional camping area with tent setup"
                     class="w-100" style="height:260px; object-fit:cover;">
              </div>
            </div>

            <div class="card-body px-5 pb-5 pt-4">
              <h3 class="text-center fw-bold mb-3 text-uppercase"
                  style="letter-spacing:.02em; color:#8f5d54; font-family:gotham;">
                Camping
              </h3>

              <div class="mx-auto mb-3" style="height:1px; width:100%; background:#e7eddc;"></div>

              <ul class="list-unstyled text-center mb-4">
                @foreach($campingFeatures as $feature)
                  <li class="mb-2" style="font-size:.82rem; color:#8f5d54; text-transform:uppercase; letter-spacing:.02em;">
                    {{ $feature }}
                  </li>
                @endforeach
              </ul>

              <div class="d-flex justify-content-center">
                <button type="button"
                        class="btn px-4 py-2 rounded-pill fw-semibold"
                        style="background:#8f5d54; color:#fff; font-size:.85rem;"
                        data-bs-toggle="modal" data-bs-target="#campingOptionsModal"
                        onmouseover="this.style.backgroundColor='#a0675a'"
                        onmouseout="this.style.backgroundColor='#8f5d54'">
                  LEARN MORE
                </button>
              </div>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>

  {{-- Glamping Options Modal --}}
  <div class="modal fade" id="glampingModal" tabindex="-1" aria-labelledby="glampingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0 pb-0">
          <h2 class="modal-title h3 fw-bold" id="glampingModalLabel" style="color: #8f5d54; font-family: 'Gotham', sans-serif;">
            <i class="bi bi-tent-fill me-2" aria-hidden="true"></i>Glamping Options
          </h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close modal"></button>
        </div>
        <div class="modal-body pt-3">
          <div class="row g-4">
            {{-- Standard Glamping --}}
            <div class="col-12 col-lg-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-secondary text-white py-3">
                  <h3 class="h5 mb-0 fw-semibold">
                    <i class="bi bi-house-door-fill me-2" aria-hidden="true"></i>Standard
                  </h3>
                </div>
                <div class="card-body p-4">
                  <div class="swiper glampingModalCarousel1 mb-4 rounded-3 overflow-hidden" style="height: 250px;">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Standard glamping tent exterior view"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Standard glamping tent interior"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-3.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Standard glamping tent amenities"
                             loading="lazy">
                      </div>
                    </div>
                    <div class="swiper-button-next !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-button-prev !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-pagination !bottom-2"></div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="h4 text-success fw-bold mb-0">₱1,200/night</p>
                    <span class="badge bg-secondary">Standard</span>
                  </div>
                  <h4 class="h6 mb-3 fw-semibold">Features</h4>
                  <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Shared bathroom facilities</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Double bed with linens provided</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Electricity and heating</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Scenic mountain views</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">28 m² space with 4 single beds</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Personal care kits provided</small>
                    </li>
                  </ul>
                  <div class="d-grid gap-2">
                    <button type="button" class="btn btn-secondary" onclick="bookTour('Standard Glamping', 1200)">Book Now</button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vrPreviewModal" data-vr-type="glamping">
                      <i class="bi bi-eye me-2" aria-hidden="true"></i>VR Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {{-- Premium Glamping --}}
            <div class="col-12 col-lg-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header text-white py-3" style="background-color: #8f5d54;">
                  <h3 class="h5 mb-0 fw-semibold">
                    <i class="bi bi-star-fill me-2" aria-hidden="true"></i>Premium
                  </h3>
                </div>
                <div class="card-body p-4">
                  <div class="swiper glampingModalCarousel2 mb-4 rounded-3 overflow-hidden" style="height: 250px;">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Premium glamping tent exterior"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Premium glamping tent luxury interior"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-3.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Premium glamping tent premium amenities"
                             loading="lazy">
                      </div>
                    </div>
                    <div class="swiper-button-next !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-button-prev !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-pagination !bottom-2"></div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                      <p class="h5 text-success fw-bold mb-0">₱5,500/night</p>
                      <small class="text-muted">Sun–Thu: ₱5,500 • Fri–Sat: ₱6,500</small>
                    </div>
                    <span class="badge" style="background-color: #8f5d54;">Exclusive</span>
                  </div>
                  <h4 class="h6 mb-3 fw-semibold">Features</h4>
                  <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Premium towels & luxury amenities</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Air conditioning & Wi-Fi ready</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Personal picnic table & fire pit</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Coffee/tea & drinking water</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Accommodates up to 6 adults</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">28 m² space with luxury upgrades</small>
                    </li>
                  </ul>
                  <div class="d-grid gap-2">
                    <button type="button" class="btn text-white" onclick="bookTour('Premium Glamping', 5500)" style="background-color: #8f5d54;">Book Now</button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vrPreviewModal" data-vr-type="glamping">
                      <i class="bi bi-eye me-2" aria-hidden="true"></i>VR Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Camping Options Modal --}}
  <div class="modal fade" id="campingOptionsModal" tabindex="-1" aria-labelledby="campingOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0 pb-0">
          <h2 class="modal-title h3 fw-bold" id="campingOptionsModalLabel" style="color: #8f5d54; font-family: 'Gotham', sans-serif;">
            <i class="bi bi-campground me-2" aria-hidden="true"></i>Camping Options
          </h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close modal"></button>
        </div>
        <div class="modal-body pt-3">
          <div class="row g-4">
            {{-- Dome Tent --}}
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header text-white py-3" style="background-color: #8f5d54;">
                  <h3 class="h6 mb-0 fw-semibold">
                    <i class="bi bi-tent me-2" aria-hidden="true"></i>Dome Tent
                  </h3>
                </div>
                <div class="card-body p-4">
                  <div class="swiper domeTentCarousel mb-4 rounded-3 overflow-hidden" style="height: 200px;">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Dome tent exterior view"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Dome tent interior"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/staycation.png') }}"
                             class="w-100 h-100 object-cover"
                             alt="Dome tent setup area"
                             loading="lazy">
                      </div>
                    </div>
                    <div class="swiper-button-next !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-button-prev !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-pagination !bottom-2"></div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="h5 text-success fw-bold mb-0">₱1,200/night</p>
                    <span class="badge" style="background-color: #8f5d54;">Comfort</span>
                  </div>
                  <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">4-person capacity</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Sleeping mats included</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Access to shared bathroom</small>
                    </li>
                  </ul>
                  <div class="d-grid gap-2">
                    <button type="button" class="btn text-white" onclick="bookTour('Dome Tent', 1200)" style="background-color: #8f5d54;">Book Now</button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vrPreviewModal" data-vr-type="camping">
                      <i class="bi bi-eye me-2" aria-hidden="true"></i>VR Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {{-- Bell Tent --}}
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-secondary text-white py-3">
                  <h3 class="h6 mb-0 fw-semibold">
                    <i class="bi bi-house-door-fill me-2" aria-hidden="true"></i>Bell Tent
                  </h3>
                </div>
                <div class="card-body p-4">
                  <div class="swiper bellTentCarousel mb-4 rounded-3 overflow-hidden" style="height: 200px;">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/staycation.png') }}"
                             class="w-100 h-100 object-cover"
                             alt="Bell tent exterior"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Bell tent interior"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-1.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Bell tent spacious area"
                             loading="lazy">
                      </div>
                    </div>
                    <div class="swiper-button-next !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-button-prev !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-pagination !bottom-2"></div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="h5 text-success fw-bold mb-0">₱1,800/night</p>
                    <span class="badge bg-secondary">Spacious</span>
                  </div>
                  <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Up to 6 guests</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Luxury canvas tent</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Shared bathroom</small>
                    </li>
                  </ul>
                  <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" onclick="bookTour('Bell Tent', 1800)">Book Now</button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vrPreviewModal" data-vr-type="camping">
                      <i class="bi bi-eye me-2" aria-hidden="true"></i>VR Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {{-- BYOT --}}
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header text-white py-3" style="background-color: #198754;">
                  <h3 class="h6 mb-0 fw-semibold">
                    <i class="bi bi-backpack me-2" aria-hidden="true"></i>BYOT
                  </h3>
                </div>
                <div class="card-body p-4">
                  <div class="swiper byotCarousel mb-4 rounded-3 overflow-hidden" style="height: 200px;">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/BYOT-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="BYOT camping site"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="BYOT camping area setup"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/staycation.png') }}"
                             class="w-100 h-100 object-cover"
                             alt="BYOT camping facilities"
                             loading="lazy">
                      </div>
                    </div>
                    <div class="swiper-button-next !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-button-prev !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-pagination !bottom-2"></div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="h5 text-success fw-bold mb-0">₱600/night</p>
                    <span class="badge" style="background-color: #198754;">Budget</span>
                  </div>
                  <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Bring your own tent</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Spacious camping spot</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Access to shared bathroom</small>
                    </li>
                  </ul>
                  <div class="d-grid gap-2">
                    <button type="button" class="btn btn-secondary" onclick="bookTour('BYOT', 600)">Book Now</button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vrPreviewModal" data-vr-type="camping">
                      <i class="bi bi-eye me-2" aria-hidden="true"></i>VR Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>

            {{-- Car Camping --}}
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-header text-dark py-3" style="background-color: #ffc107;">
                  <h3 class="h6 mb-0 fw-semibold">
                    <i class="bi bi-car-front-fill me-2" aria-hidden="true"></i>Car Camping
                  </h3>
                </div>
                <div class="card-body p-4">
                  <div class="swiper carCampingCarousel mb-4 rounded-3 overflow-hidden" style="height: 200px;">
                    <div class="swiper-wrapper">
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Car-camping3.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Car camping site"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/Glamping-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Car camping setup"
                             loading="lazy">
                      </div>
                      <div class="swiper-slide">
                        <img src="{{ asset('assets/images/carousel/BYOT-2.jpg') }}"
                             class="w-100 h-100 object-cover"
                             alt="Car camping facilities"
                             loading="lazy">
                      </div>
                    </div>
                    <div class="swiper-button-next !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-button-prev !w-8 !h-8 md:!w-12 md:!h-12 !-mt-6 md:!-mt-6" style="color: white;"></div>
                    <div class="swiper-pagination !bottom-2"></div>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="h5 text-success fw-bold mb-0">₱1,500/night</p>
                    <span class="badge text-dark" style="background-color: #ffc107;">Convenient</span>
                  </div>
                  <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Park next to your campsite</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Electrical hookup available</small>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                      <i class="bi bi-check2 text-success me-2 mt-1" aria-hidden="true"></i>
                      <small class="text-muted" style="color: #8f5d54;">Access to shared bathroom</small>
                    </li>
                  </ul>
                  <div class="d-grid gap-2">
                    <button type="button" class="btn text-dark" onclick="bookTour('Car Camping', 1500)" style="background-color: #ffc107;">Book Now</button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#vrPreviewModal" data-vr-type="camping">
                      <i class="bi bi-eye me-2" aria-hidden="true"></i>VR Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  {{-- VR Preview Modal --}}
  <div class="modal fade" id="vrPreviewModal" tabindex="-1" aria-labelledby="vrPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content bg-dark">
        <div class="modal-header border-0 pb-0">
          <h2 class="modal-title h3 text-white" id="vrPreviewModalLabel">
            <i class="bi bi-eye me-2" aria-hidden="true"></i>Virtual Reality Preview
          </h2>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close VR preview modal"></button>
        </div>
        <div class="modal-body p-0">
          <div class="container-fluid p-0">
            <div class="row g-0">
              <div class="col-12">
                <div class="vr-container position-relative" id="vr-container" style="height: 80vh; background-color: #000; min-height: 500px;">
                  
                  {{-- Loading State --}}
                  <div class="vr-loading d-flex align-items-center justify-content-center position-absolute top-0 start-0 w-100 h-100"
                       id="vr-loading"
                       style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; z-index: 10;"
                       role="status"
                       aria-live="polite">
                    <div class="text-center p-5">
                      <div class="spinner-border text-light mb-3" role="status">
                        <span class="visually-hidden">Loading virtual reality preview...</span>
                      </div>
                      <h3 class="h5 mb-3">Loading Virtual Experience</h3>
                      <p class="text-light mb-0">Please wait while we prepare your immersive preview...</p>
                    </div>
                  </div>

                  {{-- Viewer State --}}
                  <div class="vr-viewer d-none position-relative w-100 h-100" id="vr-viewer" style="min-height: 500px; z-index: 1;">
                    <div class="vr-media-container w-100 h-100" id="vr-media-container" role="img" aria-label="Virtual reality view" style="min-height: 500px; position: absolute; top: 0; left: 0;"></div>

                    {{-- Controls --}}
                    <div class="vr-controls position-absolute bottom-0 start-0 w-100 p-3"
                         style="background: rgba(0, 0, 0, 0.8); z-index: 1000;">
                      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        
                        {{-- Left Controls --}}
                        <div class="d-flex gap-2 order-1 order-md-1">
                          <button class="btn btn-outline-light btn-sm"
                                  id="vr-fullscreen"
                                  type="button"
                                  title="Enter fullscreen mode"
                                  aria-label="Enter fullscreen">
                            <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i>
                          </button>
                          <button class="btn btn-outline-light btn-sm"
                                  id="vr-info"
                                  type="button"
                                  title="Show information"
                                  aria-label="Show information">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                          </button>
                        </div>

                        {{-- Center Navigation --}}
                        <div class="d-flex align-items-center gap-3 bg-light bg-opacity-10 rounded-pill px-4 py-2 order-2 order-md-2">
                          <button class="btn btn-outline-light btn-sm"
                                  id="vr-prev"
                                  type="button"
                                  title="Previous view"
                                  aria-label="Previous view">
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                          </button>
                          <span class="text-white fw-bold text-center"
                                id="vr-counter"
                                style="min-width: 60px;"
                                aria-live="polite">1 / 5</span>
                          <button class="btn btn-outline-light btn-sm"
                                  id="vr-next"
                                  type="button"
                                  title="Next view"
                                  aria-label="Next view">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                          </button>
                        </div>

                        {{-- Right Controls --}}
                        <div class="d-flex gap-2 order-3 order-md-3">
                          <button class="btn btn-outline-light btn-sm"
                                  id="vr-share"
                                  type="button"
                                  title="Share this view"
                                  aria-label="Share this view">
                            <i class="bi bi-share" aria-hidden="true"></i>
                          </button>
                          <button class="btn btn-outline-light btn-sm"
                                  data-bs-dismiss="modal"
                                  title="Close VR preview"
                                  aria-label="Close VR preview">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    {{-- Info Overlay --}}
                    <div class="vr-info-overlay position-absolute top-0 start-0 m-3 p-3 rounded-3 text-white d-none"
                         id="vr-info-overlay"
                         style="background: rgba(0, 0, 0, 0.8); max-width: 300px; z-index: 1001;"
                         role="dialog"
                         aria-labelledby="vr-info-title">
                      <h3 class="h6 mb-2" id="vr-info-title">View Information</h3>
                      <p class="small mb-3" id="vr-info-description">View description</p>
                      <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-primary">360° View</span>
                        <span class="badge bg-success">HD Quality</span>
                        <span class="badge bg-info">Interactive</span>
                      </div>
                    </div>
                  </div>

                  {{-- Error State --}}
                  <div class="vr-error d-none text-center p-5 position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" id="vr-error" role="alert" style="background-color: #000; color: white; z-index: 5;">
                    <div class="text-center p-4">
                      <i class="bi bi-exclamation-triangle text-warning mb-3" style="font-size: 3rem;" aria-hidden="true"></i>
                      <h3 class="h4 text-white mb-3">VR Preview Unavailable</h3>
                      <p class="text-light mb-4">
                        We're sorry, but the virtual reality preview is currently unavailable.
                        Please try again later or contact our team for assistance.
                      </p>
                      <button type="button"
                              class="btn btn-primary"
                              id="retryVRPreview"
                              onclick="window.VRPreview?.retry()"
                              aria-label="Try loading VR preview again">
                        Try Again
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Scripts --}}
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>


<script>
  // Application initialization
  (function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
      try {
        initializeApplication();
      } catch (error) {
        console.error('Application initialization error:', error);
        showErrorNotification('An error occurred while loading the page. Please refresh and try again.');
      }
    });

    // Asset URLs for the application
    const AssetManager = {
      urls: {
        glamping1: '{{ asset("assets/images/carousel/Glamping-1.jpg") }}',
        glamping2: '{{ asset("assets/images/carousel/Glamping-2.jpg") }}',
        byot: '{{ asset("assets/images/carousel/BYOT-2.jpg") }}',
        carCamping: '{{ asset("assets/images/carousel/Car-camping3.jpg") }}',
        staycation: '{{ asset("assets/images/carousel/staycation.png") }}'
      }
    };

    function initializeApplication() {
      initializeHeroSlider();
      SwiperManager.init();
      ModalCarouselManager.init();
      VRPreviewManager.init();
      initializeAccommodationCards();

      // Global functions
      window.bookTour = BookingManager.bookTour;
      window.VRPreview = VRPreviewManager.publicApi;
      // Expose manager for inline hooks
      window.VRPreviewManager = VRPreviewManager;
    }

    // Ensure hero slider initializes even if Swiper loads a bit late
    function initializeHeroSlider() {
      const run = () => {
        try {
          if (typeof Swiper === 'undefined') {
            console.warn('Swiper not yet available; will attempt after window.load.');
            return;
          }
          new Swiper('.mySwiper', {
            autoplay: { delay: 3000, disableOnInteraction: false },
            loop: true,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            keyboard: { enabled: true, onlyInViewport: false },
            a11y: {
              prevSlideMessage: 'Previous slide',
              nextSlideMessage: 'Next slide',
              firstSlideMessage: 'This is the first slide',
              lastSlideMessage: 'This is the last slide',
              paginationBulletMessage: 'Go to slide {index}',
            },
          });
          console.log('Hero slider initialized successfully');
        } catch (error) {
          console.error('Hero slider initialization error:', error);
        }
      };

      // Try now (after DOMContentLoaded) — defer scripts *should* be ready
      run();
      // Fallback if CDN lagged
      window.addEventListener('load', run, { once: true });
    }

    // Card hover effects
    function initializeAccommodationCards() {
      const accommodationCards = document.querySelectorAll('article.card');
      accommodationCards.forEach(card => {
        const overlay = card.querySelector('.bg-gradient-to-t');
        card.addEventListener('mouseenter', function () {
          this.style.transform = 'translateY(-8px)';
          if (overlay) overlay.style.opacity = '1';
        });
        card.addEventListener('mouseleave', function () {
          this.style.transform = 'translateY(0)';
          if (overlay) overlay.style.opacity = '0';
        });
      });
    }

    // Swiper Manager
    const SwiperManager = {
      config: {
        default(el) {
          return {
            pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
            autoplay: { delay: 4000, disableOnInteraction: false },
            preloadImages: false,
            lazy: true,
            observer: true,
            observeParents: true
          };
        },
        modal(el) {
          return {
            pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
            autoplay: false,
            preloadImages: false,
            lazy: true,
            observer: true,
            observeParents: true
          };
        }
      },
      instances: new Map(),

      init() {
        console.log('Swiper Manager initialized');
      },

      create(selector, configName = 'default') {
        try {
          if (typeof Swiper === 'undefined') {
            console.warn('Swiper not loaded yet for', selector);
            return null;
          }
          const el = document.querySelector(selector);
          if (!el) {
            console.warn(`Element not found: ${selector}`);
            return null;
          }
          if (el.swiper) return el.swiper;

          const configFactory = this.config[configName];
          const config = typeof configFactory === 'function' ? configFactory(el) : configFactory;
          if (!config.pagination || !config.pagination.el) {
            console.warn('Pagination element not found for', selector);
          }

          // Add navigation and pagination for modal carousels
          if (configName === 'modal') {
            config.navigation = {
              nextEl: el.querySelector('.swiper-button-next'),
              prevEl: el.querySelector('.swiper-button-prev'),
            };
            config.pagination = {
              el: el.querySelector('.swiper-pagination'),
              clickable: true,
              renderBullet: function (index, className) {
                return `<span class="${className} !w-6 !h-1 !bg-white !opacity-50 !rounded-none !mx-1"></span>`;
              },
            };
          }

          const swiper = new Swiper(el, config);
          this.instances.set(selector, swiper);
          return swiper;
        } catch (error) {
          console.error('Swiper create error:', selector, error);
          return null;
        }
      },

      destroy(selector) {
        const swiper = this.instances.get(selector);
        if (swiper) {
          try {
            swiper.destroy(true, true);
            this.instances.delete(selector);
          } catch (error) {
            console.error('Swiper destroy error:', selector, error);
          }
        }
      }
    };

    // Modal Carousel Management
    const ModalCarouselManager = {
      init() {
        this.initializeGlampingModal();
        this.initializeCampingModal();
      },

      initializeGlampingModal() {
        const modal = document.getElementById('glampingModal');
        if (!modal) return;

        const carousels = ['.glampingModalCarousel1', '.glampingModalCarousel2'];

        modal.addEventListener('shown.bs.modal', () => {
          carousels.forEach(selector => {
            setTimeout(() => SwiperManager.create(selector, 'modal'), 100);
          });
        });

        modal.addEventListener('hidden.bs.modal', () => {
          carousels.forEach(selector => SwiperManager.destroy(selector));
        });
      },

      initializeCampingModal() {
        const modal = document.getElementById('campingOptionsModal');
        if (!modal) return;

        const carousels = [
          '.domeTentCarousel',
          '.bellTentCarousel',
          '.byotCarousel',
          '.carCampingCarousel'
        ];

        modal.addEventListener('shown.bs.modal', () => {
          carousels.forEach(selector => {
            setTimeout(() => SwiperManager.create(selector, 'modal'), 100);
          });
        });

        modal.addEventListener('hidden.bs.modal', () => {
          carousels.forEach(selector => SwiperManager.destroy(selector));
        });
      }
    };

    // Booking Management
    const BookingManager = {
      bookTour(packageName, price) {
        try {
          if (!packageName || typeof packageName !== 'string') {
            showErrorNotification('Invalid package name provided.');
            return;
          }
          const numPrice = Number(price);
          if (!Number.isFinite(numPrice) || numPrice < 0) {
            showErrorNotification('Invalid price provided.');
            return;
          }

          const params = new URLSearchParams({
            type: 'staycation',
            package: packageName,
            price: String(numPrice)
          });

          const bookingUrl = `{{ route('booking.create') }}?${params.toString()}`;
          showLoadingNotification('Redirecting to booking page...');
          window.location.href = bookingUrl;
        } catch (error) {
          console.error('Booking error:', error);
          showErrorNotification('Unable to proceed to booking. Please try again.');
        }
      }
    };

    // VR Preview Management
    const VRPreviewManager = {
      currentIndex: 0,
      mediaItems: [],
      isInitialized: false,
      images: AssetManager.urls,
      config: {
        loadingDelay: 800,
        validTypes: ['glamping', 'camping'],
        defaultType: 'glamping'
      },

      publicApi: {
        retry: () => VRPreviewManager.retry()
      },

      init() {
        if (this.isInitialized) return;
        this.isInitialized = true;

        const modal = document.getElementById('vrPreviewModal');
        if (!modal) {
          console.warn('VR Preview modal not found');
          return;
        }

        modal.addEventListener('show.bs.modal', (e) => {
          const button = e.relatedTarget;
          const type = this.validate(button?.getAttribute('data-vr-type'));
          this.load(type);
        });

        modal.addEventListener('hidden.bs.modal', () => this.cleanup());
        this.bindControls();

        console.log('VR Preview Manager initialized');
      },

      validate(type) {
        return this.config.validTypes.includes(type) ? type : this.config.defaultType;
      },

      els() {
        return {
          loading: document.getElementById('vr-loading'),
          viewer: document.getElementById('vr-viewer'),
          error: document.getElementById('vr-error'),
        };
      },

      // Preserve base classes; only toggle visibility utilities
      setState(state) {
        const { loading, viewer, error } = this.els();
        const show = (el, mode) => {
          if (!el) return;
          el.classList.remove('d-none', 'd-block', 'd-flex');
          el.classList.add(mode);
        };
        const hide = (el) => el && el.classList.add('d-none');

        if (state === 'loading') {
          show(loading, 'd-flex'); hide(viewer); hide(error);
        } else if (state === 'viewer') {
          hide(loading); show(viewer, 'd-block'); hide(error);
        } else if (state === 'error') {
          hide(loading); hide(viewer); show(error, 'd-flex');
        }
      },

      load(type) {
        this.setState('loading');
        this.mediaItems = this.getItems(type);
        this.currentIndex = 0;

        setTimeout(() => {
          try {
            this.render();
            this.setState('viewer');
          } catch (error) {
            console.error('VR Preview render error:', error);
            this.setState('error');
          }
        }, this.config.loadingDelay);
      },

      getItems(type) {
        const data = {
          glamping: [
            {
              type: 'image',
              src: this.images.glamping1,
              title: 'Glamping Tent Interior',
              description: 'Experience the comfort and luxury of our premium glamping tents with panoramic views of the farm.'
            },
            {
              type: 'image',
              src: this.images.glamping2,
              title: 'Glamping Tent Exterior',
              description: 'Beautiful exterior view of our luxury glamping tents surrounded by nature.'
            }
          ],
          camping: [
            {
              type: 'image',
              src: this.images.byot,
              title: 'Camping Area',
              description: 'Spacious camping grounds perfect for tents and outdoor adventures.'
            },
            {
              type: 'image',
              src: this.images.carCamping,
              title: 'Car Camping Site',
              description: 'Convenient car camping with parking right next to your site.'
            }
          ]
        };
        return data[type] || data[this.config.defaultType];
      },

      render() {
        const container = document.getElementById('vr-media-container');
        if (!container) throw new Error('VR media container not found');

        container.innerHTML = '';
        const item = this.mediaItems[this.currentIndex];
        if (!item) throw new Error('No media item found');

        const mediaEl = document.createElement('div');
        mediaEl.className = 'position-relative w-100 h-100 overflow-hidden';

        const img = document.createElement('img');
        img.src = item.src;
        img.alt = item.title;
        img.className = 'position-absolute top-0 start-0 w-100 h-100';
        img.style.objectFit = 'cover';
        img.style.objectPosition = 'center';
        img.style.minWidth = '100%';
        img.style.minHeight = '100%';
        img.loading = 'lazy';
        img.decoding = 'async';
        img.onerror = () => this.handleImageError();

        const caption = document.createElement('div');
        caption.className = 'position-absolute bottom-0 start-0 w-100 p-4 text-white';
        caption.style.background = 'linear-gradient(transparent, rgba(0,0,0,0.8))';
        caption.style.zIndex = '2';
        caption.innerHTML = `
          <h4 class="fw-semibold mb-1">${item.title}</h4>
          <p class="mb-0 small opacity-75">${item.description}</p>
        `;

        mediaEl.appendChild(img);
        mediaEl.appendChild(caption);
        container.appendChild(mediaEl);

        this.updateCounter();
        this.updateInfo(item);
      },

      handleImageError() {
        console.error('VR Preview image failed to load');
        this.setState('error');
      },

      bindControls() {
        const click = (id, handler) => {
          const el = document.getElementById(id);
          if (el) el.addEventListener('click', handler);
        };

        click('vr-prev', () => this.navigate(-1));
        click('vr-next', () => this.navigate(1));
        click('vr-info', () => this.toggleInfo());

        // Fullscreen
        const fsBtn = document.getElementById('vr-fullscreen');
        const viewerShell = document.getElementById('vr-container');
        if (fsBtn && viewerShell) {
          fsBtn.addEventListener('click', async () => {
            try {
              if (!document.fullscreenElement && viewerShell.requestFullscreen) {
                await viewerShell.requestFullscreen();
              } else if (document.exitFullscreen) {
                await document.exitFullscreen();
              }
            } catch (e) {
              console.warn('Fullscreen not supported:', e);
            }
          });
        }

        // Share (with clipboard fallback)
        const shareBtn = document.getElementById('vr-share');
        if (shareBtn) {
          shareBtn.addEventListener('click', async () => {
            try {
              const item = this.mediaItems[this.currentIndex];
              const shareData = {
                title: item?.title || 'VR Preview',
                text: 'Check out this view from CalaiaPH!',
                url: window.location.href
              };
              if (navigator.share) {
                await navigator.share(shareData);
              } else if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(shareData.url);
                alert('Link copied to clipboard!');
              }
            } catch (e) {
              console.warn('Share failed:', e);
            }
          });
        }
      },

      navigate(direction) {
        if (!this.mediaItems.length) return;
        this.currentIndex = (this.currentIndex + direction + this.mediaItems.length) % this.mediaItems.length;
        this.render();
      },

      toggleInfo() {
        const overlay = document.getElementById('vr-info-overlay');
        if (!overlay) return;
        overlay.classList.toggle('d-none');
        overlay.classList.toggle('d-block');
      },

      updateCounter() {
        const counter = document.getElementById('vr-counter');
        if (counter && this.mediaItems.length) {
          counter.textContent = `${this.currentIndex + 1} / ${this.mediaItems.length}`;
        }
      },

      updateInfo(item) {
        const title = document.getElementById('vr-info-title');
        const description = document.getElementById('vr-info-description');
        if (title) title.textContent = item.title;
        if (description) description.textContent = item.description;
      },

      retry() {
        this.load(this.config.defaultType);
      },

      cleanup() {
        this.currentIndex = 0;
        this.mediaItems = [];
        const overlay = document.getElementById('vr-info-overlay');
        if (overlay) {
          overlay.classList.remove('d-block');
          overlay.classList.add('d-none');
        }
        const container = document.getElementById('vr-media-container');
        if (container) container.innerHTML = '';
        this.setState('loading');
      }
    };

    // Utilities
    function showErrorNotification(message) {
      console.error(message);
      alert(message); // Fallback
    }

    function showLoadingNotification(message) {
      console.log(message);
    }
  })();
</script>


  {{-- Footer --}}
  <x-footer />
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
