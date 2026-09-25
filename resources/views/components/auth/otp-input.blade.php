@props([
    'name' => 'code',
    'id' => 'two_factor_code',
])

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
        class="h-14 w-full rounded-lg border border-gray-300 bg-white px-4 text-center text-xl font-semibold tracking-[0.45em] text-gray-900 outline-none transition placeholder:text-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
        required
    >
    <p id="{{ $id }}_hint" class="text-xs text-gray-500">Enter the current 6-digit code.</p>
</div>
