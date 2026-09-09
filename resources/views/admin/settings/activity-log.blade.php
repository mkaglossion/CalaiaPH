@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
@php
    $hasFilters = collect($filters ?? [])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
    $formatValue = function ($value) {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return filled($value) ? (string) $value : 'None';
    };
@endphp

<div
    class="space-y-6"
    x-data="{
        autoRefresh: localStorage.getItem('adminActivityAutoRefresh') === '1',
        seconds: 30,
        timer: null,
        start() {
            clearInterval(this.timer);
            this.seconds = 30;
            this.timer = setInterval(() => {
                this.seconds--;
                if (this.seconds <= 0) window.location.reload();
            }, 1000);
        },
        toggle() {
            this.autoRefresh = !this.autoRefresh;
            localStorage.setItem('adminActivityAutoRefresh', this.autoRefresh ? '1' : '0');
            if (this.autoRefresh) this.start(); else clearInterval(this.timer);
        }
    }"
    x-init="if (autoRefresh) start()"
>
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#8f5d54]">Administration</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Activity Log</h1>
            <p class="mt-1 text-sm text-slate-500">Search and review successful admin changes recorded in this environment.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                @click="toggle()"
                class="inline-flex h-10 items-center gap-2 rounded-xl border px-4 text-sm font-semibold transition"
                :class="autoRefresh ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
            >
                <i class="bi bi-broadcast"></i>
                <span x-text="autoRefresh ? `Auto-refresh ${seconds}s` : 'Auto-refresh off'"></span>
            </button>
            <a href="{{ request()->fullUrl() }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </a>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">All Activity</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Today</p>
            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($stats['today']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Matching Filters</p>
            <p class="mt-2 text-2xl font-bold text-blue-700">{{ number_format($stats['filtered']) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latest Record</p>
            <p class="mt-2 text-sm font-bold text-slate-900">
                {{ $stats['latest'] ? \Carbon\Carbon::parse($stats['latest'])->diffForHumans() : 'No records yet' }}
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.settings.activity-log') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-12">
            <label class="block xl:col-span-4">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Search</span>
                <span class="relative block">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input name="q" value="{{ $filters['q'] ?? '' }}" type="search" placeholder="Description, admin, model, IP..." class="h-11 w-full rounded-xl border border-slate-300 bg-white pl-10 text-sm shadow-sm transition hover:border-slate-400 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                </span>
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Action</span>
                <select name="action" class="h-11 w-full rounded-xl border border-slate-300 bg-white text-sm shadow-sm transition hover:border-slate-400 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                    <option value="">All actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str($action)->replace('_', ' ')->title() }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Admin</span>
                <select name="admin" class="h-11 w-full rounded-xl border border-slate-300 bg-white text-sm shadow-sm transition hover:border-slate-400 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
                    <option value="">All admins</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" @selected((string) ($filters['admin'] ?? '') === (string) $admin->id)>{{ $admin->name ?: $admin->email }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">From</span>
                <input name="from" value="{{ $filters['from'] ?? '' }}" type="date" class="h-11 w-full rounded-xl border border-slate-300 bg-white text-sm shadow-sm transition hover:border-slate-400 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">To</span>
                <input name="to" value="{{ $filters['to'] ?? '' }}" type="date" class="h-11 w-full rounded-xl border border-slate-300 bg-white text-sm shadow-sm transition hover:border-slate-400 focus:border-[#8f5d54] focus:ring-[#8f5d54]">
            </label>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <p class="text-sm text-slate-500">Showing {{ number_format($activities->total()) }} matching record{{ $activities->total() === 1 ? '' : 's' }}.</p>
            <div class="flex items-center gap-2">
                @if($hasFilters)
                    <a href="{{ route('admin.settings.activity-log') }}" class="inline-flex h-10 items-center rounded-xl px-4 text-sm font-semibold text-slate-600 hover:bg-slate-100">Clear</a>
                @endif
                <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-xl bg-[#8f5d54] px-5 text-sm font-semibold text-white transition hover:bg-[#7d5048]">
                    <i class="bi bi-funnel"></i>
                    Apply filters
                </button>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="divide-y divide-slate-100">
            @forelse($activities as $activity)
                @php
                    $action = strtolower((string) $activity->action);
                    $tone = str_contains($action, 'delete') || str_contains($action, 'cancel')
                        ? 'bg-rose-50 text-rose-700 ring-rose-200'
                        : (str_contains($action, 'create') || str_contains($action, 'store')
                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                            : 'bg-blue-50 text-blue-700 ring-blue-200');
                    $icon = str_contains($action, 'delete') || str_contains($action, 'cancel')
                        ? 'bi-trash3'
                        : (str_contains($action, 'create') || str_contains($action, 'store') ? 'bi-plus-circle' : 'bi-pencil-square');
                @endphp
                <article class="p-4 transition hover:bg-slate-50/70 sm:p-5">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 {{ $tone }}">
                            <i class="bi {{ $icon }}"></i>
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $activity->description ?: str($activity->action)->replace('_', ' ')->title() }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="font-medium text-slate-700">{{ $activity->user?->name ?: $activity->user?->email ?: 'Unknown admin' }}</span>
                                        <span>{{ str($activity->action)->replace('_', ' ')->title() }}</span>
                                        @if($activity->model_type)
                                            <span>{{ class_basename($activity->model_type) }}{{ $activity->model_id ? ' #' . $activity->model_id : '' }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="shrink-0 text-left lg:text-right">
                                    <p class="text-xs font-medium text-slate-700">{{ $activity->created_at->format('M d, Y · h:i A') }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                @if($activity->ip_address)
                                    <span class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-2.5 py-1 text-xs text-slate-600" x-data="{ copied: false }">
                                        <i class="bi bi-globe2"></i>
                                        <span class="font-mono">{{ $activity->ip_address }}</span>
                                        <button type="button" class="font-semibold text-[#8f5d54] hover:underline" @click="navigator.clipboard.writeText(@js($activity->ip_address)); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Copied' : 'Copy'"></button>
                                    </span>
                                @endif

                                @if(!empty($activity->changes))
                                    <details class="group w-full rounded-xl border border-slate-200 bg-slate-50/70 lg:w-auto lg:min-w-96">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2 text-xs font-semibold text-slate-700">
                                            <span><i class="bi bi-list-check mr-1.5"></i>View recorded details</span>
                                            <i class="bi bi-chevron-down transition group-open:rotate-180"></i>
                                        </summary>
                                        <dl class="grid gap-2 border-t border-slate-200 px-3 py-3 text-xs sm:grid-cols-2">
                                            @foreach($activity->changes as $key => $value)
                                                <div class="min-w-0">
                                                    <dt class="font-semibold text-slate-500">{{ str($key)->replace('_', ' ')->title() }}</dt>
                                                    <dd class="mt-0.5 warap-break-words text-slate-800">{{ $formatValue($value) }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </details>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-6 py-16 text-center">
                    <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400"><i class="bi bi-clock-history"></i></span>
                    <h2 class="mt-4 font-semibold text-slate-900">No activity found</h2>
                    <p class="mt-1 text-sm text-slate-500">Try clearing the filters or make a new admin change.</p>
                </div>
            @endforelse
        </div>

        @if($activities->hasPages())
            <div class="border-t border-slate-100 px-4 py-4 sm:px-5">
                {{ $activities->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>

@if(session('success'))
    <div data-admin-flash data-admin-flash-library="toastr" data-admin-flash-title="Success" data-admin-flash-type="success" data-admin-flash-message="{{ e(session('success')) }}"></div>
@endif

@if(session('error'))
    <div data-admin-flash data-admin-flash-library="toastr" data-admin-flash-title="Error" data-admin-flash-type="error" data-admin-flash-message="{{ e(session('error')) }}"></div>
@endif
@endsection
