@extends('layouts.admin-guest')

@section('title', 'Recovery Codes')

@section('content')
<x-auth.card
    title="Recovery Codes"
    :is-admin="$isAdmin"
    :eyebrow="$isAdmin ? 'Admin Security' : null"
    subtitle="Store these recovery codes somewhere safe. Each code can only be used once."
>
    <div class="space-y-5">
        @if(count($codes) > 0)
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <ul class="grid gap-2 font-mono text-sm text-gray-800 sm:grid-cols-2">
                    @foreach($codes as $code)
                        <li class="rounded-lg border border-gray-200 bg-white px-3 py-2">{{ $code }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Recovery codes are only shown immediately after setup or regeneration.
            </div>
        @endif

        @if($mustAcknowledge)
            <form method="POST" action="{{ route('two-factor.recovery-codes.continue') }}" class="space-y-4">
                @csrf

                <label class="flex gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700">
                    <input type="checkbox" name="saved_recovery_codes" value="1" class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500" required>
                    <span>I saved my recovery codes.</span>
                </label>

                @error('saved_recovery_codes')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full rounded-lg px-4 py-3 font-semibold text-white shadow-md transition hover:opacity-90" style="background-color: {{ $isAdmin ? '#8f5d54' : '#2563eb' }};">
                    Continue to Dashboard
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('two-factor.recovery-codes.regenerate') }}" class="space-y-3">
                @csrf
                <button type="submit" class="w-full rounded-lg border border-gray-300 px-4 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                    Regenerate Recovery Codes
                </button>
            </form>
        @endif
    </div>
</x-auth.card>
@endsection
