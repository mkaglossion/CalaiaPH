<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Camp Waiver - CalaiaPH</title>
  @vite(['resources/js/app.js'])
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
@php
  $bookingData = $bookingData ?? [];
  $customerInfo = $customerInfo ?? [];
  $waiverContent = $waiverContent ?? [];
  $campWaiverAcceptance = $campWaiverAcceptance ?? [];
  $waiverTitle = trim((string) ($waiverContent['title'] ?? '')) !== '' ? $waiverContent['title'] : 'Camp Waiver and Release from Liability';
  $waiverBody = trim((string) ($waiverContent['body'] ?? '')) !== '' ? $waiverContent['body'] : app(\App\Services\BookingConfirmationContentService::class)->defaultCampWaiverBody();
  $acceptedAt = !empty($campWaiverAcceptance['accepted_at']) ? \Carbon\Carbon::parse($campWaiverAcceptance['accepted_at'])->format('M d, Y h:i A') : null;
  $bookingReference = $bookingData['transaction_id'] ?? (isset($booking) ? $booking->transaction_id : '');
  $campWaiverPdfUrl = !empty($bookingReference) ? route('booking.camp-waiver.pdf', ['transactionId' => $bookingReference]) : null;
  $detailLabels = [
      'participant_name' => 'Participant / Guest Name',
      'birth_date' => 'Date of Birth',
      'gender' => 'Gender',
      'address' => 'Address',
      'parent_guardian_name' => 'Parent / Guardian Name',
      'emergency_contact_name' => 'Emergency Contact Name',
      'emergency_contact_phone' => 'Emergency Contact Number',
      'relationship_to_participant' => 'Relationship to Participant',
      'signature_name' => 'Typed Name / Signature',
      'signature_date' => 'Date',
      'parent_signature_name' => 'Parent / Guardian Signature',
      'child_ward_name' => 'Child / Ward Name',
      'parent_signature_date' => 'Parent / Guardian Date',
      'medical_condition' => 'Medical or Physical Condition',
  ];
  $printableDetails = is_array($campWaiverAcceptance['details'] ?? null) ? $campWaiverAcceptance['details'] : [];
@endphp
<body class="bg-neutral-100 text-slate-900">
  <main class="min-h-screen py-6 sm:py-10 print:py-0">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
      <div class="mb-4 flex flex-col gap-3 rounded-2xl bg-white/90 px-4 py-4 shadow-sm ring-1 ring-slate-200 sm:flex-row sm:items-center sm:justify-between print:hidden">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#8f5d54]">CalaiaPH Camp Waiver</p>
          <h1 class="text-xl font-bold text-slate-900">Print-friendly waiver copy</h1>
          <p class="text-sm text-slate-600">Use the print button or the browser print dialog to save this waiver as PDF.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button type="button" onclick="window.print()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#8f5d54] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#a0675a] transition-colors">
            <i class="bi bi-printer"></i>
            Print Camp Waiver
          </button>
          @if(!empty($campWaiverPdfUrl))
            <a href="{{ $campWaiverPdfUrl }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#8f5d54] bg-white px-4 py-2.5 text-sm font-semibold text-[#8f5d54] hover:bg-[#f7f2f0] transition-colors">
              <i class="bi bi-filetype-pdf"></i>
              Download PDF
            </a>
          @endif
          <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
            <i class="bi bi-house"></i>
            Home
          </a>
        </div>
      </div>

      <section class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200 sm:p-8">
        <div class="border-b border-slate-200 pb-5">
          <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Booking Reference</p>
          <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-3xl font-bold text-[#8f5d54]">{{ $waiverTitle }}</h2>
              <p class="mt-2 text-sm text-slate-600">This copy reflects the waiver accepted before PayMongo payment confirmation.</p>
            </div>
            <div class="rounded-2xl bg-[#f7f2f0] px-4 py-3 text-sm text-slate-700">
              <div><span class="font-semibold">Reference:</span> {{ $bookingReference }}</div>
              <div><span class="font-semibold">Status:</span> Confirmed</div>
              @if($acceptedAt)
                <div><span class="font-semibold">Accepted:</span> {{ $acceptedAt }}</div>
              @endif
            </div>
          </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Participant Info</h3>
            <dl class="mt-3 space-y-2 text-sm">
              @foreach($printableDetails as $field => $value)
                @if($value !== null && $value !== '')
                  <div class="grid grid-cols-1 gap-1 sm:grid-cols-3 sm:gap-3">
                    <dt class="font-medium text-slate-500">{{ $detailLabels[$field] ?? str_replace('_', ' ', ucfirst($field)) }}</dt>
                    <dd class="sm:col-span-2 text-slate-900">{{ $value }}</dd>
                  </div>
                @endif
              @endforeach
              @if(empty($printableDetails))
                <p class="text-slate-500">No waiver details were captured.</p>
              @endif
            </dl>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Guest Contact</h3>
            <dl class="mt-3 space-y-2 text-sm">
              <div class="grid grid-cols-1 gap-1 sm:grid-cols-3 sm:gap-3">
                <dt class="font-medium text-slate-500">Name</dt>
                <dd class="sm:col-span-2 text-slate-900">{{ trim(($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '')) ?: (isset($booking) ? $booking->customer_name : 'N/A') }}</dd>
              </div>
              <div class="grid grid-cols-1 gap-1 sm:grid-cols-3 sm:gap-3">
                <dt class="font-medium text-slate-500">Email</dt>
                <dd class="sm:col-span-2 text-slate-900">{{ $customerInfo['email'] ?? (isset($booking) ? $booking->customer_email : 'N/A') }}</dd>
              </div>
              <div class="grid grid-cols-1 gap-1 sm:grid-cols-3 sm:gap-3">
                <dt class="font-medium text-slate-500">Phone</dt>
                <dd class="sm:col-span-2 text-slate-900">{{ $customerInfo['phone'] ?? (isset($booking) && !empty($booking->customer_phone) ? $booking->customer_phone : 'N/A') }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">
          <h3 class="text-lg font-semibold text-slate-900">Waiver Text</h3>
          <div class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">
            {{ $waiverBody }}
          </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm font-semibold text-slate-900">Guest Signature</p>
            <div class="mt-8 border-t border-dashed border-slate-400 pt-3 text-sm text-slate-600">
              <p class="font-medium text-slate-900">{{ $campWaiverAcceptance['details']['signature_name'] ?? ($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '') }}</p>
              <p>Signature over printed name</p>
            </div>
            <div class="mt-5 text-sm text-slate-600">
              <span class="font-medium text-slate-900">Date:</span>
              {{ $campWaiverAcceptance['details']['signature_date'] ?? $acceptedAt ?? 'N/A' }}
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm font-semibold text-slate-900">Parent / Guardian</p>
            <div class="mt-8 border-t border-dashed border-slate-400 pt-3 text-sm text-slate-600">
              <p class="font-medium text-slate-900">{{ $campWaiverAcceptance['details']['parent_signature_name'] ?? $campWaiverAcceptance['details']['parent_guardian_name'] ?? 'N/A' }}</p>
              <p>Name and signature, if applicable</p>
            </div>
            <div class="mt-5 text-sm text-slate-600">
              <span class="font-medium text-slate-900">Child / Ward:</span>
              {{ $campWaiverAcceptance['details']['child_ward_name'] ?? 'N/A' }}
            </div>
          </div>
        </div>

        <div class="mt-6 rounded-2xl bg-[#f7f2f0] p-4 text-sm text-slate-700">
          <p class="font-semibold text-[#8f5d54]">Reminder</p>
          <p class="mt-1">This page is intended for printing or saving as PDF using your browser dialog.</p>
        </div>
      </section>
    </div>
  </main>

  <style>
    @page {
      size: legal;
      margin: 0.5in;
    }

    @media print {
      body {
        background: white !important;
      }

      .print\:hidden {
        display: none !important;
      }

      main {
        padding: 0 !important;
      }

      section {
        box-shadow: none !important;
        border: 0 !important;
      }
    }
  </style>
</body>
</html>
