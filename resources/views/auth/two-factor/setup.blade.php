@extends('layouts.admin-guest')

@section('title', 'Two-Factor Authentication Setup')

@section('content')
<x-auth.card
    title="Two-Factor Authentication Setup"
    :is-admin="$isAdmin"
    :eyebrow="$isAdmin ? 'Admin Security' : null"
    :wide="true"
    subtitle="Set up an authenticator app before continuing to your account."
>
    <div class="grid gap-6 md:grid-cols-[1.15fr_0.85fr] md:items-start">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 sm:p-6">
            <ol class="space-y-3 text-sm leading-6 text-gray-700">
                <li><span class="font-semibold text-gray-900">Step 1.</span> Open Google Authenticator, Microsoft Authenticator, Authy, or another TOTP app.</li>
                <li><span class="font-semibold text-gray-900">Step 2.</span> Scan this QR code, or enter the setup key manually.</li>
            </ol>

            <div class="mt-5 flex min-h-[246px] items-center justify-center rounded-xl border border-gray-200 bg-white p-3">
                <canvas
                    id="two-factor-qr"
                    class="block h-[220px] w-[220px] max-w-full"
                    width="220"
                    height="220"
                    data-qr-uri="{{ $provisioningUri }}"
                    aria-label="Two-factor setup QR code"
                ></canvas>
                <p id="two-factor-qr-error" class="max-w-xs text-center text-sm text-red-600" hidden>
                    The QR code could not be displayed. Use the setup key below instead.
                </p>
            </div>

            <details class="mt-4 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">
                <summary class="cursor-pointer font-semibold text-gray-700">Can't scan? Show setup key</summary>
                <p class="mt-2 break-all font-mono text-xs text-gray-700">{{ $secret }}</p>
            </details>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-gray-500">Final step</p>
                <h2 class="mt-2 text-xl font-semibold text-gray-900">Verify your authenticator</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600">Enter the 6-digit code shown in your app to finish setup.</p>
            </div>

            <form method="POST" action="{{ route('two-factor.setup.confirm') }}" class="space-y-4">
                @csrf

                <x-auth.otp-input id="setup_two_factor_code" />

                @error('code')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full rounded-lg px-4 py-3 font-semibold text-white shadow-md transition hover:opacity-90" style="background-color: {{ $isAdmin ? '#8f5d54' : '#2563eb' }};">
                    Enable Two-Factor Authentication
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-5 text-center">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 hover:underline">
                    &larr; Back to login
                </button>
            </form>
        </div>
    </div>

    @vite(['resources/js/two-factor-setup.js'])
</x-auth.card>
@endsection
