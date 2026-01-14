@props(['stepNumber', 'totalSteps', 'bookingType'])

@php
    $stepLabels = [
        'staycation' => [
            1 => 'Check-in & Check-out Date',
            2 => 'Select Your Staycation',
            3 => 'Select Activities',
            4 => 'Confirm Booking'
        ]
    ];
@endphp

{{-- Progress indicator --}}
<div class="mb-4">
    {{-- limit width + center --}}
    <div class="max-w-4xl md:max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-2">
            <small class="text-gray-500 text-sm">
                Step {{ $stepNumber }} of {{ $totalSteps }}: {{ $stepLabels[$bookingType][$stepNumber] ?? 'Unknown Step' }}
            </small>
            <small class="text-gray-500 text-sm">{{ $stepNumber * 33 }}% Complete</small>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5 md:h-2">
            <div
                class="bg-[#8f5d54] h-1.5 md:h-2 rounded-full transition-all duration-300"
                style="width: {{ $stepNumber * 33 }}%">
            </div>
        </div>
    </div>
</div>
