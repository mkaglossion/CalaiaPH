<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Meals or Activities - {{ $booking->transaction_id }}</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="min-h-screen bg-[#f5efe7]">
    <x-header active="profile" />

    <main class="py-10 min-h-screen">
        <div class="container mx-auto px-4 max-w-5xl">
            <a href="{{ route('profile.bookings.show', $booking) }}" class="inline-flex items-center gap-2 mb-6 text-sm font-semibold" style="color:#00848d;text-decoration:none;">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Back to {{ $booking->transaction_id }}
            </a>

            <div class="rounded-2xl bg-white p-6 md:p-8 shadow-sm" style="border:1px solid #cccbbd;">
                <div class="mb-7 pb-6" style="border-bottom:1px solid #cccbbd;">
                    <p class="text-sm uppercase tracking-wide" style="color:#7a7d52;">Add-ons for {{ $booking->transaction_id }}</p>
                    <h1 class="text-3xl font-bold mt-1" style="color:#4d3327;">Add Meals or Activities</h1>
                    <p class="mt-2" style="color:#7a7d52;">This will create a separate transaction and payment. Your original booking total remains &#8369;{{ number_format((float) $booking->total_amount, 2) }}.</p>
                </div>

                @if(session('error'))
                    <div class="rounded-xl p-4 mb-6" style="background-color:rgba(185,28,28,.08);border:1px solid #b91c1c;color:#7f1d1d;">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="rounded-xl p-4 mb-6" style="background-color:rgba(185,28,28,.08);border:1px solid #b91c1c;color:#7f1d1d;">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.bookings.add-ons.store', $booking) }}" id="addOnForm">
                    @csrf

                    <section>
                        <h2 class="text-xl font-bold mb-4" style="color:#4d3327;">Meals</h2>
                        @if($foods->isEmpty())
                            <p class="text-sm" style="color:#7a7d52;">No meals are currently available.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($foods as $food)
                                    @php
                                        $foodImage = $food->image ?: $food->swiperImages->first()?->image_path;
                                    @endphp
                                    <label class="rounded-xl p-3 flex items-center gap-3" style="border:1px solid #cccbbd;">
                                        <img src="{{ \App\Helpers\ImageHelper::src($foodImage) }}"
                                             alt="{{ $food->name }}"
                                             class="w-16 h-16 rounded-lg object-cover flex-shrink-0"
                                             loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                        <span class="flex-1 min-w-0">
                                            <strong class="block" style="color:#4d3327;">{{ $food->name }}</strong>
                                            <span class="text-sm" style="color:#98655b;">&#8369;{{ number_format((float) $food->price, 2) }}</span>
                                            @if($food->is_inventory_managed)
                                                <small class="block mt-1" style="color:#7a7d52;">{{ $food->stock_quantity }} available</small>
                                            @endif
                                        </span>
                                        <input type="number" name="foods[{{ $food->id }}]" value="{{ old('foods.' . $food->id, 0) }}" min="0" max="{{ $food->is_inventory_managed ? min(99, $food->stock_quantity) : 99 }}" class="w-20 rounded-lg px-3 py-2 text-center" style="border:1px solid #cccbbd;" aria-label="Quantity for {{ $food->name }}">
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="mt-8 pt-8" style="border-top:1px solid #cccbbd;">
                        <h2 class="text-xl font-bold mb-4" style="color:#4d3327;">Activities</h2>
                        @if($activities->isEmpty())
                            <p class="text-sm" style="color:#7a7d52;">No activities are currently available.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($activities as $activity)
                                    @php
                                        $activityImage = $activity->image ?: $activity->swiperImages->first()?->image_path;
                                    @endphp
                                    <label class="rounded-xl p-3 flex items-center gap-3" style="border:1px solid #cccbbd;">
                                        <img src="{{ \App\Helpers\ImageHelper::src($activityImage) }}"
                                             alt="{{ $activity->name }}"
                                             class="w-16 h-16 rounded-lg object-cover flex-shrink-0"
                                             loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ \App\Helpers\ImageHelper::placeholder() }}';">
                                        <span class="flex-1 min-w-0">
                                            <strong class="block" style="color:#4d3327;">{{ $activity->name }}</strong>
                                            <span class="text-sm" style="color:#98655b;">&#8369;{{ number_format((float) $activity->price, 2) }}</span>
                                            @if($activity->duration)<small class="block mt-1" style="color:#7a7d52;">{{ $activity->duration }}</small>@endif
                                        </span>
                                        <input type="number" name="activities[{{ $activity->id }}]" value="{{ old('activities.' . $activity->id, 0) }}" min="0" max="99" class="w-20 rounded-lg px-3 py-2 text-center" style="border:1px solid #cccbbd;" aria-label="Quantity for {{ $activity->name }}">
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <div class="mt-8 pt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4" style="border-top:1px solid #cccbbd;">
                        <p class="text-sm" style="color:#7a7d52;">The final amount is calculated using the current system prices.</p>
                        <button type="submit" id="addOnSubmit" class="inline-flex justify-center items-center gap-2 px-6 py-3 rounded-lg font-semibold" style="background-color:#00848d;color:#fff;">
                            <i class="bi bi-credit-card" aria-hidden="true"></i>
                            Continue to Separate Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <x-footer />
    <script>
        document.getElementById('addOnForm')?.addEventListener('submit', function () {
            const button = document.getElementById('addOnSubmit');
            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-not-allowed');
            button.innerHTML = '<span class="inline-block h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span> Processing...';
        });
    </script>
</body>
</html>
