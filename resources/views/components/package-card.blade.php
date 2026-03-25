@php
  use App\Helpers\CloudinaryHelper;
  use App\Helpers\TimeHelper;
@endphp

@props([
    'package',
    'showEditDelete' => false,
    'editRoute' => null,
    'deleteRoute' => null,
    'variant' => 'frontend', // 'frontend' or 'admin'
])

@php
  $image = $package->image;
  $isCloudinary = is_string($image) && str_contains($image, 'res.cloudinary.com');
  $isValidUrl = is_string($image) && (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/'));
  
  if (!$isValidUrl) {
    $imgSrc = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="600" height="400"%3E%3Crect fill="%23e5e7eb" width="600" height="400"/%3E%3Ctext x="50%25" y="50%25" font-family="sans-serif" font-size="24" fill="%239ca3af" text-anchor="middle" dy=".3em"%3ENo Image Available%3C/text%3E%3C/svg%3E';
    $imgSrcset = null;
  } elseif ($isCloudinary) {
    $imgSrc = CloudinaryHelper::optimize($image, 600, false, false, 'good');
    $imgSrcset = CloudinaryHelper::srcset($image, [400, 600, 900], false, false, 'good');
  } else {
    $imgSrc = $image;
    $imgSrcset = null;
  }
  
  $cardClass = $variant === 'admin' 
    ? 'tilt-card bg-white/70 backdrop-blur-lg border border-gray-200 shadow-md hover:shadow-xl'
    : 'bg-white shadow-lg hover:shadow-2xl';
@endphp

<div class="package-card {{ $cardClass }} rounded-2xl overflow-hidden transition flex flex-col h-full" x-data="packageCardData({{ json_encode($package) }})">
  <!-- Package Image -->
  <div class="relative h-40 sm:h-48 md:h-56 overflow-hidden bg-gray-200 shrink-0">
    <img 
      src="{{ $imgSrc }}"
      @if($imgSrcset) srcset="{{ $imgSrcset }}" @endif
      alt="{{ $package->name }}"
      crossorigin="anonymous"
      class="w-full h-full object-cover photo-clarity"
    >
    
    {{-- Status Badge for Admin --}}
    @if($variant === 'admin' && isset($package->is_active))
      <div class="absolute top-2 right-2">
        <span class="{{ $package->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} text-xs font-semibold px-3 py-1 rounded-full">
          {{ $package->is_active ? '✓ Active' : 'Draft' }}
        </span>
      </div>
    @endif
    
    {{-- Admin Actions Overlay --}}
    @if($showEditDelete)
      <div class="absolute top-0 left-0 right-0 bottom-0 bg-black/0 hover:bg-black/20 transition flex items-center justify-center gap-2 opacity-0 hover:opacity-100">
        @if($editRoute)
          <a href="{{ $editRoute }}" class="bg-brand hover:bg-[#a0675a] text-white p-3 rounded-full transition shadow-lg" title="Edit">
            <i class="bi bi-pencil-square text-lg"></i>
          </a>
        @endif
        @if($deleteRoute)
          <form action="{{ $deleteRoute }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this package?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white p-3 rounded-full transition shadow-lg" title="Delete">
              <i class="bi bi-trash text-lg"></i>
            </button>
          </form>
        @endif
      </div>
    @endif
  </div>

  <!-- Package Info -->
  <div class="p-4 sm:p-5 flex flex-col grow min-h-0">
    <!-- Title -->
    <div class="mb-1 min-h-6">
      <h3 class="text-lg sm:text-xl font-bold text-gray-900">{{ $package->name }}</h3>
    </div>
    
    <!-- Description -->
    <p class="text-gray-600 text-xs sm:text-sm mb-2 line-clamp-2 sm:line-clamp-3 grow min-h-6">
      {{ $package->description ?? 'Experience an unforgettable adventure with this exclusive package.' }}
    </p>

    <!-- Quick Info Grid -->
    <div class="grid grid-cols-2 gap-2 mb-1.5 pb-1.5 border-b border-gray-200">
      @if($package->duration_days)
        <div class="text-center p-1.5 bg-gray-50 rounded-lg">
          <i class="bi bi-calendar-event text-brand text-lg mb-0.5 block"></i>
          <span class="text-xs font-semibold text-gray-700">{{ $package->duration_days }} Day{{ $package->duration_days > 1 ? 's' : '' }}</span>
        </div>
      @endif
      
      @if($package->max_guests)
        <div class="text-center p-1.5 bg-gray-50 rounded-lg">
          <i class="bi bi-people text-brand text-lg mb-0.5 block"></i>
          <span class="text-xs font-semibold text-gray-700">{{ $package->max_guests }} Guest{{ $package->max_guests > 1 ? 's' : '' }}</span>
        </div>
      @endif
    </div>

    <!-- Inclusions Badge -->
    @if($package->inclusions && is_array($package->inclusions) && count($package->inclusions) > 0)
      <div class="mb-1.5 min-h-5">
        <div class="flex flex-wrap gap-1">
          @if($variant === 'frontend')
            {{-- Show ALL inclusions on frontend --}}
            @foreach($package->inclusions as $inclusion)
              <span class="inline-block bg-brand/10 text-brand text-xs px-2.5 py-1 rounded-full font-medium">
                {{ $inclusion }}
              </span>
            @endforeach
          @else
            {{-- Show first 2 + more on admin --}}
            @foreach(array_slice($package->inclusions, 0, 2) as $inclusion)
              <span class="inline-block bg-brand/10 text-brand text-xs px-2.5 py-1 rounded-full font-medium">
                {{ $inclusion }}
              </span>
            @endforeach
            @if(count($package->inclusions) > 2)
              <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2.5 py-1 rounded-full font-medium">
                +{{ count($package->inclusions) - 2 }} more
              </span>
            @endif
          @endif
        </div>
      </div>
    @endif

    <!-- Available Schedule -->
    @if($variant === 'frontend')
      @php
        $sessions = $package->sessions && is_array($package->sessions) ? $package->sessions : [];
      @endphp
      @if(count($sessions) > 0)
        <div class="mb-2.5 p-2 bg-blue-50 rounded-lg border border-blue-200 min-h-12">
          <h4 class="text-xs font-semibold text-blue-900 mb-1 flex items-center gap-1">
            <i class="bi bi-clock-history"></i> Available Sessions
          </h4>
          <div class="space-y-0.5 text-xs text-blue-800">
            @foreach($sessions as $session)
              <div class="flex justify-between">
                <span class="font-medium">{{ $session['name'] ?? $session->name }}:</span>
                <span>{{ TimeHelper::formatSessionTime($session['start'] ?? $session->start, $session['end'] ?? $session->end) }}</span>
              </div>
            @endforeach
          </div>
        </div>
      @endif
    @endif

    <!-- Price and Button -->
    <div class="mt-auto space-y-1.5">
      @if($package->price)
        <div class="p-2.5 bg-linear-to-br from-brand/10 to-brand/5 rounded-xl border border-brand/20">
          <div class="text-center">
            @if($package->pricing_type === 'per_pax')
              <span class="text-xs font-semibold text-gray-600">
                Total for {{ $package->max_guests ?? 1 }} 
                {{ ($package->max_guests ?? 1) == 1 ? 'guest' : 'guests' }}
              </span>
              <div class="text-xl sm:text-2xl font-bold text-brand">
                ₱{{ number_format($package->price, 0) }}
              </div>
            @else
              <span class="text-xs font-semibold text-gray-600">Per Group</span>
              <div class="text-2xl sm:text-3xl font-bold text-brand">
                ₱{{ number_format($package->price, 0) }}
              </div>
              @if($package->max_guests)
                <span class="text-xs text-gray-500 block pt-1 border-t border-brand/20 mt-1">
                  Covers up to {{ $package->max_guests }} 
                  {{ $package->max_guests == 1 ? 'person' : 'people' }}
                </span>
              @endif
            @endif
          </div>
        </div>
      @endif

      <!-- Action Button -->
      <button 
        @click="showBookingModal = true"
        class="w-full bg-brand hover:bg-[#a0675a] text-white font-semibold py-2 sm:py-2.5 px-4 rounded-xl transition duration-300 ease-out transform hover:scale-105 flex items-center justify-center gap-2 shadow-lg hover:shadow-2xl">
        <i class="bi bi-calendar-check text-lg"></i>
        <span class="text-sm font-bold">Book Now</span>
      </button>

      <!-- Booking Modal (only on frontend) -->
      @if($variant === 'frontend')
        <x-package-booking-modal />
      @endif
    </div>
  </div>
</div>

<script>
function packageCardData(pkg) {
    const package_ = pkg || {};
    return {
        package: { max_guests: package_.max_guests || 10, ...package_ },
        showBookingModal: false,
        isLoading: false,
        errors: [],
        form: {
            date: '',
            totalGuests: 1,
            session: '',
            firstName: '',
            lastName: '',
            phone: '',
            email: '',
            specialRequests: ''
        },

        get totalPrice() {
            if (this.package.pricing_type === 'per_pax') {
                return this.package.price * this.form.totalGuests;
            }
            return this.package.price;
        },

        formatPrice(price) {
            return parseInt(price).toLocaleString();
        },

        getMinDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        },

        resetForm() {
            this.form = {
                date: '',
                totalGuests: 1,
                session: '',
                firstName: '',
                lastName: '',
                phone: '',
                email: '',
                specialRequests: ''
            };
            this.errors = [];
        },

        async processBooking() {
            this.isLoading = true;
            this.errors = [];

            // Validation
            if (!this.form.date) this.errors.push('Please select a date');
            if (!this.form.totalGuests) this.errors.push('Please enter number of guests');
            if (this.form.totalGuests > this.package.max_guests) this.errors.push('Number of guests exceeds maximum');
            if (!this.form.firstName) this.errors.push('First name is required');
            if (!this.form.lastName) this.errors.push('Last name is required');
            if (!this.form.phone) this.errors.push('Phone number is required');
            if (!this.form.email) this.errors.push('Email is required');

            if (this.errors.length > 0) {
                this.isLoading = false;
                console.warn('Booking validation errors:', this.errors);
                return;
            }

            try {
                // Create booking in database
                const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                const bookingData = {
                    package_id: this.package.id,
                    booking_date: this.form.date,
                    total_guests: this.form.totalGuests,
                    session: this.form.session,
                    first_name: this.form.firstName,
                    last_name: this.form.lastName,
                    phone: this.form.phone,
                    email: this.form.email,
                    special_requests: this.form.specialRequests,
                    total_amount: this.totalPrice
                };

                console.log('📝 Creating booking with data:', bookingData);

                const response = await fetch('/api/package-bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(bookingData)
                });

                const result = await response.json();
                console.log('✅ Booking response status:', response.status);
                console.log('📦 Booking response:', result);

                if (response.ok && result.success) {
                    console.log('✨ Booking created successfully! ID:', result.booking_id);
                    // Initialize PayMongo checkout directly
                    await this.initializePayMongo(result.booking_id, bookingData);
                } else {
                    this.errors.push(result.message || 'Failed to create booking');
                    console.error('❌ Booking creation failed:', result);
                }
            } catch (err) {
                this.errors.push('An error occurred. Please try again.');
                console.error('❌ Booking submission error:', err);
            } finally {
                this.isLoading = false;
            }
        },

        async initializePayMongo(bookingId, bookingData) {
            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                
                const paymentPayload = {
                    booking_id: parseInt(bookingId),
                    amount: parseFloat(bookingData.total_amount),
                    email: bookingData.email,
                    name: bookingData.first_name + ' ' + bookingData.last_name,
                    package_name: this.package.name
                };
                
                console.log('🎯 Initializing PayMongo for booking:', bookingId);
                console.log('📦 Payment payload:', paymentPayload);
                console.log('✅ All data types correct:',
                    'booking_id=' + typeof paymentPayload.booking_id,
                    'amount=' + typeof paymentPayload.amount,
                    'email=' + typeof paymentPayload.email,
                    'name=' + typeof paymentPayload.name,
                    'package_name=' + typeof paymentPayload.package_name
                );

                // Create PayMongo checkout session
                const response = await fetch('/api/payment/create-checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(paymentPayload)
                });

                const result = await response.json();
                console.log('🔄 PayMongo response status:', response.status);
                console.log('📨 PayMongo response data:', result);

                if (response.ok && result.checkout_url) {
                    console.log('✨ Payment initialized successfully, redirecting to:', result.checkout_url);
                    // Redirect to PayMongo checkout
                    window.location.href = result.checkout_url;
                } else {
                    // Show all error information
                    if (result.errors) {
                        console.error('❌ Validation errors:', result.errors);
                        Object.keys(result.errors).forEach(field => {
                            result.errors[field].forEach(msg => {
                                this.errors.push(`${field}: ${msg}`);
                            });
                        });
                    }
                    
                    if (result.received_data) {
                        console.error('📥 Server received:', result.received_data);
                    }
                    
                    this.errors.push(result.message || 'Failed to initialize payment');
                    console.error('❌ PayMongo initialization failed:', result);
                }
            } catch (err) {
                this.errors.push('Payment initialization failed. Please try again.');
                console.error('❌ Payment initialization error:', err);
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>
