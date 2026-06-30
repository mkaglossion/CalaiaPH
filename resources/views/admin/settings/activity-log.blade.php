@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-semibold text-gray-900">Activity Log</h1>
        <p class="text-gray-500 mt-1">View your recent admin activities</p>
    </div>

    <!-- Activity Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :style="{
                                        'background-color': '{{ in_array($activity->action, ['create', 'store']) ? '#dcfce7' : (in_array($activity->action, ['update', 'edit']) ? '#dbeafe' : '#fee2e2') }}',
                                        'color': '{{ in_array($activity->action, ['create', 'store']) ? '#166534' : (in_array($activity->action, ['update', 'edit']) ? '#1e40af' : '#991b1b') }}'
                                    }">
                                    {{ ucfirst($activity->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($activity->model_type)
                                    {{ class_basename($activity->model_type) }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $activity->description ?? '—' }}
                                @if($activity->model_id)
                                    <span class="text-gray-400">#{{ $activity->model_id }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                @if($activity->ip_address)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm text-gray-700">{{ $activity->ip_address }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $activity->ip_address }}').then(()=>{this.innerText='Copied'}).catch(()=>{})" class="text-xs text-gray-500 hover:text-gray-700">Copy</button>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <span title="{{ $activity->created_at }}">
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <p>No activity recorded yet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $activities->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div
        data-admin-flash
        data-admin-flash-library="toastr"
        data-admin-flash-title="Success"
        data-admin-flash-type="success"
        data-admin-flash-message="{{ e(session('success')) }}"
    ></div>
@endif

@if(session('error'))
    <div
        data-admin-flash
        data-admin-flash-library="toastr"
        data-admin-flash-title="Error"
        data-admin-flash-type="error"
        data-admin-flash-message="{{ e(session('error')) }}"
    ></div>
@endif
@endsection
