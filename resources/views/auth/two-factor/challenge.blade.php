@extends('layouts.admin-guest')

@section('title', 'Two-Step Verification')
@section('body-class', 'h-dvh overflow-hidden')

@section('content')
@php
    $usingRecovery = $mode === 'recovery';
    $remainingMinutes = intdiv($challengeRemainingSeconds, 60);
    $remainingSeconds = $challengeRemainingSeconds % 60;
@endphp

<x-auth.card
    title="Two-Step Verification"
    :is-admin="$isAdmin"
    :eyebrow="$isAdmin ? 'Admin Security' : null"
    :subtitle="$isAdmin ? 'For your security, verify your identity before accessing the Admin Dashboard.' : 'Enter your verification code to continue.'"
    :compact="true"
>
    <p class="challenge-expiry mb-3 text-center text-sm text-gray-600">
        Verification session expires in
        <strong
            data-two-factor-countdown
            data-expires-at="{{ $challengeExpiresAt }}"
            data-server-now="{{ $serverNow }}"
            class="tabular-nums text-gray-900"
        >{{ sprintf('%02d:%02d', $remainingMinutes, $remainingSeconds) }}</strong>.
    </p>

    @if(! $usingRecovery)
        <form method="POST" action="{{ route('two-factor.challenge.verify') }}" class="space-y-3">
            @csrf

            <div class="challenge-guidance rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                <p class="font-semibold text-gray-900">Step 2 of 2</p>
                <p class="mt-1">Enter the 6-digit verification code from your authenticator app.</p>
                <p class="mt-2 text-xs text-gray-500">Account: {{ $maskedAccount }}</p>
            </div>

            <x-auth.otp-input id="challenge_two_factor_code" :segmented="true" />

            @error('code')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full rounded-lg px-4 py-2.5 font-semibold text-white shadow-md transition hover:opacity-90" style="background-color: {{ $isAdmin ? '#8f5d54' : '#2563eb' }};">
                Verify &amp; Continue
            </button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('two-factor.recovery') }}" class="text-sm font-medium hover:underline" style="color: {{ $isAdmin ? '#8f5d54' : '#2563eb' }};">
                Use a recovery code instead
            </a>
        </div>
    @else
        <form method="POST" action="{{ route('two-factor.recovery.verify') }}" class="space-y-3">
            @csrf

            <div class="challenge-guidance rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                <p class="font-semibold text-gray-900">Recovery Code</p>
                <p class="mt-1">Enter one of your saved recovery codes to regain access to your account.</p>
                <p class="mt-2 text-xs text-gray-500">Account: {{ $maskedAccount }}</p>
            </div>

            <div>
                <label for="recovery_code" class="block text-sm font-medium text-gray-700">Recovery code</label>
                <input
                    id="recovery_code"
                    name="recovery_code"
                    type="text"
                    autocomplete="one-time-code"
                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-3 font-mono text-sm uppercase tracking-wider outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>

            @error('recovery_code')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full rounded-lg px-4 py-2.5 font-semibold text-white shadow-md transition hover:opacity-90" style="background-color: {{ $isAdmin ? '#8f5d54' : '#2563eb' }};">
                Verify Recovery Code
            </button>
        </form>

        <div class="mt-3 text-center">
            <a href="{{ route('two-factor.challenge') }}" class="text-sm font-medium hover:underline" style="color: {{ $isAdmin ? '#8f5d54' : '#2563eb' }};">
                &larr; Use authenticator code
            </a>
        </div>
    @endif

    <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 hover:underline">
            &larr; Back to login
        </button>
    </form>
</x-auth.card>
@endsection
