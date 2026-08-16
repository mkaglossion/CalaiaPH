<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calaia Farm - User Profile</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-screen bg-[#f5efe7]">
    <x-header active="profile" />

    <main id="main-content" class="py-8" style="min-height:100vh;">
        <div class="container mx-auto px-4 max-w-7xl">

            {{-- PROFILE HEADER --}}
            <section
                aria-labelledby="profile-title"
                class="rounded-xl p-8 mb-8 text-white relative overflow-hidden shadow-lg"
                style="background:linear-gradient(135deg,#98655b 0%,#843c23 25%,#4d3327 50%,#222c1e 100%);"
            >
                <div
                    class="absolute inset-0 opacity-20"
                    style="background-image:url('data:image/svg+xml,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3e%3ccircle cx=%2250%22 cy=%2250%22 r=%222%22 fill=%22rgba(246,243,236,0.45)%22/%3e%3c/svg%3e');background-size:50px 50px;">
                </div>

                <div class="flex flex-col md:flex-row items-center gap-8 relative z-10">
                    {{-- Avatar --}}
                    <div
                        class="relative w-32 h-32 rounded-full flex items-center justify-center transition-all duration-300 hover:scale-105"
                        style="background-color:rgba(246,243,236,0.15);border:4px solid rgba(246,243,236,0.5);"
                    >
                        @if(!empty(auth()->user()->picture))
                            <img
                                src="{{ asset(auth()->user()->picture) }}?t={{ time() }}"
                                alt="Profile picture"
                                class="w-full h-full rounded-full object-cover"
                            >
                        @else
                            <div class="text-5xl" style="color:#f6f3ec;">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                            </div>
                        @endif
                    </div>

                    {{-- User info --}}
                    <div class="flex-1 text-center md:text-left">
                        <h1 id="profile-title"
                            class="text-3xl md:text-4xl font-bold mb-2 drop-shadow-md"
                            style="color:#f6f3ec;">
                            {{ auth()->user()->name ?? 'User' }}
                        </h1>

                        <p class="text-lg mb-1 flex items-center justify-center md:justify-start gap-2 drop-shadow-sm"
                           style="color:rgba(246,243,236,0.9);">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            {{ auth()->user()->email ?? 'user@example.com' }}
                        </p>

                        @if(!empty(auth()->user()->phone))
                            <p class="text-lg mb-3 flex items-center justify-center md:justify-start gap-2 drop-shadow-sm"
                               style="color:rgba(246,243,236,0.9);">
                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                {{ auth()->user()->phone }}
                            </p>
                        @endif

                        <div class="flex flex-wrap gap-2 mt-4 justify-center md:justify-start">
                            <span
                                class="px-3 py-1 rounded-full text-sm flex items-center gap-1"
                                style="background-color:rgba(246,243,236,0.12);border:1px solid rgba(246,243,236,0.5);color:#f6f3ec;"
                            >
                                <i class="bi bi-star" aria-hidden="true"></i>
                                Valued Member
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ALERTS --}}
            @if(session('success'))
                <div
                    role="alert"
                    class="border rounded-lg p-4 mb-6 flex items-start gap-3"
                    style="background-color:rgba(122,125,82,0.08);border-color:#7a7d52;color:#222c1e;"
                >
                    <i class="bi bi-check-circle mt-0.5" aria-hidden="true" style="color:#7a7d52;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div
                    role="alert"
                    class="border rounded-lg p-4 mb-6 flex items-start gap-3"
                    style="background-color:rgba(132,60,35,0.08);border-color:#843c23;color:#4d3327;"
                >
                    <i class="bi bi-exclamation-triangle mt-0.5" aria-hidden="true" style="color:#843c23;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- MAIN COLUMN --}}
                <div class="lg:col-span-2">
                    {{-- TABS --}}
                    <nav
                        role="tablist"
                        aria-label="Profile sections"
                        aria-orientation="horizontal"
                        class="rounded-xl p-2 mb-8 shadow-sm sticky top-4 z-10 backdrop-blur"
                        style="background-color:#ffffff;border:1px solid rgba(77,51,39,0.12);"
                    >
                        <div class="flex flex-col sm:flex-row gap-2">
                            {{-- Overview tab --}}
                            <button
                                class="profile-tab flex-1 px-4 py-3 rounded-lg cursor-pointer transition-all duration-300 flex items-center justify-center gap-2 font-medium"
                                role="tab"
                                aria-selected="true"
                                aria-controls="overview-panel"
                                id="overview-tab"
                                data-tab="overview"
                                data-active="true"
                                style="border:none;background-color:#98655b;color:#f6f3ec;"
                            >
                                <i class="bi bi-person" aria-hidden="true"></i>
                                <span>Overview</span>
                            </button>

                            {{-- Bookings tab --}}
                            <button
                                class="profile-tab flex-1 px-4 py-3 rounded-lg cursor-pointer transition-all duration-300 flex items-center justify-center gap-2 font-medium"
                                role="tab"
                                aria-selected="false"
                                aria-controls="bookings-panel"
                                id="bookings-tab"
                                data-tab="bookings"
                                style="border:none;background-color:transparent;color:#4d3327;"
                            >
                                <i class="bi bi-calendar-check" aria-hidden="true"></i>
                                <span>My Bookings</span>
                            </button>

                            {{-- Edit tab --}}
                            <button
                                class="profile-tab flex-1 px-4 py-3 rounded-lg cursor-pointer transition-all duration-300 flex items-center justify-center gap-2 font-medium"
                                role="tab"
                                aria-selected="false"
                                aria-controls="edit-panel"
                                id="edit-tab"
                                data-tab="edit"
                                style="border:none;background-color:transparent;color:#4d3327;"
                            >
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                <span>Edit Profile</span>
                            </button>

                            {{-- Security tab --}}
                            <button
                                class="profile-tab flex-1 px-4 py-3 rounded-lg cursor-pointer transition-all duration-300 flex items-center justify-center gap-2 font-medium"
                                role="tab"
                                aria-selected="false"
                                aria-controls="password-panel"
                                id="password-tab"
                                data-tab="password"
                                style="border:none;background-color:transparent;color:#4d3327;"
                            >
                                <i class="bi bi-key" aria-hidden="true"></i>
                                <span>Security</span>
                            </button>
                        </div>
                    </nav>

                    {{-- PANELS WRAPPER --}}
                    <div
                        class="rounded-xl shadow-sm min-h-96"
                        style="background-color:#ffffff;border:1px solid rgba(77,51,39,0.15);"
                    >
                        <!-- Overview Panel -->
                        <section class="tab-panel p-6 md:p-8 opacity-100 transform scale-100 transition-all duration-300"
                                 role="tabpanel"
                                 aria-labelledby="overview-tab"
                                 aria-describedby="overview-description"
                                 id="overview-panel"
                                 data-active="true">
                            <div id="overview-description" class="sr-only">
                                View your personal information and account statistics
                            </div>

                            <div class="space-y-6">
                                {{-- PERSONAL INFORMATION --}}
                                <div class="rounded-xl p-6"
                                     style="background-color:#ffffff;border:1px solid rgba(204,203,189,0.9);">
                                    <div class="flex items-center gap-3 mb-4 pb-3"
                                         style="border-bottom:1px solid rgba(204,203,189,0.9);">
                                        <i class="bi bi-person-lines-fill text-xl" aria-hidden="true" style="color:#98655b;"></i>
                                        <h3 class="text-xl font-semibold" style="color:#4d3327;">Personal Information</h3>
                                    </div>

                                    {{-- rows --}}
                                    <div class="space-y-1">
                                        {{-- Full Name --}}
                                        <div class="flex flex-col gap-2 py-4"
                                             style="border-bottom:1px solid rgba(204,203,189,0.8);">
                                            <div class="text-sm font-medium uppercase tracking-wide"
                                                 style="color:#7a7d52;">
                                                Full Name
                                            </div>
                                            <div class="flex items-center gap-2 text-base"
                                                 style="color:#4d3327;">
                                                <i class="bi bi-person" aria-hidden="true"></i>
                                                <span>{{ auth()->user()->name ?? 'User' }}</span>
                                            </div>
                                        </div>

                                        {{-- Email --}}
                                        <div class="flex flex-col gap-2 py-4"
                                             style="border-bottom:1px solid rgba(204,203,189,0.8);">
                                            <div class="text-sm font-medium uppercase tracking-wide"
                                                 style="color:#7a7d52;">
                                                Email Address
                                            </div>
                                            <div class="flex items-center gap-2 text-base"
                                                 style="color:#4d3327;">
                                                <i class="bi bi-envelope" aria-hidden="true"></i>
                                                <span>{{ auth()->user()->email ?? 'user@example.com' }}</span>
                                            </div>
                                        </div>

                                        {{-- Phone --}}
                                        <div class="flex flex-col gap-2 py-4"
                                             style="border-bottom:1px solid rgba(204,203,189,0.8);">
                                            <div class="text-sm font-medium uppercase tracking-wide"
                                                 style="color:#7a7d52;">
                                                Phone Number
                                            </div>
                                            <div class="flex items-center gap-2 text-base"
                                                 style="color:#4d3327;">
                                                <i class="bi bi-telephone" aria-hidden="true"></i>
                                                <span>{{ auth()->user()->phone ?? 'Not provided' }}</span>
                                            </div>
                                        </div>

                                        {{-- Address --}}
                                        <div class="flex flex-col gap-2 py-4"
                                             style="border-bottom:1px solid rgba(204,203,189,0.8);">
                                            <div class="text-sm font-medium uppercase tracking-wide"
                                                 style="color:#7a7d52;">
                                                Address
                                            </div>
                                            <div class="flex items-center gap-2 text-base"
                                                 style="color:#4d3327;">
                                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                                <span>{{ auth()->user()->address ?? 'Not provided' }}</span>
                                            </div>
                                        </div>

                                        {{-- Date of Birth (last row, no bottom border) --}}
                                        <div class="flex flex-col gap-2 py-4">
                                            <div class="text-sm font-medium uppercase tracking-wide"
                                                 style="color:#7a7d52;">
                                                Date of Birth
                                            </div>
                                            <div class="flex items-center gap-2 text-base"
                                                 style="color:#4d3327;">
                                                <i class="bi bi-calendar" aria-hidden="true"></i>
                                                <span>
                                                    {{ auth()->user()->date_of_birth ? \Carbon\Carbon::parse(auth()->user()->date_of_birth)->format('F d, Y') : 'Not provided' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ACCOUNT STATISTICS --}}
                                <div class="rounded-xl p-6"
                                     style="background-color:#ffffff;border:1px solid rgba(204,203,189,0.9);">
                                    <div class="flex items-center gap-3 mb-4 pb-3"
                                         style="border-bottom:1px solid rgba(204,203,189,0.9);">
                                        <i class="bi bi-graph-up text-xl" aria-hidden="true" style="color:#00848d;"></i>
                                        <h3 class="text-xl font-semibold" style="color:#4d3327;">Account Statistics</h3>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-4">
                                        <div class="text-center p-4 rounded-lg"
                                             style="background-color:#ffffff;border:1px solid rgba(204,203,189,0.9);">
                                            <div class="text-3xl font-bold mb-1" style="color:#98655b;">
                                                {{ $totalBookings ?? 0 }}
                                            </div>
                                            <div class="text-sm font-medium" style="color:#4d3327;">Total Bookings</div>
                                        </div>

                                        <div class="text-center p-4 rounded-lg"
                                             style="background-color:#ffffff;border:1px solid rgba(204,203,189,0.9);">
                                            <div class="text-3xl font-bold mb-1" style="color:#98655b;">
                                                ₱{{ number_format($totalSpent ?? 0, 2) }}
                                            </div>
                                            <div class="text-sm font-medium" style="color:#4d3327;">Total Spent</div>
                                        </div>

                                        <div class="text-center p-4 rounded-lg"
                                             style="background-color:#ffffff;border:1px solid rgba(204,203,189,0.9);">
                                            <div class="text-3xl font-bold mb-1" style="color:#98655b;">
                                                {{ $confirmedBookings ?? 0 }}
                                            </div>
                                            <div class="text-sm font-medium" style="color:#4d3327;">Confirmed</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- EDIT PROFILE --}}
                        <section
                            id="edit-panel"
                            class="tab-panel p-6 md:p-8 hidden opacity-0 transform scale-95 transition-all duration-300"
                            role="tabpanel"
                            aria-labelledby="edit-tab"
                        >
                            <div id="edit-description" class="sr-only">
                                Edit your personal information and account details
                            </div>

                            <form id="editProfileForm" action="{{ route('profile.update') }}" method="post" novalidate>
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="space-y-2">
                                        <label for="first_name" class="block font-medium" style="color:#4d3327;">First Name *</label>
                                        <input
                                            type="text"
                                            id="first_name"
                                            name="first_name"
                                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                            style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                            value="{{ old('first_name', auth()->user()->first_name ?? '') }}"
                                            autocomplete="given-name"
                                            required
                                        >
                                    </div>

                                    <div class="space-y-2">
                                        <label for="last_name" class="block font-medium" style="color:#4d3327;">Last Name *</label>
                                        <input
                                            type="text"
                                            id="last_name"
                                            name="last_name"
                                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                            style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                            value="{{ old('last_name', auth()->user()->last_name ?? '') }}"
                                            autocomplete="family-name"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="space-y-2 mb-6">
                                    <label for="email" class="block font-medium" style="color:#4d3327;">Email Address *</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                        style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                        value="{{ old('email', auth()->user()->email ?? '') }}"
                                        autocomplete="email"
                                        required
                                    >
                                </div>

                                <div class="space-y-2 mb-6">
                                    <label for="mobile_number" class="block font-medium" style="color:#4d3327;">Phone Number</label>
                                    <input
                                        type="tel"
                                        id="mobile_number"
                                        name="mobile_number"
                                        class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                        style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                        value="{{ old('mobile_number', auth()->user()->phone ?? '') }}"
                                        autocomplete="tel"
                                    >
                                </div>

                                <div class="space-y-2 mb-6">
                                    <label for="address" class="block font-medium" style="color:#4d3327;">Address</label>
                                    <input
                                        type="text"
                                        id="address"
                                        name="address"
                                        class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                        style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                        value="{{ old('address', auth()->user()->address ?? '') }}"
                                        autocomplete="address-line1"
                                    >
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div class="space-y-2">
                                        <label for="zip_code" class="block font-medium" style="color:#4d3327;">Zip Code</label>
                                        <input
                                            type="text"
                                            id="zip_code"
                                            name="zip_code"
                                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                            style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                            value="{{ old('zip_code', auth()->user()->zip_code ?? '') }}"
                                        >
                                    </div>

                                    <div class="space-y-2">
                                        <label for="date_of_birth" class="block font-medium" style="color:#4d3327;">Date of Birth</label>
                                        <input
                                            type="date"
                                            id="date_of_birth"
                                            name="date_of_birth"
                                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                            style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                            value="{{ old('date_of_birth', auth()->user()->date_of_birth ?? '') }}"
                                            autocomplete="bday"
                                        >
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3 mt-8 pt-6"
                                     style="border-top:1px solid rgba(204,203,189,0.9);">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg transition transform hover:-translate-y-0.5 hover:shadow-md"
                                        style="background-color:#00848d;color:#f6f3ec;"
                                    >
                                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                                        Update Profile
                                    </button>

                                    <button
                                        type="reset"
                                        class="inline-flex items-center gap-2 px-6 py-2 rounded-lg transition hover:shadow-sm"
                                        style="border:1px solid rgba(204,203,189,0.9);color:#4d3327;background-color:#ffffff;"
                                    >
                                        <i class="bi bi-arrow-clockwise" aria-hidden="true"></i>
                                        Reset
                                    </button>
                                </div>
                            </form>
                        </section>

                        {{-- BOOKINGS --}}
                        <section
                            id="bookings-panel"
                            class="tab-panel p-6 md:p-8 hidden opacity-0 transform scale-95 transition-all duration-300"
                            role="tabpanel"
                            aria-labelledby="bookings-tab"
                        >
                            <div id="bookings-description" class="sr-only">
                                View your booking history and manage reservations
                            </div>

                            <div class="flex items-center justify-between gap-4 mb-6">
                                <div>
                                    <h3 class="text-2xl font-semibold" style="color:#4d3327;">My Bookings</h3>
                                    <p class="text-sm mt-1" style="color:#7a7d52;">Open a booking to view its separate add-on transactions.</p>
                                </div>
                                <a href="{{ route('booking.calendar') }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium"
                                   style="background-color:#db8636;color:#f6f3ec;text-decoration:none;">
                                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                                    Book a Stay
                                </a>
                            </div>

                            @if(($bookings ?? collect())->isEmpty())
                                <div class="text-center py-10 rounded-xl" style="background-color:#f6f3ec;border:1px solid #cccbbd;">
                                    <i class="bi bi-calendar-check text-5xl" aria-hidden="true" style="color:#00848d;"></i>
                                    <p class="mt-3" style="color:#4d3327;">You have no bookings yet.</p>
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($bookings as $booking)
                                        @php
                                            $addOnCount = $booking->addOnTransactions->count();
                                            $addOnTotal = $booking->add_ons_total;
                                        @endphp
                                        <article class="rounded-xl p-5" style="background-color:#fff;border:1px solid #cccbbd;">
                                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                                <div>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h4 class="text-lg font-bold" style="color:#4d3327;">{{ $booking->transaction_id }}</h4>
                                                        @if($addOnCount > 0)
                                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color:rgba(0,132,141,.1);color:#00848d;">
                                                                {{ $addOnCount }} {{ \Illuminate\Support\Str::plural('add-on', $addOnCount) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-sm mt-1" style="color:#7a7d52;">{{ $booking->display_type_label }}</p>
                                                    <p class="text-sm mt-2" style="color:#4d3327;">
                                                        {{ optional($booking->checkin_date)->format('M d, Y') ?? 'Date unavailable' }}
                                                        @if($booking->display_checkout_date)
                                                            &ndash; {{ $booking->display_checkout_date->format('M d, Y') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="sm:text-right">
                                                    <p class="text-xs uppercase tracking-wide" style="color:#7a7d52;">Original Booking Total</p>
                                                    <p class="text-xl font-bold" style="color:#98655b;">&#8369;{{ number_format((float) $booking->total_amount, 2) }}</p>
                                                    <p class="text-xs font-semibold uppercase mt-1" style="color:#222c1e;">{{ $booking->payment_status }}</p>
                                                </div>
                                            </div>

                                            @if($addOnCount > 0)
                                                <div class="flex justify-between text-sm mt-4 pt-4" style="border-top:1px solid #e5e2dc;color:#4d3327;">
                                                    <span>Add-ons Total</span>
                                                    <strong>&#8369;{{ number_format($addOnTotal, 2) }}</strong>
                                                </div>
                                            @endif

                                            <div class="mt-4">
                                                <a href="{{ route('profile.bookings.show', $booking) }}"
                                                   class="inline-flex items-center gap-2 text-sm font-semibold"
                                                   style="color:#00848d;text-decoration:none;">
                                                    View Booking Details
                                                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>

                        {{-- SECURITY --}}
                        <section
                            id="password-panel"
                            class="tab-panel p-6 md:p-8 hidden opacity-0 transform scale-95 transition-all duration-300"
                            role="tabpanel"
                            aria-labelledby="password-tab"
                        >
                            <div id="password-description" class="sr-only">
                                Change your password and manage security settings
                            </div>

                            <form id="changePasswordForm" action="#" method="post" novalidate>
                                @csrf
                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label for="current_password" class="block font-medium" style="color:#4d3327;">Current Password *</label>
                                        <input
                                            type="password"
                                            id="current_password"
                                            name="current_password"
                                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                            style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                            required
                                            autocomplete="current-password"
                                        >
                                    </div>

                                    <div class="space-y-2">
                                        <label for="new_password" class="block font-medium" style="color:#4d3327;">New Password *</label>
                                        <input
                                            type="password"
                                            id="new_password"
                                            name="new_password"
                                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                            style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                            required
                                            autocomplete="new-password"
                                        >
                                    </div>

                                    <div class="space-y-2">
                                        <label for="confirm_password" class="block font-medium" style="color:#4d3327;">Confirm New Password *</label>
                                        <input
                                            type="password"
                                            id="confirm_password"
                                            name="confirm_password"
                                            class="w-full px-4 py-2 rounded-lg focus:ring-2 focus:outline-none"
                                            style="border:1px solid rgba(204,203,189,0.9);background-color:#ffffff;--tw-ring-color:#00848d;"
                                            required
                                            autocomplete="new-password"
                                        >
                                    </div>

                                    <div class="flex flex-wrap gap-3 mt-8 pt-6"
                                         style="border-top:1px solid rgba(204,203,189,0.9);">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-2 px-6 py-2 rounded-lg transition transform hover:-translate-y-0.5 hover:shadow-md"
                                            style="background-color:#222c1e;color:#f6f3ec;"
                                        >
                                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                                            Update Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                {{-- SIDEBAR --}}
                <aside class="space-y-6">
                    {{-- Quick actions --}}
                    <div
                        class="rounded-xl p-6 shadow-sm"
                        style="background-color:#ffffff;border:1px solid rgba(204,203,189,0.9);"
                    >
                        <h3 class="text-lg font-semibold mb-4 pb-2"
                            style="border-bottom:1px solid rgba(204,203,189,0.9);color:#4d3327;">
                            Quick Actions
                        </h3>

                        <div class="space-y-2">
                            <a
                                href="{{ route('booking.calendar') }}"
                                class="flex items-center gap-3 p-3 rounded-lg transition-colors no-underline"
                                style="color:#4d3327;border:1px solid transparent;"
                            >
                                <i class="bi bi-plus-circle text-xl" aria-hidden="true" style="color:#db8636;"></i>
                                <div>
                                    <strong class="block font-medium">Book a Stay</strong>
                                    <small class="text-sm" style="color:#4d3327;">Reserve your next visit</small>
                                </div>
                            </a>

                            <a
                                href="{{ route('profile.show') }}#bookings"
                                class="flex items-center gap-3 p-3 rounded-lg transition-colors no-underline"
                                style="color:#4d3327;border:1px solid transparent;"
                            >
                                <i class="bi bi-calendar-check text-xl" aria-hidden="true" style="color:#00848d;"></i>
                                <div>
                                    <strong class="block font-medium">My Bookings</strong>
                                    <small class="text-sm" style="color:#4d3327;">View booking history</small>
                                </div>
                            </a>

                            <a
                                href="/checkinout"
                                class="flex items-center gap-3 p-3 rounded-lg transition-colors no-underline"
                                style="color:#4d3327;border:1px solid transparent;"
                            >
                                <i class="bi bi-person-check text-xl" aria-hidden="true" style="color:#7a7d52;"></i>
                                <div>
                                    <strong class="block font-medium">Check-in/out</strong>
                                    <small class="text-sm" style="color:#4d3327;">Manage your stays</small>
                                </div>
                            </a>
                        </div>
                    </div>

                    {{-- Account status --}}
                    <div
                        class="rounded-xl p-6 shadow-sm"
                        style="background-color:#ffffff;border:1px solid rgba(204,203,189,0.9);"
                    >
                        <h3 class="text-lg font-semibold mb-4 pb-2"
                            style="border-bottom:1px solid rgba(204,203,189,0.9);color:#4d3327;">
                            Account Status
                        </h3>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-2 rounded-lg"
                                 style="background-color:rgba(122,125,82,0.1);color:#222c1e;">
                                <i class="bi bi-shield-check" aria-hidden="true" style="color:#7a7d52;"></i>
                                <span>Account Verified</span>
                            </div>

                            <div class="flex items-center gap-3 p-2 rounded-lg"
                                 style="background-color:rgba(122,125,82,0.1);color:#222c1e;">
                                <i class="bi bi-envelope-check" aria-hidden="true" style="color:#7a7d52;"></i>
                                <span>Email Confirmed</span>
                            </div>

                            <div class="flex items-center gap-3 p-2 rounded-lg"
                                 style="background-color:rgba(246,243,236,0.9);color:#4d3327;">
                                <i class="bi bi-credit-card" aria-hidden="true" style="color:#4d3327;"></i>
                                <span>Payment Method</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <x-footer />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initializeProfileTabs();

            const requestedTab = window.location.hash.replace('#', '');
            if (['overview', 'bookings', 'edit', 'password'].includes(requestedTab)) {
                switchTab(requestedTab);
            }
        });

        function initializeProfileTabs() {
            const tabs = document.querySelectorAll('.profile-tab');

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const tabId = this.getAttribute('data-tab');
                    switchTab(tabId);
                });
            });
        }

        function resetTabStyles() {
            const tabs = document.querySelectorAll('.profile-tab');
            tabs.forEach(tab => {
                tab.dataset.active = 'false';
                tab.style.backgroundColor = 'transparent';
                tab.style.color = '#4d3327';
            });
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.add('hidden', 'opacity-0', 'scale-95');
                panel.classList.remove('opacity-100', 'scale-100');
                panel.dataset.active = 'false';
            });

            resetTabStyles();

            const targetPanel = document.getElementById(tabId + '-panel');
            const targetTab = document.getElementById(tabId + '-tab');

            if (targetPanel && targetTab) {
                targetPanel.classList.remove('hidden', 'opacity-0', 'scale-95');
                targetPanel.classList.add('opacity-100', 'scale-100');
                targetPanel.dataset.active = 'true';

                targetTab.dataset.active = 'true';
                targetTab.style.backgroundColor = '#98655b';
                targetTab.style.color = '#f6f3ec';

                window.location.hash = tabId;
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>
