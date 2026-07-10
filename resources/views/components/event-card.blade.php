@props(['event'])

<div class="bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
    <!-- Event Image -->
    <div class="relative h-48 bg-gray-200 overflow-hidden">
        @if($event->image)
            <img src="{{ $event->image }}" alt="{{ $event->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gray-300">
                <i class="bi bi-calendar-event text-5xl text-gray-400"></i>
            </div>
        @endif
        <!-- Event Badge -->
        <div class="absolute top-4 left-4">
            <span class="inline-block bg-brand text-white px-3 py-1 rounded-full text-sm font-semibold">
                Special Event
            </span>
        </div>
    </div>

    <!-- Event Content -->
    <div class="p-6">
        <h3 class="text-xl font-bold text-gray-900 mb-2">
            {{ $event->name }}
        </h3>
        
        <p class="text-gray-600 text-sm mb-4">
            {{ $event->description }}
        </p>

        <!-- Features -->
        @if($event->features)
            <div>
                <p class="text-sm font-semibold text-gray-900 mb-3">What's Included:</p>
                <ul class="space-y-2">
                    @foreach(array_map('trim', explode(',', $event->features)) as $feature)
                        <li class="flex items-center gap-3 text-gray-700 text-sm">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-brand text-white text-xs font-bold flex-shrink-0">✓</span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
</div>
