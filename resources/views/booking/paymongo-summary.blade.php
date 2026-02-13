{{-- resources/views/booking/paymongo-summary.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CalaiaPH - Payment Summary</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>

<body class="bg-[#f5efe7]">
    <x-header active="booking" />

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl">
            {{-- Header --}}
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold mb-2" style="font-family: 'Gotham Book', sans-serif;">Payment for</h1>
                <h2 class="text-2xl text-[#8f5d54] font-semibold mb-6" style="font-family: 'Gotham Book', sans-serif;">Staycation Booking Payment</h2>
                <div class="border-b-2 border-gray-900 w-32 mx-auto"></div>
            </div>

            {{-- Payment Summary Card --}}
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                {{-- Items Table --}}
                <div class="overflow-x-auto mb-8">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b-2 border-gray-300">
                                <th class="text-left text-sm font-bold text-gray-800 pb-4">ITEM NAME</th>
                                <th class="text-right text-sm font-bold text-gray-800 pb-4">QTY</th>
                                <th class="text-right text-sm font-bold text-gray-800 pb-4">PRICE</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Accommodations --}}
                            @foreach($lineItems as $item)
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="text-sm text-gray-700 py-4 font-medium">{{ $item['name'] }}</td>
                                    <td class="text-sm text-gray-600 py-4 text-right">{{ $item['quantity'] }}</td>
                                    <td class="text-sm text-gray-700 py-4 text-right font-semibold">
                                        ₱{{ number_format($item['amount'] / 100, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Total Section --}}
                <div class="flex justify-end mb-8">
                    <div class="w-full max-w-xs">
                        <div class="flex justify-between items-center py-3 border-t-2 border-gray-300">
                            <span class="font-bold text-lg text-gray-900">TOTAL:</span>
                            <span class="font-bold text-lg text-[#8f5d54]">₱{{ number_format($grandTotal, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Method Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-shield-check text-blue-600 text-lg mt-1"></i>
                        <div>
                            <p class="text-sm font-semibold text-gray-800 mb-1">Secure Payment via PayMongo</p>
                            <p class="text-xs text-gray-600">You'll be redirected to PayMongo to complete your payment securely using card, GCash, PayMaya, or QR PH.</p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-4">
                    <a href="{{ route('booking.staycationPayment') }}" 
                       class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <i class="bi bi-arrow-left mr-2"></i>
                        Back to Payment
                    </a>
                    <form action="{{ route('booking.processPayment') }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-[#8f5d54] text-white font-semibold hover:bg-[#a0675a] transition shadow-lg">
                            <i class="bi bi-lock-fill"></i>
                            Proceed to PayMongo
                            <span class="text-right">₱{{ number_format($grandTotal, 2) }}</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Trust Badges --}}
            <div class="grid grid-cols-3 gap-4 max-w-md mx-auto">
                <div class="text-center">
                    <i class="fas fa-lock text-green-600 text-2xl mb-2"></i>
                    <p class="text-xs text-gray-600 font-semibold">SSL Secured</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl mb-2"></i>
                    <p class="text-xs text-gray-600 font-semibold">Protected</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-credit-card text-purple-600 text-2xl mb-2"></i>
                    <p class="text-xs text-gray-600 font-semibold">Encrypted</p>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
</body>
</html>
