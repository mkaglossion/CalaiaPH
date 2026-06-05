@extends('layouts.admin')

@section('title', 'System Alerts')

@section('content')
<div class="space-y-6">
    @php
        $statusTabs = [
            'unresolved' => ['label' => 'Unresolved', 'count' => $counts['unresolved']],
            'resolved' => ['label' => 'Resolved', 'count' => $counts['resolved']],
            'all' => ['label' => 'All', 'count' => $counts['all']],
        ];
        $severityAllCount = $status === 'resolved'
            ? $counts['resolved']
            : ($status === 'all' ? $counts['all'] : $counts['unresolved']);
        $severityTabs = [
            'all' => ['label' => 'All', 'count' => $severityAllCount, 'class' => 'bg-white text-gray-700 border-gray-200'],
            'critical' => ['label' => 'Critical', 'count' => $counts['critical'], 'class' => 'bg-red-100 text-red-700 border-red-200'],
            'warning' => ['label' => 'Warning', 'count' => $counts['warning'], 'class' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
            'info' => ['label' => 'Info', 'count' => $counts['info'], 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
        ];
        $baseQuery = request()->except(['page']);
    @endphp

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">System Alerts</h1>
            <p class="text-gray-500 mt-1">Monitor critical system and operational alerts</p>
        </div>
        @if($counts['resolved'] > 0)
            <form method="POST" action="{{ route('admin.settings.alerts.clear') }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                    <i data-lucide="trash" class="w-4 h-4 mr-2"></i>
                    Clear Old Resolved
                </button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        <div class="flex flex-wrap gap-2">
            @foreach($statusTabs as $key => $tab)
                <a
                    href="{{ route('admin.settings.alerts', array_merge($baseQuery, ['status' => $key])) }}"
                    class="px-3 py-1.5 border rounded-lg text-sm font-medium transition {{ $status === $key ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}"
                >
                    {{ $tab['label'] }} ({{ number_format($tab['count']) }})
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach($severityTabs as $key => $tab)
                <a
                    href="{{ route('admin.settings.alerts', array_merge($baseQuery, ['severity' => $key])) }}"
                    class="px-3 py-1.5 border rounded-lg text-sm font-medium transition {{ $severity === $key ? $tab['class'] . ' ring-2 ring-offset-1 ring-gray-300' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}"
                >
                    {{ $tab['label'] }} ({{ number_format($tab['count']) }})
                </a>
            @endforeach
        </div>
    </div>

    <!-- Alerts List -->
    <div class="space-y-3">
        @forelse($alerts as $alert)
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
                            @if($alert->resolved_at)
                                <span class="text-xs text-green-600">Resolved {{ $alert->resolved_at->diffForHumans() }}</span>
                            @endif
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

                    @if(!$alert->resolved_at)
                        <form method="POST" action="{{ route('admin.settings.alerts.resolve', $alert->id) }}" class="ml-4 inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition text-sm">
                                <i data-lucide="check" class="w-4 h-4 mr-1"></i>
                                Resolve
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg p-8 text-center shadow-sm">
                <i data-lucide="check-circle" class="w-12 h-12 text-green-500 mx-auto mb-3"></i>
                <p class="text-gray-900 font-medium">No Alerts Found</p>
                <p class="text-gray-500 text-sm mt-1">
                    No {{ $status === 'all' ? '' : $status . ' ' }}alerts{{ $severity !== 'all' ? ' for severity ' . ucfirst($severity) : '' }} at this time
                </p>
            </div>
        @endforelse
    </div>

    @if($alerts->hasPages())
        <div class="mt-6">
            {{ $alerts->links() }}
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
