@props([
    'name' => 'code',
    'id' => 'two_factor_code',
    'segmented' => false,
])

@if($segmented)
    <div class="otp-segmented space-y-2" data-otp-segmented>
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">Verification code</label>

        <div class="relative">
            <input
                id="{{ $id }}"
                name="{{ $name }}"
                type="text"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                autocomplete="one-time-code"
                value="{{ old($name) }}"
                aria-describedby="{{ $id }}_hint"
                aria-label="6-digit verification code"
                class="absolute inset-0 z-10 h-full w-full cursor-text opacity-0"
                data-otp-input
                required
            >

            <div class="grid grid-cols-6 gap-2" aria-hidden="true">
                @for($index = 0; $index < 6; $index++)
                    <span
                        class="otp-segmented-cell flex aspect-square min-w-0 items-center justify-center rounded-lg border border-gray-300 bg-white text-xl font-semibold text-gray-900"
                        data-otp-cell
                        data-active="false"
                        data-filled="false"
                    ></span>
                @endfor
            </div>
        </div>

        <p id="{{ $id }}_hint" class="text-xs text-gray-500">Enter or paste the current 6-digit code.</p>
    </div>
@else
    <div class="space-y-2">
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">Verification code</label>
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="text"
            inputmode="numeric"
            pattern="[0-9]{6}"
            maxlength="6"
            autocomplete="one-time-code"
            value="{{ old($name) }}"
            placeholder="000000"
            aria-describedby="{{ $id }}_hint"
            class="h-14 w-full rounded-lg border border-gray-300 bg-white px-4 text-center text-xl font-semibold tracking-normal text-gray-900 outline-none transition placeholder:text-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
            required
        >
        <p id="{{ $id }}_hint" class="text-xs text-gray-500">Enter the current 6-digit code.</p>
    </div>
@endif
