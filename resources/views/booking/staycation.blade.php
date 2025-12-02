{{-- resources/views/booking/staycation.blade.php --}}
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="CalaiaPH - Farm stays and outdoor adventures">
    <meta name="keywords" content="farm stay, day tours, outdoor activities, nature retreat, CalaiaPH, Philippines farm experience">
    <meta name="author" content="CalaiaPH">
    @vite(['resources/css/app.css'])
    <title>Booking Staycation - Calaia Farm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  </head>
  <body class="bg-[#f5efe7]">
        <div class="flex flex-col items-center justify-center px-4">
            @if (session('success'))
                    <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
                            {{ session('success') }}
                    </div>
                    <script>
                        // Clear selections and UI after booking success
                        sessionStorage.removeItem('staycation_selections');
                        if (window.selectedAccommodations) {
                            window.selectedAccommodations = {};
                        }
                        var container = document.getElementById('selected-accommodations-container');
                        if (container) container.innerHTML = '';

                        // Clear the booking summary immediately
                        document.getElementById('selected-accommodations-summary').innerHTML = '';
                        document.getElementById('total-price').textContent = '₱ 0';
                        document.getElementById('total-guests').textContent = '0';
                        document.getElementById('selected-accommodations-count').textContent = '0 selected';
                        document.getElementById('proceed-btn').disabled = true;
                        document.getElementById('skip-btn').disabled = true;

                        if (typeof window.updateBookingSummary === 'function') window.updateBookingSummary();
                        setTimeout(function() {
                            window.location.href = '{{ route("booking.staycation") }}?clear=1';
                        }, 1200);
                    </script>
            @endif
        </div>
    <x-header active="booking" />
    <div class="min-h-screen">
@php
    // Initialize / read booking session
    $booking = session('booking', [
        'checkin'        => null,
        'checkout'       => null,
        'accommodations' => [],
        'booking_type'   => 'general',
        'timestamp'      => now()->timestamp,
        'completed_steps'=> [],
        'current_step'   => 1,
    ]);

    $bookingType  = $booking['booking_type'] ?? 'general';
    $stepNumber   = '1'; // landing page
    $totalSteps   = $bookingType === 'staycation' ? '3' : '4';
    $backLink     = url('/home');

    $completedSteps  = $booking['completed_steps'] ?? [];
    $step1Completed  = in_array(1, $completedSteps);

    $checkinDate  = $booking['checkin'] ?? null;
    $checkoutDate = $booking['checkout'] ?? null;
    $datesSelected = !empty($checkinDate) && !empty($checkoutDate);

    // Calculate nights
    $nights = 0;
    if ($datesSelected) {
        $start = new DateTime($checkinDate);
        $end = new DateTime($checkoutDate);
        $diff = $start->diff($end);
        $nights = max(1, $diff->days);
    }

    // Check if accommodations are selected
    $accommodationsSelected = !empty($booking['accommodations']) && count(array_filter($booking['accommodations'])) > 0;

    // For staycation, adjust step display
    if ($bookingType === 'staycation') {
        if ($accommodationsSelected) {
            $stepNumber = '1'; // Show as step 1 (completed booking)
        }
    }

    // ---- NEW: group staycations into glamping + camping & compute stats ----
    $staycations     = $staycations ?? [];
    $glampingStaycations = [];
    $campingStaycations  = [];

    foreach ($staycations as $s) {
        if (($s['type'] ?? null) === 'glamping') {
            $glampingStaycations[] = $s;
        } elseif (($s['type'] ?? null) === 'camping') {
            $campingStaycations[] = $s;
        }
    }

    $glampingMinPrice = !empty($glampingStaycations)
        ? min(array_column($glampingStaycations, 'price'))
        : 0;
    $glampingCount    = count($glampingStaycations);

    $campingMinPrice = !empty($campingStaycations)
        ? min(array_column($campingStaycations, 'price'))
        : 0;
    $campingCount    = count($campingStaycations);

    // Category hero images + descriptions
    $glampingImage = asset('assets/images/carousel/Glamping-1.jpg');
    $campingImage  = asset('assets/images/carousel/Car-camping3.jpg');

    $glampingDescription = 'Luxury camping experience with premium amenities, king-size bed, private bathroom, and panoramic views.';
    $campingDescription  = 'Traditional camping experiences with vehicle access and natural settings.';
@endphp

@php
$galleryData = [
    'glamping-standard' => [
        'images' => [
            asset('assets/images/carousel/Glamping-1.jpg'),
            asset('assets/images/carousel/wheretostay.jpg'),
            asset('assets/images/carousel/Calaia-26.jpg'),
            asset('assets/images/carousel/our-farm.jpg'),
            asset('assets/images/carousel/our-farm2.jpg'),
            asset('assets/images/carousel/f&b.jpg'),
            asset('assets/images/carousel/menu1.jpg'),
            asset('assets/images/carousel/menu3.jpg'),
            asset('assets/images/carousel/organic.png'),
            asset('assets/images/carousel/organic1.jpg'),
            asset('assets/images/carousel/farm.jpg'),
            asset('assets/images/carousel/activities-1.jpg')
        ]
    ],
    'glamping-premium' => [
        'images' => [
            asset('assets/images/carousel/Glamping-1.jpg'),
            asset('assets/images/carousel/wheretostay.jpg'),
            asset('assets/images/carousel/Calaia-26.jpg'),
            asset('assets/images/carousel/our-farm.jpg'),
            asset('assets/images/carousel/our-farm2.jpg'),
            asset('assets/images/carousel/f&b.jpg'),
            asset('assets/images/carousel/menu1.jpg'),
            asset('assets/images/carousel/menu3.jpg'),
            asset('assets/images/carousel/organic.png'),
            asset('assets/images/carousel/organic1.jpg'),
            asset('assets/images/carousel/farm.jpg'),
            asset('assets/images/carousel/activities-1.jpg')
        ]
    ],
    'camping-dome' => [
        'images' => [
            asset('assets/images/carousel/BYOT-2.jpg'),
            asset('assets/images/carousel/Car-camping3.jpg'),
            asset('assets/images/carousel/bonfire-5.jpg'),
            asset('assets/images/carousel/biking-4.jpg'),
            asset('assets/images/carousel/games-grounds4.jpg'),
            asset('assets/images/carousel/go-cart2.jpg'),
            asset('assets/images/carousel/minikalesa.jpg'),
            asset('assets/images/carousel/animal and wellness.jpg'),
            asset('assets/images/carousel/groups.jpg'),
            asset('assets/images/carousel/whattodo.jpg'),
            asset('assets/images/carousel/SET MEALS2.jpg'),
            asset('assets/images/carousel/f&b.jpg')
        ]
    ],
    'camping-bell' => [
        'images' => [
            asset('assets/images/carousel/BYOT-2.jpg'),
            asset('assets/images/carousel/Car-camping3.jpg'),
            asset('assets/images/carousel/bonfire-5.jpg'),
            asset('assets/images/carousel/biking-4.jpg'),
            asset('assets/images/carousel/games-grounds4.jpg'),
            asset('assets/images/carousel/go-cart2.jpg'),
            asset('assets/images/carousel/minikalesa.jpg'),
            asset('assets/images/carousel/animal and wellness.jpg'),
            asset('assets/images/carousel/groups.jpg'),
            asset('assets/images/carousel/whattodo.jpg'),
            asset('assets/images/carousel/SET MEALS2.jpg'),
            asset('assets/images/carousel/f&b.jpg')
        ]
    ],
    'camping-byot' => [
        'images' => [
            asset('assets/images/carousel/BYOT-2.jpg'),
            asset('assets/images/carousel/Car-camping3.jpg'),
            asset('assets/images/carousel/bonfire-5.jpg'),
            asset('assets/images/carousel/biking-4.jpg'),
            asset('assets/images/carousel/games-grounds4.jpg'),
            asset('assets/images/carousel/go-cart2.jpg'),
            asset('assets/images/carousel/minikalesa.jpg'),
            asset('assets/images/carousel/animal and wellness.jpg'),
            asset('assets/images/carousel/groups.jpg'),
            asset('assets/images/carousel/whattodo.jpg'),
            asset('assets/images/carousel/SET MEALS2.jpg'),
            asset('assets/images/carousel/f&b.jpg')
        ]
    ],
    'camping-car' => [
        'images' => [
            asset('assets/images/carousel/BYOT-2.jpg'),
            asset('assets/images/carousel/Car-camping3.jpg'),
            asset('assets/images/carousel/bonfire-5.jpg'),
            asset('assets/images/carousel/biking-4.jpg'),
            asset('assets/images/carousel/games-grounds4.jpg'),
            asset('assets/images/carousel/go-cart2.jpg'),
            asset('assets/images/carousel/minikalesa.jpg'),
            asset('assets/images/carousel/animal and wellness.jpg'),
            asset('assets/images/carousel/groups.jpg'),
            asset('assets/images/carousel/whattodo.jpg'),
            asset('assets/images/carousel/SET MEALS2.jpg'),
            asset('assets/images/carousel/f&b.jpg')
        ]
    ]
];
@endphp


<div class="pt-6 md:pt-12"
     data-root-dir="{{ url('/') }}"
     data-accommodation-data='@json($accommodationData ?? [])'
     data-selected-activities='@json($booking['selected_activities'] ?? [])'>

    {{-- Header --}}
    <div class="text-center mb-10">
        <h1 class="text-2xl font-bold tracking-tight" style="font-family: 'Gotham Book', sans-serif;">Book Staycation</h1>
        <div class="border-b-2 border-gray-900 w-20 md:w-24 mx-auto my-3"></div>
        <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">Plan your perfect getaway at Calaia Nature Park</p>
    </div>

<div class="mx-0 md:mx-10">
   {{-- Progress + nav --}}
   <div class="mb-6">
       {{-- Progress indicator --}}
       <div class="mb-4">
           {{-- limit width + center --}}
           <div class="max-w-4xl md:max-w-5xl mx-auto">
               <div class="flex justify-between items-center mb-2">
                   <small class="text-gray-500 text-sm">
                       @if($bookingType === 'staycation')
                           Step 1 of 3: Select Your Staycation
                       @else
                           Steps {{ $stepNumber }} of {{ $totalSteps }}
                       @endif
                   </small>
                   <small class="text-gray-500 text-sm">33% Complete</small>
               </div>
               <div class="w-full bg-gray-200 rounded-full h-1.5 md:h-2">
                   <div
                       class="bg-[#8f5d54] h-1.5 md:h-2 rounded-full transition-all duration-300"
                       style="width: 33%">
                   </div>
               </div>
           </div>
       </div>

       {{-- Back + guest mode --}}
       <div class="flex items-end justify-between">
           <a href="{{ $backLink }}"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
               <i class="bi bi-arrow-left mr-2"></i>
               Back
           </a>

           <div class="flex items-center gap-2">
               @if (session()->has('user.is_guest') && session('user.is_guest'))
                   <span
                       class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold text-white shadow"
                       style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: 1px solid rgba(255,255,255,.2);">
                       <i class="bi bi-person mr-1 text-sm"></i> Guest Mode
                   </span>
               @endif
           </div>
       </div>
   </div>

   {{-- 3 cards sa isang grid: Glamping, Camping, Booking Summary --}}
   <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
       {{-- Glamping card --}}
       <div class="bg-white rounded-2xl shadow-[0_18px_40px_rgba(15,23,42,0.08)] border border-gray-100 overflow-hidden flex flex-col">
           <img
               src="{{ $glampingImage }}"
               alt="Glamping Options"
               class="w-full object-cover"
               style="height: 280px;">

           <div class="flex flex-col p-6 h-full">
               <div class="flex items-start justify-between gap-3 mb-2">
                   <h3 class="text-2xl font-extrabold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">
                       Glamping Options
                   </h3>
                   <span
                       class="inline-flex items-center px-3 py-1 rounded-full text-base font-semibold"
                       style="background: #ffc44c; color: #2b1b00;">
                       GLAMPING
                   </span>
               </div>

               <p class="text-xs text-gray-600 leading-relaxed mb-3" style="font-family: 'Gotham Book', sans-serif;">
                   {{ $glampingDescription }}
               </p>

               <p class="text-xs text-gray-500 mb-4" style="font-family: 'Gotham Book', sans-serif;">
                   Includes:
                   <span class="font-semibold text-gray-800">Standard</span> and
                   <span class="font-semibold text-gray-800">Premium</span> glamping tents.
               </p>

               @if($glampingCount > 0)
                   <p class="font-semibold mb-1" style="color: #7a7d52; font-family: 'Gotham Book', sans-serif; font-size: 16px;">
                       Starts at ₱5,760/night (Weekdays: Sun.-Thu.)<br>• ₱7,200/night (Weekends: Fri.-Sat.)
                   </p>
                   <p class="text-xs text-gray-500 mb-6" style="font-family: 'Gotham Book', sans-serif;">
                       {{ $glampingCount }} {{ Str::plural('option', $glampingCount) }} available
                   </p>
               @else
                   <p class="text-base text-gray-400 mb-6">No glamping options available.</p>
               @endif

               <button
                   type="button"
                   class="select-category-btn mt-auto w-full inline-flex items-center justify-center rounded-full text-lg font-semibold text-white px-6 py-3 transition"
                   data-category="glamping"
                   data-staycations='@json($glampingStaycations)'
                   @if($glampingCount === 0) disabled @endif
                   style="background-color:#8f5d54; box-shadow:0 10px 20px rgba(143,93,84,0.35);">
                   Select Glamping Options
               </button>
           </div>
       </div>

       {{-- Camping card --}}
       <div class="bg-white rounded-2xl shadow-[0_18px_40px_rgba(15,23,42,0.08)] border border-gray-100 overflow-hidden flex flex-col">
           <img
               src="{{ $campingImage }}"
               alt="Camping Options"
               class="w-full object-cover"
               style="height: 280px;">

           <div class="flex flex-col p-6 h-full">
               <div class="flex items-start justify-between gap-3 mb-2">
                   <h3 class="text-2xl font-extrabold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">
                       Camping Options
                   </h3>
                   <span
                       class="inline-flex items-center px-3 py-1 rounded-full text-base font-semibold"
                       style="background:#31b44b; color:#ffffff;">
                       CAMPING
                   </span>
               </div>

               <p class="text-xs text-gray-600 leading-relaxed mb-3" style="font-family: 'Gotham Book', sans-serif;">
                   {{ $campingDescription }}
               </p>

               <p class="text-xs text-gray-500 mb-4" style="font-family: 'Gotham Book', sans-serif;">
                   Includes:
                   <span class="font-semibold text-gray-800">Bell Tent</span>,
                   <span class="font-semibold text-gray-800">Dome Tent</span>,
                   <span class="font-semibold text-gray-800">BYOT</span>, and
                   <span class="font-semibold text-gray-800">Car Camping</span>.
               </p>

               @if($campingCount > 0)
                   <p class="font-semibold mb-1" style="color: #7a7d52; font-family: 'Gotham Book', sans-serif; font-size: 16px;">
                       Starts at ₱300/night (BYOT/Car Camping)<br>• ₱3,000/night (Bell/Dome Tents)
                   </p>
                   <p class="text-xs text-gray-500 mb-6" style="font-family: 'Gotham Book', sans-serif;">
                       {{ $campingCount }} {{ Str::plural('option', $campingCount) }} available
                   </p>
               @else
                   <p class="text-base text-gray-400 mb-6">No camping options available.</p>
               @endif

               <button
                   type="button"
                   class="select-category-btn mt-auto w-full inline-flex items-center justify-center rounded-full text-lg font-semibold text-white px-6 py-3 transition"
                   data-category="camping"
                   data-staycations='@json($campingStaycations)'
                   @if($campingCount === 0) disabled @endif
                   style="background-color:#8f5d54; box-shadow:0 10px 20px rgba(143,93,84,0.35);">
                   Select Camping Options
               </button>
           </div>
       </div>

       {{-- Booking summary card (right) --}}
       <div class="bg-white rounded-xl shadow-md p-6 lg:sticky lg:top-6">
           <h3 class="text-2xl font-bold text-gray-900 mb-4" style="font-family: 'Gotham Book', sans-serif;">Booking Summary</h3>

           @if($datesSelected)
               <div class="space-y-3">
                   <div class="flex justify-between items-center">
                       <span class="text-sm text-gray-600">Check-in:</span>
                       <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkinDate)->format('M j, Y') }}</span>
                   </div>
                   <div class="flex justify-between items-center">
                       <span class="text-sm text-gray-600">Check-out:</span>
                       <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($checkoutDate)->format('M j, Y') }}</span>
                   </div>
                   <div class="flex justify-between items-center">
                       <span class="text-sm text-gray-600">Nights:</span>
                       <span class="text-sm font-medium">{{ $nights }}</span>
                   </div>
                   <hr class="my-3">
                   <div class="flex justify-between items-center">
                       <span class="text-sm text-gray-600">Accommodations:</span>
                       <span class="text-sm font-medium" id="selected-accommodations-count">0 selected</span>
                   </div>
                   <div id="selected-accommodations-summary" class="mt-2 space-y-1">
                       <!-- JS will populate -->
                   </div>
                   <div class="flex justify-between items-center">
                       <span class="text-sm text-gray-600">Total Guests:</span>
                       <span class="text-sm font-medium" id="total-guests">0</span>
                   </div>


                   <div class="flex justify-between items-center text-lg font-bold">
                       <span>Total:</span>
                       <span id="total-price">₱ 0</span>
                   </div>
               </div>

               <div class="mt-6 space-y-3">
                   <button class="w-full bg-emerald-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed" id="proceed-btn" disabled onclick="proceedToActivities(event)">
                       Proceed to Activities
                   </button>

                   <button class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-gray-700 transition disabled:opacity-50 disabled:cursor-not-allowed" id="skip-btn" disabled onclick="skipToPayment()">
                       Skip Activities & Go to Payment
                   </button>
               </div>

               <div class="mt-6 pt-6 border-t border-gray-200">
                   <div class="text-center">
                       <h4 class="text-sm font-semibold text-gray-900 mb-3" style="font-family: 'Gotham Book', sans-serif;">Secure Booking</h4>
                       <div class="flex justify-center items-center space-x-4">
                           <div class="flex items-center">
                               <i class="fas fa-lock text-green-600 mr-2"></i>
                               <span class="text-xs text-gray-600">SSL Secured</span>
                           </div>
                           <div class="flex items-center">
                               <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                               <span class="text-xs text-gray-600">Data Protected</span>
                           </div>
                       </div>
                       <p class="text-xs text-gray-500 mt-2">Your information is safe with us</p>
                   </div>
               </div>
           @else
               <p class="text-xs text-gray-500" style="font-family: 'Gotham Book', sans-serif;">Please select your dates to see the booking summary.</p>
           @endif
       </div>
   </div>

   {{-- Selected accommodations (cards under the 3 main cards) --}}
   <div class="mt-6 mb-8 w-full">
       <div
           id="selected-accommodations-container"
           style="display:none;"
           aria-hidden="true"
       >
           <!-- Selected staycations hidden by design: small-card UI removed -->
       </div>
   </div>
</div>
</div>
</div>

{{-- Modals --}}
{{-- Glamping Modal --}}
<div id="glamping-modal" class="fixed inset-0 hidden z-50" role="dialog" aria-modal="true" aria-labelledby="glamping-modal-title">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 transition-opacity duration-300" id="glamping-modal-backdrop" onclick="if (!event.target.closest('#glamping-modal-content')) closeModal('glamping-modal')"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-hidden border border-gray-200 relative z-10 transform transition-all duration-300 scale-95 opacity-0" id="glamping-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="glamping-modal-title" class="font-bold text-gray-900" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Select Glamping Options</h2>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none focus:outline-none focus:ring-2 focus:ring-gray-400 rounded" data-modal="glamping-modal" aria-label="Close modal" style="line-height: 1;">
                        ×
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="glamping-options">
                        @foreach($glampingStaycations as $staycation)
                            <div class="staycation-card bg-white border-2 border-gray-200 rounded-xl p-4 flex items-center gap-4 hover:border-[#8f5d54] hover:shadow-md transition-all duration-200 cursor-pointer" data-staycation-id="{{ $staycation['id'] }}">
                                <input type="checkbox"
                                       class="staycation-checkbox hidden"
                                       data-category="glamping"
                                       data-staycation='@json($staycation)'
                                       id="glamping-{{ $staycation['id'] }}">
                                <label for="glamping-{{ $staycation['id'] }}" class="flex-1 cursor-pointer flex items-center gap-3">
                                    <div class="selection-indicator w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                        <div class="w-3 h-3 bg-[#8f5d54] rounded-full opacity-0 transition-opacity"></div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">{{ $staycation['name'] }}</div>
                                        <div class="text-gray-600 text-sm" style="font-family: 'Gotham Book', sans-serif;">₱{{ number_format($staycation['price']) }}/night</div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-400" data-modal="glamping-modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Camping Modal --}}
<div id="camping-modal" class="fixed inset-0 hidden z-50" role="dialog" aria-modal="true" aria-labelledby="camping-modal-title">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 transition-opacity duration-300" id="camping-modal-backdrop" onclick="if (!event.target.closest('#camping-modal-content')) closeModal('camping-modal')"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-hidden border border-gray-200 relative z-10 transform transition-all duration-300 scale-95 opacity-0" id="camping-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="camping-modal-title" class="font-bold text-gray-900" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Select Camping Options</h2>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none focus:outline-none focus:ring-2 focus:ring-gray-400 rounded" data-modal="camping-modal" aria-label="Close modal" style="line-height: 1;">
                        ×
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="camping-options">
                        @foreach($campingStaycations as $staycation)
                            <div class="staycation-card bg-white border-2 border-gray-200 rounded-xl p-4 flex items-center gap-4 hover:border-[#8f5d54] hover:shadow-md transition-all duration-200 cursor-pointer" data-staycation-id="{{ $staycation['id'] }}">
                                <input type="checkbox"
                                       class="staycation-checkbox hidden"
                                       data-category="camping"
                                       data-staycation='@json($staycation)'
                                       id="camping-{{ $staycation['id'] }}">
                                <label for="camping-{{ $staycation['id'] }}" class="flex-1 cursor-pointer flex items-center gap-3">
                                    <div class="selection-indicator w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center">
                                        <div class="w-3 h-3 bg-[#8f5d54] rounded-full opacity-0 transition-opacity"></div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <div class="font-semibold text-gray-900" style="font-family: 'Gotham Book', sans-serif;">{{ $staycation['name'] }}</div>
                                            @if(str_contains(strtolower($staycation['name']), 'byot'))
                                                <div class="relative group">
                                                    <i class="fas fa-info-circle text-gray-400 text-xs cursor-help"></i>
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                        Bring Your Own Tent
                                                    </div>
                                                </div>
                                            @elseif(str_contains(strtolower($staycation['name']), 'dome'))
                                                <div class="relative group">
                                                    <i class="fas fa-info-circle text-gray-400 text-xs cursor-help"></i>
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                        Spacious tent for groups
                                                    </div>
                                                </div>
                                            @elseif(str_contains(strtolower($staycation['name']), 'bell'))
                                                <div class="relative group">
                                                    <i class="fas fa-info-circle text-gray-400 text-xs cursor-help"></i>
                                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                                        Traditional bell-shaped tent
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-gray-600 text-sm" style="font-family: 'Gotham Book', sans-serif;">₱{{ number_format($staycation['price']) }}/night</div>
                                        <div class="text-xs text-green-600 mt-1" style="font-family: 'Gotham Book', sans-serif;">
                                            <i class="fas fa-check-circle mr-1"></i>Available ({{ $staycation['capacity'] ?? 6 }} guests max)
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-400" data-modal="camping-modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pet Policy Modal --}}
<div id="pet-policy-modal" class="fixed inset-0 hidden z-50" role="dialog" aria-modal="true" aria-labelledby="pet-policy-modal-title">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 transition-opacity duration-300" id="pet-policy-modal-backdrop" onclick="if (!event.target.closest('#pet-policy-modal-content')) closeModal('pet-policy-modal')"></div>

        <!-- Modal content -->
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden border border-gray-200 relative z-10 transform transition-all duration-300 scale-95 opacity-0" id="pet-policy-modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="pet-policy-modal-title" class="font-bold text-gray-900" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Pet Policy</h2>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600 text-3xl font-light leading-none focus:outline-none focus:ring-2 focus:ring-gray-400 rounded" data-modal="pet-policy-modal" aria-label="Close modal" style="line-height: 1;">
                        ×
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto text-center">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center justify-center">
                            <span class="text-3xl mr-2">🐾</span>
                            Pets are Welcome at Calaia Nature Park!
                        </h3>
                        <p class="text-lg text-gray-700 leading-relaxed">We're happy to welcome well-socialized, non-aggressive pets to Calaia! To ensure a safe and enjoyable experience for everyone, including our animal residents and guests, we kindly ask pet owners to follow these guidelines:</p>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                                <span class="text-2xl mr-2">🐶</span>
                                Owner Responsibility
                            </h4>
                            <p class="text-gray-700" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Pet owners are fully responsible for the behavior and care of their pets at all times. Pets must never be left unattended.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                                <span class="text-2xl mr-2">🐕</span>
                                Leash Policy
                            </h4>
                            <p class="text-gray-700" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Pets may be off-leash only when under the close and careful supervision of their owners.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                                <span class="text-2xl mr-2">🦙</span>
                                Respect for Farm Animals
                            </h4>
                            <p class="text-gray-700" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Please ensure your pets do not disturb or chase the grazing animals on the property.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                                <span class="text-2xl mr-2">🏕️</span>
                                Overnight Stays & Pet Fee
                            </h4>
                            <p class="text-gray-700 mb-2" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Kindly inform us upon booking if you will be bringing a pet.</p>
                            <p class="text-gray-700 mb-2" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">If your pet will stay inside the glamping tent, a pet fee of ₱2,000 per pet applies and must be paid in full to confirm your booking.</p>
                            <p class="text-gray-700" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Any damage to the tent, bedding, or furnishings caused by pets will be charged accordingly.</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-900 mb-2 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                                <span class="text-2xl mr-2">📋</span>
                                Health & Cleanliness
                            </h4>
                            <p class="text-gray-700" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">All pets must be up to date on vaccinations (including rabies) and treated for external parasites. We will kindly ask for the date of your pet's last parasite treatment.</p>
                        </div>

                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-semibold text-red-900 mb-2 flex items-center justify-center" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                                <span class="text-2xl mr-2">⚠️</span>
                                Damage & Liability
                            </h4>
                            <p class="text-red-800" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">Owners will be held liable for any damage caused to property or injury inflicted on farm animals, other pets, fellow guests, or Calaia staff.</p>
                        </div>
                    </div>

                    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="font-semibold text-green-900 flex items-center justify-center" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                            <span class="text-2xl mr-2">🐾🌿</span>
                            We appreciate your cooperation and understanding. Let's work together to keep Calaia a safe, peaceful, and pet-friendly space for all!
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-end">
                        <button type="button" class="close-modal px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-gray-400" data-modal="pet-policy-modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    
      {{-- Gallery Modal --}}
      <div id="gallery-modal" class="fixed inset-0 hidden z-50" role="dialog" aria-modal="true" aria-labelledby="gallery-modal-title">
        <div class="flex items-center justify-center min-h-screen px-4">
          <!-- Backdrop -->
          <div class="absolute inset-0 transition-opacity duration-300" onclick="closeGallery()"></div>
    
          <!-- Modal content -->
          <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden border border-gray-200 relative z-10 transform transition-all duration-300 scale-95 opacity-0" id="gallery-modal-content">
            <div class="p-6">
              <div class="flex justify-between items-center mb-6">
                <h2 id="gallery-modal-title" class="font-bold text-gray-900" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Photo Gallery</h2>
                <button type="button" class="text-gray-400 hover:text-gray-600 text-3xl font-light leading-none focus:outline-none focus:ring-2 focus:ring-gray-400 rounded" onclick="closeGallery()" style="line-height: 1;">
                  ×
                </button>
              </div>
    
              <div class="flex items-center justify-center h-96 bg-black">
                <img id="gallery-image" class="max-w-full max-h-full object-contain" alt="Gallery image">
              </div>
    
              <div class="flex items-center justify-center gap-4 p-4 border-t">
                <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" onclick="galleryPrev()"><i class="bi bi-chevron-left"></i> Prev</button>
                <span id="gallery-counter" class="px-3 py-2 rounded-md text-gray-700 font-semibold">1 / 3</span>
                <button class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300" onclick="galleryNext()">Next <i class="bi bi-chevron-right"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    
      {{-- Toast --}}
      <div class="toast fixed bottom-4 right-4 items-center text-white border-0 rounded-lg shadow-lg px-4 py-3 hidden z-60"
           role="alert" aria-live="assertive" aria-atomic="true">
          <div class="flex items-center w-full">
              <div class="toast-body text-sm flex-1"></div>
              <button type="button"
                      class="ml-3 text-white text-xs font-semibold uppercase tracking-wide btn-close"
                      data-bs-dismiss="toast"
                      aria-label="Close">
                  ✕
              </button>
          </div>
      </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  </body>
  <script>
    const el = document.querySelector('.pt-6.md\\:pt-12');
    window.ROOT_DIR = el.dataset.rootDir;
    window.accommodationData = JSON.parse(el.dataset.accommodationData || '[]');
    window.selectedAccommodations = {}; // Store selected accommodations by staycation.id
    window.galleryData = <?php echo json_encode($galleryData); ?>;
    window.galleryImages = [];
    window.galleryCurrentIndex = 0;

    // Storage key for staycation selections
// Global dates from Blade
const CHECKIN = '{{ $checkinDate }}';
const CHECKOUT = '{{ $checkoutDate }}';

// Single source of truth for all pricing
function computeStaycationTotals(staycation, options = {}) {
    const {
        guests = 1,
        checkinStr = CHECKIN,
        checkoutStr = CHECKOUT,
        numPets = 0,
        hasPetAgreement = false,
        hasBreakfast = false
    } = options;

    const emptyResult = {
        basePriceTotal: 0,
        extraPriceTotal: 0,
        petFee: 0,
        breakfastFee: 0,
        total: 0,
        nights: 0
    };

    if (!checkinStr || !checkoutStr) return emptyResult;

    const checkin = new Date(checkinStr);
    const checkout = new Date(checkoutStr);
    const diffMs = checkout - checkin;
    if (!isFinite(diffMs) || diffMs <= 0) return emptyResult;

    const nights = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
    const name = (staycation.name || '').toLowerCase();

    let basePriceTotal = 0;
    let extraPriceTotal = 0;

    // ---- BASE ACCOMMODATION PRICING ----
    if (name.includes('standard') || name.includes('premium')) {
        let current = new Date(checkin);
        while (current < checkout) {
            const day = current.getDay(); // 0-6
            const isWeekend = (day === 5 || day === 6); // Fri & Sat
            let basePrice = 0;

            if (name.includes('standard')) basePrice = isWeekend ? 6000 : 4800;
            if (name.includes('premium')) basePrice = isWeekend ? 7200 : 5760;

            const extraGuests = Math.max(0, guests - 4);
            basePriceTotal += basePrice;
            extraPriceTotal += extraGuests * 400;

            current.setDate(current.getDate() + 1);
        }
    } else if (name.includes('bell') || name.includes('dome')) {
        // Dome / Bell: flat 3000 per night, up to 8
        basePriceTotal = 3000 * nights;
    } else if (name.includes('byot') || name.includes('car')) {
        // BYOT / Car: 300 * guests * nights + 500 * guests * nights
        basePriceTotal = (300 * nights) + (500 * guests * nights);
    }

    // ---- PET FEE ----
    const petFee = (hasPetAgreement && numPets > 0) ? 2000 * numPets : 0;

    // ---- BREAKFAST FEE ----
    const breakfastFee = hasBreakfast ? 500 * guests : 0;

    const total = basePriceTotal + extraPriceTotal + petFee + breakfastFee;

    return {
        basePriceTotal,
        extraPriceTotal,
        petFee,
        breakfastFee,
        total,
        nights
    };
}
    const STAYCATION_STORAGE_KEY = 'staycation_selections';

    // Save selections to storage
    function saveSelectionsToStorage() {
        try {
            const data = {
                selectedAccommodations: window.selectedAccommodations,
                timestamp: Date.now()
            };
            sessionStorage.setItem(STAYCATION_STORAGE_KEY, JSON.stringify(data));
        } catch (error) {
            console.warn('Failed to save selections to storage:', error);
        }
    }

    // Load selections from storage
    function loadSelectionsFromStorage() {
        try {
            const stored = sessionStorage.getItem(STAYCATION_STORAGE_KEY);
            if (stored) {
                const data = JSON.parse(stored);
                // Check if data is not too old (24 hours)
                const age = Date.now() - data.timestamp;
                if (age < 24 * 60 * 60 * 1000) { // 24 hours
                    window.selectedAccommodations = data.selectedAccommodations || {};
                    return true;
                }
            }
        } catch (error) {
            console.warn('Failed to load selections from storage:', error);
        }
        return false;
    }

    // Clear storage (call when booking is completed or user starts fresh)
    function clearSelectionsStorage() {
        sessionStorage.removeItem(STAYCATION_STORAGE_KEY);
    }


    window.FeeCalculator = {
        calculateTotal: function(accommodations, checkin, checkout) {
            if (!checkin || !checkout) return 0;

            let total = 0;

            Object.values(accommodations).forEach(acc => {
                if (!acc || !acc.staycation) return;

                const result = computeStaycationTotals(acc.staycation, {
                    guests: acc.guests || 1,
                    checkinStr: checkin,
                    checkoutStr: checkout,
                    numPets: acc.num_pets || 0,
                    hasPetAgreement: acc.pet_agreement,
                    hasBreakfast: acc.has_breakfast
                });

                total += result.total;
            });

            return total;
        }
    };


    window.updateBookingSummary = function() {
        const summaryContainer = document.getElementById('selected-accommodations-summary');
        if (!summaryContainer) return;

        summaryContainer.innerHTML = '';
        let selectedCount = 0;
        let totalPrice = 0;
        let totalGuests = 0;

        Object.entries(window.selectedAccommodations).forEach(([key, accommodation]) => {
            if (!accommodation || !accommodation.staycation) return;

            selectedCount++;

            const guests = accommodation.guests || 1;
            totalGuests += guests;
            const staycation = accommodation.staycation;
            const name = staycation.name;

            const result = computeStaycationTotals(staycation, {
                guests,
                checkinStr: CHECKIN,
                checkoutStr: CHECKOUT,
                numPets: accommodation.num_pets || 0,
                hasPetAgreement: accommodation.pet_agreement,
                hasBreakfast: accommodation.has_breakfast
            });

            totalPrice += result.total;

            const summaryItem = document.createElement('div');
            summaryItem.className = 'text-xs text-gray-600 space-y-1';

            const nights = result.nights || 1;

            // Base accommodation line with remove button
            const accommodationDiv = document.createElement('div');
            accommodationDiv.className = 'flex justify-between items-center';

            const perNightBase = result.basePriceTotal > 0
                ? Math.round(result.basePriceTotal / nights)
                : 0;

            const accommodationText = document.createElement('span');
            accommodationText.textContent =
                `ACCOMMODATION: ${name} - ₱ ${perNightBase.toLocaleString()}/night (${guests} guest${guests > 1 ? 's' : ''})`;
            accommodationDiv.appendChild(accommodationText);

            // Add remove button
            const removeButton = document.createElement('button');
            removeButton.className = 'text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors';
            removeButton.innerHTML = '<i class="fas fa-trash-alt mr-1"></i>';
            removeButton.dataset.key = key;
            removeButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Remove from selected accommodations
                delete window.selectedAccommodations[key];

                // Update UI
                window.updateBookingSummary();

                // Show toast notification
                showToast(`Removed ${name} from your booking`, 'info');
            });

            accommodationDiv.appendChild(removeButton);
            summaryItem.appendChild(accommodationDiv);

            // Extra mattress line
            if (result.extraPriceTotal > 0) {
                const extraGuests = Math.max(0, guests - 4);
                const perNightExtra = Math.round(result.extraPriceTotal / nights);

                const extraDiv = document.createElement('div');
                extraDiv.textContent =
                    `Extra Mattress (${extraGuests} guest${extraGuests > 1 ? 's' : ''}): ₱ ${perNightExtra.toLocaleString()}/night`;
                summaryItem.appendChild(extraDiv);
            }

            // Pet fee
            if (result.petFee > 0) {
                const numPets = accommodation.num_pets || 0;
                const petDiv = document.createElement('div');
                petDiv.textContent =
                    `Pet Fee (${numPets} pet${numPets > 1 ? 's' : ''}): ₱ ${result.petFee.toLocaleString()}`;
                summaryItem.appendChild(petDiv);
            }

            // Breakfast fee
            if (result.breakfastFee > 0) {
                const breakfastDiv = document.createElement('div');
                breakfastDiv.textContent =
                    `Breakfast (${guests} guest${guests > 1 ? 's' : ''}): ₱ ${result.breakfastFee.toLocaleString()}`;
                summaryItem.appendChild(breakfastDiv);
            }

            summaryContainer.appendChild(summaryItem);
        });

        // Activities are handled on the activities page, not here
        // const selectedActivities = JSON.parse(el.dataset.selectedActivities || '[]');
        // const activitiesTotal = selectedActivities.reduce(
        //     (sum, activity) => sum + (activity.price * activity.guests),
        //     0
        // );
        // totalPrice += activitiesTotal;

        const totalPriceEl = document.getElementById('total-price');
        if (totalPriceEl) totalPriceEl.textContent = `₱ ${totalPrice.toLocaleString()}`;

        const totalGuestsEl = document.getElementById('total-guests');
        if (totalGuestsEl) totalGuestsEl.textContent = totalGuests;

        const selectedAccText = document.getElementById('selected-accommodations-count');
        if (selectedAccText) selectedAccText.textContent = `${selectedCount} selected`;

        const proceedBtn = document.getElementById('proceed-btn');
        const skipBtn = document.getElementById('skip-btn');
        if (proceedBtn) proceedBtn.disabled = selectedCount === 0;
        if (skipBtn) skipBtn.disabled = selectedCount === 0;

        saveSelectionsToStorage();
    };

    window.initializeBookingSummary = function() {
        window.updateBookingSummary();
    };




    // Toast function
    function showToast(message, type = 'info') {
        const toastEl = document.querySelector('.toast');
        if (toastEl) {
            const toastBody = toastEl.querySelector('.toast-body');
            if (toastBody) {
                toastBody.textContent = message;
            }
            // Set color based on type
            toastEl.classList.remove('bg-emerald-600', 'bg-red-600', 'bg-blue-600');
            if (type === 'success') {
                toastEl.classList.add('bg-emerald-600');
            } else if (type === 'error') {
                toastEl.classList.add('bg-red-600');
            } else {
                toastEl.classList.add('bg-blue-600');
            }
            toastEl.classList.remove('hidden');
            setTimeout(() => {
                toastEl.classList.add('hidden');
            }, 3000);
        }
    }



    // --- Display selected accommodation card ---
    function displaySelectedAccommodation(key, acc) {
        // Disabled: selected-accommodations small-card UI removed.
        // This function is intentionally a no-op to prevent DOM rendering of the
        // small selected-card. Selections are still stored in `window.selectedAccommodations`.
        return;
    }

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            // Load saved selections
            if (loadSelectionsFromStorage()) {
                // Update UI with loaded selections
                Object.entries(window.selectedAccommodations).forEach(([key, acc]) => {
                    displaySelectedAccommodation(key, acc);
                });
                window.updateBookingSummary();
            }
    
            if (typeof window.initializeBookingSummary === 'function') {
                window.initializeBookingSummary();
            }
            initializeStaycationValidation();
        }, 100);
    });

    function initializeStaycationValidation() {
        // Modal functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('select-category-btn')) {
                const category = e.target.dataset.category;
                const staycations = JSON.parse(e.target.dataset.staycations || '[]');
                openCategoryModal(category, staycations);
            }

            if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
                const modalId = e.target.dataset.modal || e.target.closest('.close-modal').dataset.modal;
                closeModal(modalId);
            }

            if (e.target.classList.contains('select-staycation-modal')) {
                const staycationData = e.target.dataset.staycation;
                if (staycationData) {
                    try {
                        const staycation = JSON.parse(staycationData);
                        selectStaycationFromModal(staycation, e.target);
                    } catch (error) {
                        console.error('Error parsing staycation data:', error);
                        showToast('Error selecting staycation option', 'error');
                    }
                }
            }
        });
    }

    // Global variables for modal management
    let activeModal = null;
    let previousFocusElement = null;
    let focusableElements = [];

    // Handle staycation card selection
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('staycation-checkbox')) {
            const checkbox = e.target;
            const card = checkbox.closest('.staycation-card');
            const indicator = card.querySelector('.selection-indicator div');

            if (checkbox.checked) {
                card.classList.add('border-[#8f5d54]', 'bg-[#8f5d54]/5');
                card.classList.remove('border-gray-200');
                indicator.classList.remove('opacity-0');
                indicator.classList.add('opacity-100');
            } else {
                card.classList.remove('border-[#8f5d54]', 'bg-[#8f5d54]/5');
                card.classList.add('border-gray-200');
                indicator.classList.remove('opacity-100');
                indicator.classList.add('opacity-0');
            }
        }
    });

    // Allow clicking on card to toggle selection
    document.addEventListener('click', function(e) {
        const card = e.target.closest('.staycation-card');
        if (card && !e.target.closest('input, select, button, label')) {
            const checkbox = card.querySelector('.staycation-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            }
        }
    });

    // Handle guest +/- buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('guest-increment') || e.target.classList.contains('guest-decrement')) {
            e.preventDefault();
            const button = e.target;
            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;

            const currentValue = parseInt(input.value) || 0;
            const min = parseInt(input.min) || 0;
            const max = parseInt(input.max) || 50;

            let newValue;
            if (button.classList.contains('guest-increment')) {
                newValue = Math.min(currentValue + 1, max);
            } else {
                newValue = Math.max(currentValue - 1, min);
            }

            input.value = newValue;

            // Update pricing display
            updateModalPricing(button.closest('.bg-white.rounded-xl'));

            // Trigger change event for validation
            input.dispatchEvent(new Event('change'));
        }
    });

    // Update pricing display in modal (uses computeStaycationTotals)
    function updateModalPricing(card) {
        if (!card) return;

        const input = card.querySelector('.guest-input');
        const priceDisplay = card.querySelector('.pricing-display span');
        const checkbox = card.querySelector('.staycation-checkbox');

        if (!input || !priceDisplay || !checkbox) return;

        let staycation;
        try {
            staycation = JSON.parse(checkbox.dataset.staycation || '{}');
        } catch (e) {
            console.warn('Invalid staycation data in modal:', e);
            return;
        }

        const guests = parseInt(input.value) || 0;

        const petAgreementInput = card.querySelector('.pet-agreement');
        const petCountInput = card.querySelector('.pet-num-input');
        const hasPetsRadio = card.querySelector('.has-pets-radio:checked');

        const hasPetAgreement = !!(petAgreementInput && petAgreementInput.checked && hasPetsRadio && hasPetsRadio.value === 'yes');
        const numPets = (petCountInput && !petCountInput.disabled)
            ? (parseInt(petCountInput.value) || 0)
            : 0;

        const hasBreakfastRadio = card.querySelector('.has-breakfast-radio:checked');
        const hasBreakfast = hasBreakfastRadio && hasBreakfastRadio.value === 'yes';

        const result = computeStaycationTotals(staycation, {
            guests,
            checkinStr: CHECKIN,
            checkoutStr: CHECKOUT,
            numPets,
            hasPetAgreement,
            hasBreakfast
        });

        priceDisplay.textContent = result.total.toLocaleString();
    }

    // Update pricing when guest input changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('guest-input')) {
            updateModalPricing(e.target.closest('.bg-white.rounded-xl'));
        }
    });





    // Handle pet section radio buttons
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('has-pets-radio')) {
            const radio = e.target;
            const card = radio.closest('.bg-white.rounded-xl');
            const petDetails = card.querySelector('.pet-details');
            const petAgreement = card.querySelector('.pet-agreement');
            const petCountInput = card.querySelector('.pet-num-input');
            const petButtons = card.querySelectorAll('.pet-decrement, .pet-increment');

            if (radio.value === 'yes') {
                // Show pet details
                if (petDetails) petDetails.classList.remove('hidden');
                // Reset pet agreement and count
                if (petAgreement) petAgreement.checked = false;
                if (petCountInput) {
                    petCountInput.value = '0';
                    petCountInput.disabled = true;
                }
                if (petButtons) {
                    petButtons.forEach(btn => btn.disabled = true);
                }
            } else {
                // Hide pet details and reset
                if (petDetails) petDetails.classList.add('hidden');
                if (petAgreement) petAgreement.checked = false;
                if (petCountInput) {
                    petCountInput.value = '0';
                    petCountInput.disabled = true;
                }
                if (petButtons) {
                    petButtons.forEach(btn => btn.disabled = true);
                }
            }

            // Update pricing
            updateModalPricing(card);
        }
    });

    // Handle pet agreement checkbox
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('pet-agreement')) {
            const checkbox = e.target;
            const card = checkbox.closest('.bg-white.rounded-xl');
            const petCountInput = card.querySelector('.pet-num-input');
            const petButtons = card.querySelectorAll('.pet-decrement, .pet-increment');

            if (checkbox.checked) {
                // Enable pet count controls
                if (petCountInput) petCountInput.disabled = false;
                if (petButtons) {
                    petButtons.forEach(btn => btn.disabled = false);
                }
            } else {
                // Disable and reset pet count
                if (petCountInput) {
                    petCountInput.value = '0';
                    petCountInput.disabled = true;
                }
                if (petButtons) {
                    petButtons.forEach(btn => btn.disabled = true);
                }
            }

            // Update pricing
            updateModalPricing(card);
        }
    });

    // Handle pet count +/- buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pet-increment') || e.target.classList.contains('pet-decrement')) {
            e.preventDefault();
            const button = e.target;
            const targetId = button.dataset.target;
            const input = document.getElementById(targetId);
            if (!input || input.disabled) return;

            const currentValue = parseInt(input.value) || 0;
            const min = parseInt(input.min) || 0;
            const max = parseInt(input.max) || 2;

            let newValue;
            if (button.classList.contains('pet-increment')) {
                newValue = Math.min(currentValue + 1, max);
            } else {
                newValue = Math.max(currentValue - 1, min);
            }

            input.value = newValue;

            // Update pricing
            const card = button.closest('.bg-white.rounded-xl');
            updateModalPricing(card);
        }
    });

    // Configuration for staycation types
    const STAYCATION_CONFIG = {
        'glamping-standard': {
            displayPrice: 'Start at ₱4,800/night (Weekdays: Sun.-Thu.)<br>• ₱6,000/night (Weekends: Fri.-Sat.)',
            features: [
                'With Private Toilet & Bath',
                'Tent good for 4 pax (max of 6; +₱400/pax/night)',
                'Assigned heated shower with soap, shampoo & toilet with bidet (approx. 10 meters from tent)',
                'Access to Wi-Fi',
                'Private picnic table and fire pit/grill',
                'Complimentary coffee/tea and drinking water in tent',
                'Bathroom towels, dental & vanity kit, sleeping eye masks',
                'Convenience outlet',
                'Pet-friendly*'
            ]
        },
        'glamping-premium': {
            displayPrice: 'Start at ₱5,760/night (Weekdays: Sun.-Thu.)<br>• ₱7,200/night (Weekends: Fri.-Sat.)',
            features: [
                'With Private Toilet & Bath',
                'Tent good for 4 pax (max of 6; +₱400/pax/night)',
                'Exclusive heated shower with soap, shampoo & toilet with bidet, located right beside the tent',
                'Access to Wi-Fi',
                'Private picnic table and fire pit/grill',
                'Complimentary coffee/tea and drinking water in tent',
                'Bathroom towels, dental & vanity kit, sleeping eye masks',
                'Convenience outlet',
                'Pet-friendly*'
            ]
        },
        'camping-bell': {
            displayPrice: 'Starts at ₱3,000/tent/night',
            features: [
                'Fan-cooled, 25 sqm tent',
                'Spacious enough for up to 8 mattress sets (+₱400 per set/night) or 12 sleeping bags',
                'Common toilet and shower',
                'Convenience outlets',
                'Access to Wi-Fi, camp amenities, and activities',
                'Pet-friendly*'
            ]
        },
        'camping-dome': {
            displayPrice: 'Starts at ₱3,000/tent/night',
            features: [
                'Fan-cooled, 25 sqm tent',
                'Spacious enough for up to 8 mattress sets (+₱400 per set/night) or 12 sleeping bags',
                'Common toilet and shower',
                'Convenience outlets',
                'Access to Wi-Fi, camp amenities, and activities',
                'Pet-friendly*'
            ]
        },
        'camping-byot': {
            displayPrice: 'Starts at ₱300/tent or camp space + ₱500/pax overnight fee',
            features: [
                'Spacious area for your own tent or car camp setup',
                'Common toilet and shower',
                'Access to Wi-Fi, camp amenities, and activities'
            ]
        },
        'camping-car': {
            displayPrice: 'Starts at ₱300/tent or camp space + ₱500/pax overnight fee',
            features: [
                'Spacious area for your own tent or car camp setup',
                'Common toilet and shower',
                'Access to Wi-Fi, camp amenities, and activities'
            ]
        }
    };

    function detectStayType(name) {
        const n = name.toLowerCase();
        if (n.includes('standard')) return 'glamping-standard';
        if (n.includes('premium')) return 'glamping-premium';
        if (n.includes('bell')) return 'camping-bell';
        if (n.includes('dome')) return 'camping-dome';
        if (n.includes('byot')) return 'camping-byot';
        if (n.includes('car')) return 'camping-car';
        return 'unknown';
    }

    function getCapacityText(staycation) {
        const capacity = staycation.capacity || 0;
        const adults = staycation.adults || 0;
        const children = staycation.children || 0;
        const mattress = staycation.mattress || 0;
        const lowerName = staycation.name.toLowerCase();
        const mattressText = (lowerName.includes('bell') || lowerName.includes('dome')) ? '' : ` + extra mattress available ${mattress}`;
        const petsText = (lowerName.includes('glamping') || lowerName.includes('bell') || lowerName.includes('dome')) ? ', 2 pets' : '';
        return `Capacity: ${capacity} guests (${adults} adults, ${children} children${mattressText}${petsText})`;
    }

    function getPriceDisplay(staycation) {
        const type = detectStayType(staycation.name || '');
        const config = STAYCATION_CONFIG[type];
        return config ? config.displayPrice : 'Starts at ₱ ' + (staycation.price || 0).toLocaleString() + '/night';
    }

    function getFeaturesList(name) {
        const type = detectStayType(name || '');
        const config = STAYCATION_CONFIG[type];
        if (!config) return `
            <li>• Shared bathroom facilities</li>
            <li>• Double bed</li>
            <li>• Electricity and heating</li>
            <li>• Scenic views</li>
            <li>• Comfortable accommodations</li>
            <li>• 28 m² • 4 single beds</li>
        `;
        return config.features.map(f => `<li>• ${f}</li>`).join('');
    }

    // Generic modal helpers
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalContent = document.getElementById(modalId + '-content');
        if (!modal || !modalContent) return;

        previousFocusElement = document.activeElement;

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);

        setupFocusTrap(modalId);

        setTimeout(() => {
            const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) firstFocusable.focus();
        }, 100);

        activeModal = modalId;
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalContent = document.getElementById(modalId + '-content');
        if (!modal || !modalContent) return;

        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 300);

        if (previousFocusElement && typeof previousFocusElement.focus === 'function') {
            setTimeout(() => previousFocusElement.focus(), 310);
        }

        activeModal = null;
    }

    function openCategoryModal(category, staycations) {
        const modalId = `${category}-modal`;
        const modal = document.getElementById(modalId);
        const modalContent = document.getElementById(`${modalId}-content`);
        const optionsContainer = document.getElementById(`${category}-options`);

        if (!modal || !modalContent || !optionsContainer) return;

        // Store previous focus element
        previousFocusElement = document.activeElement;

        // Clear previous options
        optionsContainer.innerHTML = '';

        // Show all staycations, including already selected ones
        const availableStaycations = staycations;

        // Populate options
        availableStaycations.forEach((staycation, index) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'bg-white rounded-xl shadow-md overflow-hidden transition transform hover:-translate-y-1 hover:shadow-xl h-full flex flex-col border border-gray-100';

            // Determine input label and type based on staycation name
            let inputLabel = 'Number of Guests';
            let inputPlaceholder = '1';
            let isBYOT = staycation.name.toLowerCase().includes('byot');
            let isCarCamping = staycation.name.toLowerCase().includes('car camping');

            let option = '';
            let defaultGuests = 0;
            if (staycation.name.toLowerCase().includes('standard')) {
                option = 'standard';
                defaultGuests = 4;
            } else if (staycation.name.toLowerCase().includes('premium')) {
                option = 'premium';
                defaultGuests = 4;
            } else if (staycation.name.toLowerCase().includes('dome')) {
                option = 'dome';
                defaultGuests = 8;
            } else if (staycation.name.toLowerCase().includes('bell')) {
                option = 'bell';
                defaultGuests = 8;
            } else if (staycation.name.toLowerCase().includes('byot')) {
                option = 'byot';
                defaultGuests = 1;
            } else if (staycation.name.toLowerCase().includes('car')) {
                option = 'car';
                defaultGuests = 1;
            }

            // 🔹 CHECK IF MAY NA-SAVE NANG GUESTS FOR THIS STAYCATION
            const lowerName = staycation.name.toLowerCase();
            const category = staycation.type || (lowerName.includes('standard') || lowerName.includes('premium') ? 'glamping' : 'camping');
            const key = `${category}-${staycation.id}`;
            const savedAcc = window.selectedAccommodations
                ? window.selectedAccommodations[key]
                : null;

            if (savedAcc && typeof savedAcc.guests === 'number') {
                defaultGuests = savedAcc.guests;   // <-- ito na yung 6, 5, etc. na dati mong sinet
            }

            if (isBYOT) {
                inputLabel = 'How many BYOT?';
                inputPlaceholder = '0';
            } else if (isCarCamping) {
                inputLabel = 'Number of Vehicles';
                inputPlaceholder = '0';
            }

            optionDiv.innerHTML = `
                <div class="relative overflow-hidden" style="height: 220px;">
                    <div class="carousel-container flex transition-transform duration-300 ease-in-out" style="width: 300%; height: 100%;">
                        <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-full object-cover flex-shrink-0" style="width: 33.333%;">
                        <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-full object-cover flex-shrink-0" style="width: 33.333%;">
                        <img src="${staycation.image || '/assets/images/default.jpg'}" alt="${staycation.name}" class="w-full object-cover flex-shrink-0" style="width: 33.333%;">
                    </div>
                    <button class="carousel-prev absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75 transition" data-direction="prev">
                        ‹
                    </button>
                    <button class="carousel-next absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75 transition" data-direction="next">
                        ›
                    </button>
                    <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
                        <button class="carousel-indicator w-2 h-2 rounded-full bg-white opacity-50 hover:opacity-100 transition" data-slide="0"></button>
                        <button class="carousel-indicator w-2 h-2 rounded-full bg-white opacity-50 hover:opacity-100 transition" data-slide="1"></button>
                        <button class="carousel-indicator w-2 h-2 rounded-full bg-white opacity-50 hover:opacity-100 transition" data-slide="2"></button>
                    </div>
                </div>

                <div class="flex flex-col p-4 h-full">
                    <div class="flex justify-between items-start mb-0 gap-2">
                        <h5 class="font-bold mb-0" style="line-height: 1.2; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em; font-size: 25px; font-family: 'Gotham Book', sans-serif;">
                            ${staycation.name}
                        </h5>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-base font-semibold ml-2" style="background: linear-gradient(135deg, ${category === 'glamping' ? '#FFD700 0%, #FFA500 100%' : '#32CD32 0%, #228B22 100%'}); color: ${category === 'glamping' ? '#333' : '#fff'};">
                            ${category.charAt(0).toUpperCase() + category.slice(1)}
                        </span>
                    </div>


                    <p class="text-gray-500 mb-0" style="flex-grow: 1; font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                        ${isBYOT || isCarCamping ? 'Area capacity: Suitable for multiple setups and vehicles' : getCapacityText(staycation)}
                    </p>

                    <div class="mb-0">
                        <h6 class="font-semibold text-gray-800 mb-0" style="font-size: 25px; font-family: 'Gotham Book', sans-serif;">Features</h6>
                        <ul class="text-gray-600 space-y-1" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">
                            ${getFeaturesList(staycation.name)}
                        </ul>
                    </div>

                    <div class="mt-auto">
                        <div class="mb-3">
                            <label for="guests-${index}" class="block font-bold text-gray-700 mb-1 mt-2" style="font-size: 13px; font-family: 'Gotham Book', sans-serif;">${inputLabel}</label>
                            <div class="flex items-center gap-2">
                                <button type="button" class="guest-decrement w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors" data-target="guests-${index}">-</button>
                                ${isBYOT || isCarCamping ?
                                    `<input type="number" id="guests-${index}" name="guests" required class="guest-input flex-1 text-center px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent" style="font-size: 13px;" min="1" max="50" placeholder="${inputPlaceholder}" value="${defaultGuests}">` :
                                    `<input type="number" id="guests-${index}" name="guests" required class="guest-input flex-1 text-center px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent" style="font-size: 13px;" min="0" max="${category === 'camping' ? 8 : 6}" value="${defaultGuests}">`
                                }
                                <button type="button" class="guest-increment w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors" data-target="guests-${index}">+</button>
                            </div>
                            <div class="pricing-display mt-2 text-sm font-semibold text-[#8f5d54]" style="font-family: 'Gotham Book', sans-serif;">
                                Total: ₱<span class="total-price-${index}">0</span>
                            </div>
                        </div>
                        ${staycation.name.toLowerCase().includes('glamping') || staycation.name.toLowerCase().includes('bell') || staycation.name.toLowerCase().includes('dome') ? `
                        <div class="pet-section">
                            <div class="pet-initial-question mb-3 pt-3 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-700 mb-2" style="font-family: 'Gotham Book', sans-serif;">Will you be bringing pets?</p>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_pets_${index}" value="no" class="has-pets-radio" checked data-staycation-id="${staycation.id}">
                                        <span class="text-sm text-gray-700" style="font-family: 'Gotham Book', sans-serif;">No pets</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_pets_${index}" value="yes" class="has-pets-radio" data-staycation-id="${staycation.id}">
                                        <span class="text-sm text-gray-700" style="font-family: 'Gotham Book', sans-serif;">Bringing pets (+₱2,000/pet)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="pet-details hidden">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start gap-3 mb-3">
                                        <input type="checkbox" id="pet-agreement-${index}" class="pet-agreement mt-1 w-4 h-4" data-staycation-id="${staycation.id}">
                                        <div class="flex-1">
                                            <label for="pet-agreement-${index}" class="font-medium text-blue-900 cursor-pointer text-sm" style="font-family: 'Gotham Book', sans-serif;">
                                                I agree to the pet policy
                                            </label>
                                            <div class="text-xs text-blue-800 mt-1 space-y-1">
                                                <p>• Pets must be leashed and supervised at all times</p>
                                                <p>• ₱2,000 fee per pet (non-refundable)</p>
                                                <p>• Maximum 2 pets per accommodation</p>
                                            </div>
                                            <button onclick="openPetPolicyModal(); return false;" class="text-xs text-blue-600 hover:text-blue-800 underline mt-1">
                                                View full pet policy
                                            </button>
                                        </div>
                                    </div>

                                    <div class="pet-count-section">
                                        <label class="block text-sm font-medium text-gray-700 mb-2" style="font-family: 'Gotham Book', sans-serif;">Number of pets</label>
                                        <div class="flex items-center gap-2">
                                            <button type="button" class="pet-decrement w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" data-target="pet-count-${index}" disabled>-</button>
                                            <input type="number" id="pet-count-${index}" class="pet-num-input text-center w-16 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-[#8f5d54] focus:border-transparent" style="font-family: 'Gotham Book', sans-serif;" min="0" max="2" value="0" disabled>
                                            <button type="button" class="pet-increment w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" data-target="pet-count-${index}" disabled>+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}

                        <!-- FOOD OPTION -->
                        <div class="food-section">
                            <div class="pt-3 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-700 mb-2" style="font-family: 'Gotham Book', sans-serif;">Will you add breakfast meal?</p>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_breakfast_${index}" value="no" class="has-breakfast-radio" checked data-staycation-id="${staycation.id}">
                                        <span class="text-sm text-gray-700" style="font-family: 'Gotham Book', sans-serif;">Without breakfast</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="has_breakfast_${index}" value="yes" class="has-breakfast-radio" data-staycation-id="${staycation.id}">
                                        <span class="text-sm text-gray-700" style="font-family: 'Gotham Book', sans-serif;">With breakfast (+₱500/person)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <span class="block font-bold text-primary-600 mt-4 mb-2" style="font-size: 20px; font-family: 'Gotham Book', sans-serif;">
                            ${getPriceDisplay(staycation)}
                        </span>

                        <!-- CHECKBOX TO SELECT STAYCATION -->
                        <div class="mb-3">
                            <label for="select-${index}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-[#8f5d54] text-white font-semibold rounded-lg cursor-pointer hover:bg-[#a0675a] transition-colors duration-200 shadow-md hover:shadow-lg" style="font-family: 'Gotham Book', sans-serif;">
                                <input type="checkbox"
                                    id="select-${index}"
                                    class="staycation-checkbox hidden"
                                    data-index="${index}"
                                    data-category="${category}"
                                    data-staycation='${JSON.stringify(staycation).replace(/'/g, "'")}'
                                />
                                <span class="select-text">Select this staycation</span>
                                <i class="fas fa-check ml-2 opacity-0 transition-opacity"></i>
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <button class="flex-1 inline-flex justify-center items-center rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition px-3 py-2"
                                onclick="alert('VR Preview coming soon!')">
                                <i class="fas fa-vr-cardboard mr-2"></i>
                                VR Preview
                            </button>
                        </div>
                        <div class="mt-2">
                            <button class="w-full inline-flex justify-center items-center rounded-lg text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition px-3 py-2"
                                onclick="openGallery('${category}', '${option}')">
                                <i class="bi bi-images mr-2"></i>
                                Photo Gallery
                            </button>
                        </div>
                    </div>
                </div>
            `;

            optionsContainer.appendChild(optionDiv);

            // BYOT / Car Camping: kailangan >0 ang quantity bago puwedeng i-check
            const guestInput = optionDiv.querySelector('.guest-input');
            const selectCheckbox = optionDiv.querySelector('.staycation-checkbox');
            const isByotOrCar = staycation.name.toLowerCase().includes('byot') || staycation.name.toLowerCase().includes('car');

            if (isByotOrCar && guestInput && selectCheckbox) {
                const updateCheckboxState = () => {
                    const val = parseInt(guestInput.value) || 0;
                    const shouldEnable = val > 0;

                    selectCheckbox.disabled = !shouldEnable;

                    // Safety: kung naging 0 tapos naka-check pa, i-uncheck
                    if (!shouldEnable && selectCheckbox.checked) {
                        selectCheckbox.checked = false;
                        selectCheckbox.dispatchEvent(new Event('change'));
                    }
                };

                // initial state based on defaultGuests
                updateCheckboxState();

                // respond sa manual type at sa +/- buttons
                guestInput.addEventListener('input', updateCheckboxState);
                guestInput.addEventListener('change', updateCheckboxState);
            }

            // Add event listeners for pet options
            const petAgreementInput = optionDiv.querySelector('.pet-agreement');
            const numPetsInput = optionDiv.querySelector('.pet-num-input');
            if (petAgreementInput) {
                petAgreementInput.addEventListener('change', updatePetDataForCurrentSelection);
            }
            if (numPetsInput) {
                numPetsInput.addEventListener('change', updatePetDataForCurrentSelection);
                numPetsInput.addEventListener('click', function() {
                    const checkbox = this.closest('.mb-3').querySelector('.pet-agreement');
                    if (!checkbox.checked) {
                        showToast('Please agree to the pet policy before selecting the number of pets.', 'error');
                    }
                });
                numPetsInput.addEventListener('focus', function() {
                    const checkbox = this.closest('.mb-3').querySelector('.pet-agreement');
                    if (!checkbox.checked) {
                        showToast('Please agree to the pet policy before selecting the number of pets.', 'error');
                    }
                });
            }

            // Ensure select button changes color
            const checkbox = optionDiv.querySelector('.staycation-checkbox');
            const label = optionDiv.querySelector('label[for="' + checkbox.id + '"]');
            if (checkbox && label) {
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        label.classList.remove('bg-[#8f5d54]', 'hover:bg-[#a0675a]');
                        label.classList.add('bg-green-600', 'hover:bg-green-700');
                        const selectText = label.querySelector('.select-text');
                        const checkIcon = label.querySelector('.fa-check');
                        if (selectText) selectText.textContent = 'Selected';
                        if (checkIcon) checkIcon.classList.remove('opacity-0');
                    } else {
                        label.classList.remove('bg-green-600', 'hover:bg-green-700');
                        label.classList.add('bg-[#8f5d54]', 'hover:bg-[#a0675a]');
                        const selectText = label.querySelector('.select-text');
                        const checkIcon = label.querySelector('.fa-check');
                        if (selectText) selectText.textContent = 'Select this staycation';
                        if (checkIcon) checkIcon.classList.add('opacity-0');
                    }
                });
            }

            // Carousel functionality
            let currentSlide = 0;
            const carouselContainer = optionDiv.querySelector('.carousel-container');
            const indicators = optionDiv.querySelectorAll('.carousel-indicator');
            const prevBtn = optionDiv.querySelector('.carousel-prev');
            const nextBtn = optionDiv.querySelector('.carousel-next');

            function updateCarousel() {
                if (carouselContainer) {
                    carouselContainer.style.transform = `translateX(-${currentSlide * 33.333}%)`;
                }
                indicators.forEach((ind, index) => {
                    ind.classList.toggle('opacity-100', index === currentSlide);
                    ind.classList.toggle('opacity-50', index !== currentSlide);
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    currentSlide = currentSlide > 0 ? currentSlide - 1 : 2;
                    updateCarousel();
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    currentSlide = currentSlide < 2 ? currentSlide + 1 : 0;
                    updateCarousel();
                });
            }
            indicators.forEach((ind, index) => {
                ind.addEventListener('click', () => {
                    currentSlide = index;
                    updateCarousel();
                });
            });

            // (Optional) extra validation for guest-input enablement can stay if you add a select button later
        });

        // Pre-populate selections if they exist (use unified category-id keys)
        // For each option checkbox, check if there's a saved selection matching this category + staycation id
        (function prepopulateSelections() {
            const checkboxes = optionsContainer.querySelectorAll('.staycation-checkbox');
            checkboxes.forEach(cb => {
                try {
                    const sc = JSON.parse(cb.dataset.staycation || '{}');
                    const key = `${category}-${sc.id}`;
                    const saved = window.selectedAccommodations && window.selectedAccommodations[key];
                    if (saved) {
                        cb.checked = true;
                        cb.dispatchEvent(new Event('change'));

                        const card = cb.closest('.bg-white.rounded-xl');
                        const guestInput = card.querySelector('.guest-input');
                        if (guestInput) guestInput.value = saved.guests || 1;

                        if (saved.pet_agreement) {
                            const petAgreement = card.querySelector('.pet-agreement');
                            const numPetsInput = card.querySelector('.pet-num-input');
                            if (petAgreement) petAgreement.checked = true;
                            if (numPetsInput) numPetsInput.value = saved.num_pets || 0;
                        }

                        if (saved.has_breakfast) {
                            const breakfastRadio = card.querySelector('.has-breakfast-radio[value="yes"]');
                            if (breakfastRadio) breakfastRadio.checked = true;
                        }

                        updateModalPricing(card);
                    }
                } catch (e) {
                    // ignore parse errors
                }
            });
        })();

        showModal(modalId);
    }

    function closeModal(modalId) {
        hideModal(modalId);
    }

    function setupFocusTrap(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Get all focusable elements within the modal
        focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        // Focus trap event listener
        const handleKeyDown = (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    // Shift + Tab
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    // Tab
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            } else if (e.key === 'Escape') {
                // ESC key
                e.preventDefault();
                closeModal(modalId);
            }
        };

        // Add event listener
        modal.addEventListener('keydown', handleKeyDown);

        // Store the listener for cleanup
        modal._focusTrapHandler = handleKeyDown;
    }

    function selectStaycationFromModal(staycation, button) {
        // Close the modal
        closeModal('glamping-modal');
        closeModal('camping-modal');
        // Determine category and use unified key so selections don't overwrite each other
        const card = button.closest('.bg-white.rounded-xl');
        const input = card ? card.querySelector('.guest-input') : null;
        const guests = input ? parseInt(input.value) || 1 : 1;

        const lowerName = (staycation.name || '').toLowerCase();
        const category = staycation.type || (lowerName.includes('standard') || lowerName.includes('premium') ? 'glamping' : 'camping');
        const key = `${category}-${staycation.id}`;

        // Store selection under category-id key
        window.selectedAccommodations[key] = {
            staycation: staycation,
            guests: guests
        };

        // Render selection and update summary
        displaySelectedAccommodation(key, window.selectedAccommodations[key]);
        window.updateBookingSummary();

        showToast(`${staycation.name} selected`, 'success');
    }

    function initializeMultiSelectionModal(category) {
        const modalId = `${category}-modal`;
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const saveBtn = modal.querySelector('.save-multi-selection');
        if (!saveBtn) return;

        saveBtn.addEventListener('click', () => {
            const checkboxes = modal.querySelectorAll('.staycation-checkbox:checked');
            if (checkboxes.length === 0) {
                showToast('Please select at least one option', 'error');
                return;
            }
            // When saving multi-select for this category, remove any previous selections for the same category
            Object.keys(window.selectedAccommodations || {}).forEach(k => {
                if (k.startsWith(`${category}-`)) delete window.selectedAccommodations[k];
            });

            // Add each checked staycation into the global map using category-id keys
            checkboxes.forEach(cb => {
                try {
                    const staycation = JSON.parse(cb.dataset.staycation || '{}');
                    const guests = parseInt(cb.closest('.bg-white.rounded-xl')?.querySelector('.guest-input')?.value, 10) || 1;
                    if (staycation.id) {
                        const key = `${category}-${staycation.id}`;
                        window.selectedAccommodations[key] = {
                            staycation: staycation,
                            guests: guests
                        };
                        displaySelectedAccommodation(key, window.selectedAccommodations[key]);
                    }
                } catch (err) {
                    // ignore parse errors
                }
            });

            // Removal UI initializer removed; no-op.
            window.updateBookingSummary();

            // Close modal
            closeModal(modalId);

            showToast(`${checkboxes.length} option(s) selected`, 'success');
        });
    }

    function openPetPolicyModal() {
        showModal('pet-policy-modal');
    }

    function togglePetSelect(checkbox) {
        const container = checkbox.closest('.mb-3');
        const select = container.querySelector('.pet-num-input');
        if (select) {
            select.disabled = !checkbox.checked;
            if (!checkbox.checked) {
                select.value = '0'; // Reset to 0 when unchecked
            }
        }
    }

    function updatePetDataForCurrentSelection() {
        // Find the card in the currently open modal and map to correct category-id key
        const modal = document.querySelector('#glamping-modal:not(.hidden), #camping-modal:not(.hidden)');
        if (!modal) return;
        const card = modal.querySelector('.bg-white.rounded-xl');
        if (!card) return;

        const checkbox = card.querySelector('.staycation-checkbox');
        if (!checkbox) return;

        let staycation;
        try {
            staycation = JSON.parse(checkbox.dataset.staycation || '{}');
        } catch (e) {
            return;
        }

        const lowerName = (staycation.name || '').toLowerCase();
        const category = checkbox.dataset.category || staycation.type || (lowerName.includes('standard') || lowerName.includes('premium') ? 'glamping' : 'camping');
        const key = `${category}-${staycation.id}`;
        const acc = window.selectedAccommodations[key];
        if (!acc) return;

        const petAgreementInput = card.querySelector('.pet-agreement');
        const numPetsInput = card.querySelector('.pet-num-input');
        if (petAgreementInput && numPetsInput) {
            acc.pet_agreement = petAgreementInput.checked;
            acc.num_pets = parseInt(numPetsInput.value) || 0;
            window.updateBookingSummary();
        }
    }

    // 👉 CLEAN GLOBAL CHANGE HANDLER: checkbox + quantity + pets (multi-select, unlimited)
// Helper: consistent unique key per staycation
function getStaycationKeyFromCheckbox(checkbox) {
    let staycation;
    try {
        staycation = JSON.parse(checkbox.dataset.staycation || '{}');
    } catch (err) {
        console.error('Invalid staycation JSON:', err);
        return null;
    }
    if (!staycation || !staycation.id) return null;

    const lowerName = (staycation.name || '').toLowerCase();
    const typeFromName = (lowerName.includes('standard') || lowerName.includes('premium'))
        ? 'glamping'
        : 'camping';

    const category =
        checkbox.dataset.category ||
        staycation.type ||
        typeFromName; // fallback if walang type

    const key = `${category}-${staycation.id}`;

    return { staycation, key, category };
}

// Helper: extract data from card
function getCardData(card) {
    const guestsInput = card.querySelector('.guest-input');
    const guests = parseInt(guestsInput?.value, 10) || 0;

    const petAgreementInput = card.querySelector('.pet-agreement');
    const numPetsInput = card.querySelector('.pet-num-input');
    const hasPetAgreement = !!(petAgreementInput && petAgreementInput.checked);
    const numPets = hasPetAgreement && numPetsInput ? (parseInt(numPetsInput.value, 10) || 0) : 0;

    const breakfastRadio = card.querySelector('.has-breakfast-radio:checked');
    const hasBreakfast = !!(breakfastRadio && breakfastRadio.value === 'yes');

    return { guests, hasPetAgreement, numPets, hasBreakfast };
}

// Helper: update UI for selection
function updateSelectionUI(card, checkbox, isSelected) {
    const label = card.querySelector(`label[for="${checkbox.id}"]`);
    if (!label) return;

    const selectText = label.querySelector('.select-text');
    const checkIcon = label.querySelector('.fa-check');

    if (isSelected) {
        label.classList.remove('bg-[#8f5d54]', 'hover:bg-[#a0675a]');
        label.classList.add('bg-green-600', 'hover:bg-green-700');
        if (selectText) selectText.textContent = 'Selected';
        if (checkIcon) checkIcon.classList.remove('opacity-0');
    } else {
        label.classList.remove('bg-green-600', 'hover:bg-green-700');
        label.classList.add('bg-[#8f5d54]', 'hover:bg-[#a0675a]');
        if (selectText) selectText.textContent = 'Select this staycation';
        if (checkIcon) checkIcon.classList.add('opacity-0');
    }
}

    document.addEventListener('change', function (e) {
    const target = e.target;

    const isCheckbox     = target.classList.contains('staycation-checkbox');
    const isGuestInput   = target.classList.contains('guest-input');
    const isPetAgreement = target.classList.contains('pet-agreement');
    const isPetCount     = target.classList.contains('pet-num-input');
    const isBreakfast    = target.classList.contains('has-breakfast-radio');

    // Ignore other inputs
    if (!isCheckbox && !isGuestInput && !isPetAgreement && !isPetCount && !isBreakfast) {
        return;
    }

    const card = target.closest('.bg-white.rounded-xl');
    if (!card) return;

    const checkbox = card.querySelector('.staycation-checkbox');
    if (!checkbox) return;

    const info = getStaycationKeyFromCheckbox(checkbox);
    if (!info) return;

    const { staycation, key, category } = info;
    const lowerName = (staycation.name || '').toLowerCase();

    // Get data from card
    const { guests, hasPetAgreement, numPets, hasBreakfast } = getCardData(card);

    // --- Guard: pets count without agreeing to policy ---
    if (isPetCount && !hasPetAgreement) {
        const numPetsInput = card.querySelector('.pet-num-input');
        if (numPetsInput) numPetsInput.value = '0';
        showToast('Please agree to the pet policy before selecting the number of pets.', 'error');
        updateModalPricing(card);
        return;
    }

    // If just changing guests/pets/breakfast while NOT yet selected, only update pricing
    if (!checkbox.checked && !isCheckbox) {
        updateModalPricing(card);
        return;
    }

    // --- Validation when turning checkbox ON ---
    if (isCheckbox && checkbox.checked) {
        // BYOT / Car camping: require quantity > 0
        if ((lowerName.includes('byot') || lowerName.includes('car')) && guests <= 0) {
            showToast('Please enter a number before selecting this option.', 'error');
            checkbox.checked = false;
            updateModalPricing(card);
            return;
        }

        // Bell / Dome: require guests > 0
        if ((lowerName.includes('bell') || lowerName.includes('dome')) && guests <= 0) {
            showToast('Please set number of guests (up to 8) before selecting.', 'error');
            checkbox.checked = false;
            updateModalPricing(card);
            return;
        }

        // Unlimited accommodations allowed
        const selectedCount = Object.keys(window.selectedAccommodations || {}).length;
        const alreadySelected = !!window.selectedAccommodations[key];
    }

    // --- Update global state ---
    if (checkbox.checked) {
        // Add or update selection
        window.selectedAccommodations[key] = {
            staycation,
            guests,
            pet_agreement: hasPetAgreement,
            num_pets: numPets,
            has_breakfast: hasBreakfast,
        };

        displaySelectedAccommodation(key, window.selectedAccommodations[key]);
        updateSelectionUI(card, checkbox, true);

        if (isCheckbox) {
            showToast(`You selected ${staycation.name}`, 'success');
        }
    } else {
        // Remove this specific staycation
        delete window.selectedAccommodations[key];
        displaySelectedAccommodation(key, null);
        updateSelectionUI(card, checkbox, false);

        if (isCheckbox) {
            showToast(`You removed ${staycation.name}`, 'info');
        }
    }

    // If guests/pets/breakfast changed while selected, update data
    if (!isCheckbox && window.selectedAccommodations[key]) {
        window.selectedAccommodations[key] = {
            ...window.selectedAccommodations[key],
            guests,
            pet_agreement: hasPetAgreement,
            num_pets: numPets,
            has_breakfast: hasBreakfast,
        };
    }

    // Recompute pricing & summary
    updateModalPricing(card);
    window.updateBookingSummary();
});

    function proceedToActivities() {
        // Validation: Check if accommodations are selected and have valid guests
        const selectedCount = Object.keys(window.selectedAccommodations).length;
        if (selectedCount === 0) {
            showToast('Please select at least one accommodation before proceeding.', 'error');
            return;
        }

        // Check each selected staycation has valid guests
        for (const acc of Object.values(window.selectedAccommodations)) {
            if (!acc.guests || acc.guests < 1) {
                showToast('Please specify the number of guests for all selected staycations.', 'error');
                return;
            }
        }

        // Confirmation dialog
        const totalPrice = document.getElementById('total-price')?.textContent || '₱ 0';
        const totalGuests = document.getElementById('total-guests')?.textContent || '0';

        if (!confirm(`Are you sure you want to proceed?\n\nTotal Guests: ${totalGuests}\nTotal Price: ${totalPrice}\n\nYou will be taken to select activities next.`)) {
            return;
        }

        // Show loading state
        const proceedBtn = document.getElementById('proceed-btn');
        if (proceedBtn) {
            proceedBtn.disabled = true;
            proceedBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }

        // Send POST request to save accommodations and proceed
        fetch('{{ route("booking.staycation") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                accommodations: window.selectedAccommodations,
                proceed_to_activities: true,
                pet_agreement: document.getElementById('pet-agreement') ? document.getElementById('pet-agreement').checked : false,
                num_pets: document.getElementById('num-pets') ? (parseInt(document.getElementById('num-pets').value) || 0) : 0
            })
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.success) {
                clearSelectionsStorage(); // Clear saved selections
                window.location.href = '{{ route("booking.staycationActivities") }}';
            } else {
                // Reset button
                if (proceedBtn) {
                    proceedBtn.disabled = false;
                    proceedBtn.innerHTML = 'Proceed to Activities';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Reset button
            if (proceedBtn) {
                proceedBtn.disabled = false;
                proceedBtn.innerHTML = 'Proceed to Activities';
            }
            showToast('Network error. Please check your connection and try again.', 'error');
        });
    }

    function skipToPayment() {
        // Validation: Check if accommodations are selected and have valid guests
        const selectedCount = Object.keys(window.selectedAccommodations).length;
        if (selectedCount === 0) {
            showToast('Please select at least one accommodation before proceeding.', 'error');
            return;
        }

        // Check each selected staycation has valid guests
        for (const acc of Object.values(window.selectedAccommodations)) {
            if (!acc.guests || acc.guests < 1) {
                showToast('Please specify the number of guests for all selected staycations.', 'error');
                return;
            }
        }

        // Confirmation dialog
        const totalPrice = document.getElementById('total-price')?.textContent || '₱ 0';
        const totalGuests = document.getElementById('total-guests')?.textContent || '0';

        if (!confirm(`Are you sure you want to skip activities and proceed to payment?\n\nTotal Guests: ${totalGuests}\nTotal Price: ${totalPrice}\n\nYou can always add activities later if needed.`)) {
            return;
        }

        // Show loading state
        const skipBtn = document.getElementById('skip-btn');
        if (skipBtn) {
            skipBtn.disabled = true;
            skipBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }

        // Send POST request to save accommodations and skip to payment
        fetch('{{ route("booking.staycation") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                accommodations: window.selectedAccommodations,
                skip_to_payment: true,
                pet_agreement: document.getElementById('pet-agreement') ? document.getElementById('pet-agreement').checked : false,
                num_pets: document.getElementById('num-pets') ? (parseInt(document.getElementById('num-pets').value) || 0) : 0
            })
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.success) {
                clearSelectionsStorage(); // Clear saved selections
                window.location.href = '{{ route("booking.staycationPayment") }}';
            } else {
                // Reset button
                if (skipBtn) {
                    skipBtn.disabled = false;
                    skipBtn.innerHTML = 'Skip Activities & Go to Payment';
                }
                showToast('Error saving accommodations. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Reset button
            if (skipBtn) {
                skipBtn.disabled = false;
                skipBtn.innerHTML = 'Skip Activities & Go to Payment';
            }
            showToast('Network error. Please check your connection and try again.', 'error');
        });
    }

    // Gallery functions
    function openGallery(type, option) {
        const key = type + '-' + option;
        const data = window.galleryData[key];
        if (data) {
            window.galleryImages = data.images;
            window.galleryCurrentIndex = 0;
            showGalleryModal();
        }
    }

    function closeGallery() {
        hideGalleryModal();
    }

    function galleryPrev() {
        if (window.galleryCurrentIndex > 0) {
            window.galleryCurrentIndex--;
            updateGalleryImage();
        }
    }

    function galleryNext() {
        if (window.galleryCurrentIndex < window.galleryImages.length - 1) {
            window.galleryCurrentIndex++;
            updateGalleryImage();
        }
    }

    function showGalleryModal() {
        const modal = document.getElementById('gallery-modal');
        const modalContent = document.getElementById('gallery-modal-content');
        if (modal && modalContent) {
            updateGalleryImage();
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    function hideGalleryModal() {
        const modal = document.getElementById('gallery-modal');
        const modalContent = document.getElementById('gallery-modal-content');
        if (modal && modalContent) {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }

    function updateGalleryImage() {
        const img = document.getElementById('gallery-image');
        const counter = document.getElementById('gallery-counter');
        if (img && window.galleryImages.length > 0) {
            img.src = window.galleryImages[window.galleryCurrentIndex];
            counter.textContent = (window.galleryCurrentIndex + 1) + ' / ' + window.galleryImages.length;
        }
    }
</script>
</html>



