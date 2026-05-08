{{-- resources/views/components/vat-label.blade.php --}}
@php
$vatService = app(\App\Services\VatService::class);
$vatPercentage = $vatService->getCurrentVatRatePercentage();
@endphp

<span class="text-sm text-gray-600">
    Includes {{ $vatPercentage }}% VAT:
</span></content>