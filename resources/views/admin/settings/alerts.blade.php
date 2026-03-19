@extends('layouts.admin')

@section('title', 'System Alerts')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">System Alerts</h1>
            <p class="text-gray-500 mt-1">Monitor critical system and operational alerts</p>
        </div>
        @if($unresolved->total() > 0)
            <form method="POST" action="{{ route('admin.settings.alerts.clear') }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                    <i data-lucide="trash" class="w-4 h-4 mr-2"></i>
                    Clear Resolved
                </button>
            </form>
        @endif
    </div>

    <!-- Filter by Severity -->
    <div class="flex gap-2">
        <a href="{{ route('admin.settings.alerts') }}" class="px-3 py-1 bg-white border rounded-lg text-sm font-medium hover:bg-gray-50">
            All
        </a>
        <button class="px-3 py-1 bg-red-100 text-red-700 border border-red-200 rounded-lg text-sm font-medium">
            Critical
        </button>
        <button class="px-3 py-1 bg-yellow-100 text-yellow-700 border border-yellow-200 rounded-lg text-sm font-medium">
            Warning
        </button>
        <button class="px-3 py-1 bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-sm font-medium">
            Info
        </button>
    </div>

    <!-- Alerts List -->
    <div class="space-y-3">
        @forelse($unresolved as $alert)
            <div class="bg-white rounded-lg border-l-4 p-4 shadow-sm"
                :style="{
                    'border-color': '{{ in_array($alert->severity, ['critical']) ? '#dc2626' : (in_array($alert->severity, ['warning']) ? '#f59e0b' : '#3b82f6') }}'
                }">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :style="{
                                    'background-color': '{{ in_array($alert->severity, ['critical']) ? '#fee2e2' : (in_array($alert->severity, ['warning']) ? '#fef3c7' : '#dbeafe') }}',
                                    'color': '{{ in_array($alert->severity, ['critical']) ? '#991b1b' : (in_array($alert->severity, ['warning']) ? '#92400e' : '#1e40af') }}'
                                }">
                                {{ ucfirst($alert->severity) }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $alert->created_at->format('M d, Y H:i') }}</span>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $alert->type)) }}</h3>
                        <p class="text-gray-700 mt-1">{{ $alert->message }}</p>

                        @if($alert->data)
                            <div class="mt-2 text-sm text-gray-600">
                                @foreach($alert->data as $key => $value)
                                    <p><strong>{{ ucfirst($key)}}:</strong> {{ $value }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.settings.alerts.resolve', $alert->id) }}" class="ml-4 inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition text-sm">
                            <i data-lucide="check" class="w-4 h-4 mr-1"></i>
                            Resolve
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                <i data-lucide="check-circle" class="w-12 h-12 text-green-500 mx-auto mb-3"></i>
                <p class="text-gray-900 font-medium">All Clear!</p>
                <p class="text-gray-500 text-sm mt-1">No unresolved alerts at this time</p>
            </div>
        @endforelse
    </div>

    @if($unresolved->hasPages())
        <div class="mt-6">
            {{ $unresolved->links() }}
        </div>
    @endif
</div>

@if(session('success'))
    <script>
        toastr.success("{{ session('success') }}", 'Success');
    </script>
@endif

@if(session('error'))
    <script>
        toastr.error("{{ session('error') }}", 'Error');
    </script>
@endif
@endsection
