{{-- resources/views/components/booking-progress.blade.php --}}
@props([
    'steps' => [],
    'currentStep' => 1
])

<div class="bg-white shadow-sm border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-3">
        <div class="flex items-center justify-center space-x-2 sm:space-x-4 max-w-md sm:max-w-lg mx-auto">
            @foreach($steps as $index => $step)
                {{-- Step {{ $index + 1 }} --}}
                <div class="flex flex-col items-center
                    {{ $step['status'] === 'completed' ? 'text-green-600' :
                       ($step['status'] === 'current' ? 'text-[#8f5d54]' : 'text-gray-400') }}">
                    @if($step['status'] === 'completed')
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mb-1">
                            <i class="bi bi-check-circle-fill text-sm"></i>
                        </div>
                    @else
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold mb-1
                            {{ $step['status'] === 'current' ? 'bg-[#8f5d54] text-white' : 'bg-gray-300 text-gray-600' }}">
                            {{ $index + 1 }}
                        </div>
                    @endif
                    <span class="text-sm font-medium">{{ $step['label'] }}</span>
                </div>

                @if(!$loop->last)
                    <div class="flex-1 h-px bg-gray-300"></div>
                @endif
            @endforeach
        </div>
    </div>
</div>