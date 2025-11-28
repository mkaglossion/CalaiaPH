@props([
    'currentStep' => 2,
    'totalSteps' => 4,
    'steps' => null,
    'flow' => 'booking', // booking, staycation, dayvisit, special-event
    'bgClass' => 'bg-white',
    'borderClass' => 'border-gray-100',
    'activeColor' => '#8f5d54',
    'showLabels' => true
])

@php
    // Define default steps for different flows
    $defaultSteps = [
        'booking' => [
            1 => ['label' => 'Details', 'active' => false],
            2 => ['label' => 'Activities', 'active' => true],
            3 => ['label' => 'Payment', 'active' => false],
            4 => ['label' => 'Confirmation', 'active' => false],
        ],
        'staycation' => [
            1 => ['label' => 'Accommodation', 'active' => false],
            2 => ['label' => 'Activities', 'active' => true],
            3 => ['label' => 'Payment', 'active' => false],
            4 => ['label' => 'Confirmation', 'active' => false],
        ],
        'dayvisit' => [
            1 => ['label' => 'Details', 'active' => false],
            2 => ['label' => 'Activities', 'active' => true],
            3 => ['label' => 'Payment', 'active' => false],
            4 => ['label' => 'Confirmation', 'active' => false],
        ],
        'special-event' => [
            1 => ['label' => 'Event Selection', 'active' => false],
            2 => ['label' => 'Details', 'active' => false],
            3 => ['label' => 'Activities', 'active' => true],
            4 => ['label' => 'Payment', 'active' => false],
            5 => ['label' => 'Confirmation', 'active' => false],
        ],
    ];

    $steps = $steps ?? ($defaultSteps[$flow] ?? $defaultSteps['booking']);

    // Mark current step as active
    if (isset($steps[$currentStep])) {
        foreach ($steps as $key => $step) {
            $steps[$key]['active'] = ($key === $currentStep);
        }
    }
@endphp

{{-- Progress Indicator --}}
<div class="{{ $bgClass }} shadow-sm border-b {{ $borderClass }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
            @foreach($steps as $stepNumber => $step)
                {{-- Step Circle --}}
                <div class="flex flex-col items-center {{ $step['active'] ? 'text-[' . $activeColor . ']' : 'text-gray-400' }}">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 {{ $step['active'] ? 'bg-[' . $activeColor . '] text-white' : 'bg-gray-200 text-gray-600' }} rounded-full flex items-center justify-center text-xs sm:text-sm font-bold mb-1">
                        {{ $stepNumber }}
                    </div>
                    @if($showLabels)
                        <span class="text-xs sm:text-sm font-medium text-center">{{ $step['label'] }}</span>
                    @endif
                </div>

                {{-- Connector Line --}}
                @if($stepNumber < $totalSteps)
                    <div class="flex-1 h-px {{ $step['active'] && isset($steps[$stepNumber + 1]) && $steps[$stepNumber + 1]['active'] ? 'bg-[' . $activeColor . ']' : 'bg-gray-300' }}"></div>
                @endif
            @endforeach
        </div>
    </div>
</div>