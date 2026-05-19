@extends('layouts.admin')

@section('title', 'PayMongo Webhook Logs')

@section('content')
<div class="space-y-6">
    @if(isset($tableReady) && !$tableReady)
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
            PayMongo webhook log table is not yet migrated. Run the migration to enable event storage.
        </div>
    @endif

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-gray-500">Payments · Debug</p>
            <h1 class="text-3xl font-semibold text-gray-900">PayMongo Webhook Logs</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <span>Back to Bookings</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="p-4 rounded-xl shadow-sm bg-white border border-gray-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-emerald-200">
            <p class="text-xs font-semibold uppercase text-gray-500">HTTP 200</p>
            <p class="text-2xl font-bold text-emerald-700">{{ number_format($stats['ok']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-rose-200">
            <p class="text-xs font-semibold uppercase text-gray-500">HTTP Errors</p>
            <p class="text-2xl font-bold text-rose-700">{{ number_format($stats['failed']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-blue-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Handled</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($stats['handled']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-amber-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Invalid Signature</p>
            <p class="text-2xl font-bold text-amber-700">{{ number_format($stats['invalid_signature']) }}</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('admin.bookings.paymongo-webhooks') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Event Type</label>
                <input type="text" name="event_type" value="{{ request('event_type') }}" placeholder="payment.paid"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Reference</label>
                <input type="text" name="reference" value="{{ request('reference') }}" placeholder="TXN-..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Signature</label>
                <select name="signature_valid" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="1" {{ request('signature_valid') === '1' ? 'selected' : '' }}>Valid</option>
                    <option value="0" {{ request('signature_valid') === '0' ? 'selected' : '' }}>Invalid</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Handled</label>
                <select name="handled" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="1" {{ request('handled') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('handled') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="md:col-span-6 flex items-center gap-2">
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('admin.bookings.paymongo-webhooks') }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Received</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Signature</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Handled</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">HTTP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($events as $event)
                        <tr class="align-top">
                            <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap">{{ $event->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <div class="font-semibold">{{ $event->event_type ?? 'N/A' }}</div>
                                <div class="text-gray-500">{{ $event->resource_type ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <div>{{ $event->reference ?? '-' }}</div>
                                @if($event->checkout_session_id)
                                    <div class="text-gray-500">Checkout: {{ $event->checkout_session_id }}</div>
                                @endif
                                @if($event->payment_id)
                                    <div class="text-gray-500">Payment: {{ $event->payment_id }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap">
                                @if(!is_null($event->amount_centavos))
                                    ₱{{ number_format($event->amount_centavos / 100, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if(!$event->signature_present)
                                    <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold">Missing</span>
                                @elseif($event->signature_valid)
                                    <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">Valid</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-rose-100 text-rose-700 font-semibold">Invalid</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($event->handled)
                                    <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold">Yes</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-1 rounded-full {{ $event->http_status === 200 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} font-semibold">
                                    {{ $event->http_status }}
                                </span>
                                @if($event->error_message)
                                    <div class="text-rose-700 mt-1">{{ $event->error_message }}</div>
                                @elseif($event->message)
                                    <div class="text-gray-500 mt-1">{{ $event->message }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <details>
                                    <summary class="cursor-pointer text-blue-600 hover:text-blue-800">View payload</summary>
                                    <pre class="mt-2 max-h-56 overflow-auto bg-gray-50 p-2 rounded border border-gray-200 text-[11px] leading-5">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">No webhook logs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $events->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection
