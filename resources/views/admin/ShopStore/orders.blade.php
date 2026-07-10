@extends('layouts.admin')

@section('title', 'Shop Orders')

@section('content')
@php
    $adminPrimaryButton = 'inline-flex items-center justify-center gap-2 rounded-xl bg-[#8f5d54] px-4 py-2 text-sm font-medium text-white shadow transition hover:bg-[#7a4d46] hover:shadow-lg';
@endphp
<div class="space-y-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-gray-500">Shop · Orders</p>
            <h1 class="text-3xl font-semibold text-gray-900">Shop Orders</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.bookings.paymongo-webhooks') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg {{ $adminPrimaryButton }}">
                <span>PayMongo Logs</span>
            </a>
            <a href="{{ route('admin.shop.shopitems') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                <span>Back to Shop Items</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="p-4 rounded-xl shadow-sm bg-white border border-gray-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-amber-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-amber-700">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-emerald-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Paid</p>
            <p class="text-2xl font-bold text-emerald-700">{{ number_format($stats['paid']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-rose-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Failed</p>
            <p class="text-2xl font-bold text-rose-700">{{ number_format($stats['failed']) }}</p>
        </div>
        <div class="p-4 rounded-xl shadow-sm bg-white border border-blue-200">
            <p class="text-xs font-semibold uppercase text-gray-500">Paid Revenue</p>
            <p class="text-2xl font-bold text-blue-700">PHP {{ number_format($stats['paid_revenue'], 2) }}</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('admin.shop.orders') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Order ref, customer, checkout id"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Status</label>
                <select name="payment_status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Order Status</label>
                <select name="order_status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="pending" {{ request('order_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('order_status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="ready" {{ request('order_status') === 'ready' ? 'selected' : '' }}>Ready</option>
                    <option value="completed" {{ request('order_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('order_status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                <button type="submit" class="px-3 py-2 {{ $adminPrimaryButton }}">Filter</button>
                <a href="{{ route('admin.shop.orders') }}" class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Created</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Order</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fulfillment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Payment</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="align-top">
                            <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap">
                                {{ $order->created_at?->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <div class="font-semibold">{{ $order->order_reference }}</div>
                                <div class="text-gray-500">Txn: {{ $order->transaction_id ?? '-' }}</div>
                                <div class="text-gray-500">Checkout: {{ $order->paymongo_checkout_id ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <div class="font-semibold">{{ $order->customer_name }}</div>
                                <div class="text-gray-500">{{ $order->customer_email }}</div>
                                <div class="text-gray-500">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap">
                                {{ number_format($order->item_count) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap">
                                <div class="font-semibold text-gray-900">PHP {{ number_format((float) $order->final_total, 2) }}</div>
                                <div class="text-gray-500">VAT: PHP {{ number_format((float) $order->vat_amount, 2) }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                <div class="capitalize">{{ $order->delivery_option }}</div>
                                <div class="text-gray-500">Date: {{ optional($order->pickup_date)->format('Y-m-d') }}</div>
                                @if(!empty($order->delivery_address))
                                    <details>
                                        <summary class="cursor-pointer text-blue-600 hover:text-blue-800 mt-1">Address</summary>
                                        <div class="mt-1 text-gray-600 break-words max-w-xs">{{ $order->delivery_address }}</div>
                                    </details>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700">
                                @php
                                    $paymentBadge = match($order->payment_status) {
                                        'paid' => 'bg-emerald-100 text-emerald-700',
                                        'failed' => 'bg-rose-100 text-rose-700',
                                        'refunded' => 'bg-slate-100 text-slate-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };

                                    $orderBadge = match($order->order_status) {
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700',
                                        'ready' => 'bg-blue-100 text-blue-700',
                                        'processing' => 'bg-indigo-100 text-indigo-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <div>
                                    <span class="px-2 py-1 rounded-full {{ $paymentBadge }} font-semibold">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <span class="px-2 py-1 rounded-full {{ $orderBadge }} font-semibold">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </div>
                                @if($order->paid_at)
                                    <div class="text-gray-500 mt-2">Paid: {{ $order->paid_at->format('Y-m-d H:i') }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">No shop orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection
