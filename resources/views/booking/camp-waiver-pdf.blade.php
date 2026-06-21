<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Camp Waiver PDF</title>
  <style>
    @page {
      size: legal;
      margin: 0.42in;
    }
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 10.5px;
      line-height: 1.42;
      color: #1f2937;
      margin: 0;
      padding: 0;
    }
    .header {
      border-bottom: 2px solid #8f5d54;
      margin-bottom: 12px;
      padding-bottom: 10px;
      break-inside: avoid;
      page-break-inside: avoid;
    }
    .eyebrow {
      text-transform: uppercase;
      letter-spacing: 0.18em;
      font-size: 9px;
      color: #8f5d54;
      font-weight: bold;
      margin: 0 0 6px;
    }
    h1 {
      margin: 0;
      font-size: 21px;
      color: #111827;
    }
    .meta {
      margin-top: 6px;
      font-size: 9.5px;
      color: #4b5563;
    }
    .two-up {
      width: 100%;
      margin-top: 10px;
    }
    .two-up::after {
      content: "";
      display: block;
      clear: both;
    }
    .col {
      width: 49%;
      box-sizing: border-box;
    }
    .col-left {
      float: left;
      padding-right: 5px;
    }
    .col-right {
      float: right;
      padding-left: 5px;
    }
    .card {
      border: 1px solid #d1d5db;
      border-radius: 10px;
      padding: 10px 12px;
      background: #f9fafb;
      break-inside: avoid;
      page-break-inside: avoid;
    }
    .card h2,
    .section h2 {
      margin: 0 0 6px;
      font-size: 12.5px;
      color: #111827;
    }
    .label {
      color: #6b7280;
      font-weight: bold;
    }
    .row {
      margin-bottom: 4px;
    }
    .waiver-body {
      white-space: pre-wrap;
      margin-top: 8px;
      font-size: 9.75px;
      line-height: 1.36;
      word-break: break-word;
      overflow-wrap: anywhere;
      break-inside: auto;
      page-break-inside: auto;
    }
    .section {
      border: 1px solid #d1d5db;
      border-radius: 10px;
      padding: 10px 12px;
      margin-top: 10px;
      break-inside: avoid;
      page-break-inside: avoid;
    }
    .waiver-section {
      break-inside: auto;
      page-break-inside: auto;
    }
    .signature-grid {
      margin-top: 12px;
    }
    .signature-line {
      margin-top: 28px;
      border-top: 1px dashed #6b7280;
      padding-top: 7px;
      font-size: 9.5px;
    }
    .footer-note {
      margin-top: 10px;
      font-size: 9px;
      color: #6b7280;
    }
    .page-break {
      page-break-before: always;
      break-before: page;
    }
  </style>
</head>
@php
  $booking = $booking ?? null;
  $bookingData = $bookingData ?? [];
  $customerInfo = $customerInfo ?? [];
  $campWaiverAcceptance = $campWaiverAcceptance ?? [];
  $waiverContent = $waiverContent ?? [];
  $waiverTitle = trim((string) ($waiverContent['title'] ?? '')) !== '' ? $waiverContent['title'] : 'Camp Waiver and Release from Liability';
  $waiverBody = trim((string) ($waiverContent['body'] ?? '')) !== '' ? $waiverContent['body'] : app(\App\Services\BookingConfirmationContentService::class)->defaultCampWaiverBody();
  $waiverParts = preg_split('/(?=^\s*IV\.\s*MEDICAL AUTHORIZATION\b)/mi', $waiverBody, 2) ?: [$waiverBody];
  $waiverIntro = trim((string) ($waiverParts[0] ?? ''));
  $waiverMedical = trim((string) ($waiverParts[1] ?? ''));
  $acceptedAt = !empty($campWaiverAcceptance['accepted_at']) ? \Carbon\Carbon::parse($campWaiverAcceptance['accepted_at'])->format('M d, Y h:i A') : null;
  $bookingReference = $bookingData['transaction_id'] ?? ($booking->transaction_id ?? '');
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
<body>
  <div class="header">
    <p class="eyebrow">CalaiaPH Camp Waiver</p>
    <h1>{{ $waiverTitle }}</h1>
    <div class="meta">
      <div><span class="label">Reference:</span> {{ $bookingReference }}</div>
      @if($acceptedAt)
        <div><span class="label">Accepted:</span> {{ $acceptedAt }}</div>
      @endif
      <div><span class="label">Status:</span> Confirmed booking waiver copy</div>
    </div>
  </div>

  <div class="two-up">
    <div class="col col-left">
      <div class="card">
        <h2>Participant Info</h2>
        @forelse($printableDetails as $field => $value)
          @if($value !== null && $value !== '')
            <div class="row">
              <span class="label">{{ $detailLabels[$field] ?? str_replace('_', ' ', ucfirst($field)) }}:</span>
              {{ $value }}
            </div>
          @endif
        @empty
          <div class="row">No waiver details were captured.</div>
        @endforelse
      </div>
    </div>
    <div class="col col-right">
      <div class="card">
        <h2>Guest Contact</h2>
        <div class="row"><span class="label">Name:</span> {{ trim(($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '')) ?: ($booking->customer_name ?? 'N/A') }}</div>
        <div class="row"><span class="label">Email:</span> {{ $customerInfo['email'] ?? ($booking->customer_email ?? 'N/A') }}</div>
        <div class="row"><span class="label">Phone:</span> {{ $customerInfo['phone'] ?? ($booking->customer_phone ?? 'N/A') }}</div>
      </div>
    </div>
  </div>

  <div class="section waiver-section">
    <h2>Waiver Text</h2>
    @if($waiverIntro !== '')
      <div class="waiver-body">{{ $waiverIntro }}</div>
    @endif
  </div>

  @if($waiverMedical !== '')
    <div class="page-break"></div>
    <div class="section waiver-section" style="margin-top: 0;">
      <div class="waiver-body">{{ $waiverMedical }}</div>
    </div>
  @endif

  <div class="two-up signature-grid">
    <div class="col col-left">
      <div class="section">
        <h2>Guest Signature</h2>
        <div class="signature-line">
          {{ $campWaiverAcceptance['details']['signature_name'] ?? trim(($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '')) }}
          <div>Signature over printed name</div>
        </div>
        <div class="row"><span class="label">Date:</span> {{ $campWaiverAcceptance['details']['signature_date'] ?? $acceptedAt ?? 'N/A' }}</div>
      </div>
    </div>
    <div class="col col-right">
      <div class="section">
        <h2>Parent / Guardian</h2>
        <div class="signature-line">
          {{ $campWaiverAcceptance['details']['parent_signature_name'] ?? $campWaiverAcceptance['details']['parent_guardian_name'] ?? 'N/A' }}
          <div>Name and signature, if applicable</div>
        </div>
        <div class="row"><span class="label">Child / Ward:</span> {{ $campWaiverAcceptance['details']['child_ward_name'] ?? 'N/A' }}</div>
      </div>
    </div>
  </div>

  <div class="footer-note">
    Generated from the confirmed booking record. Use your browser or PDF viewer to print if needed.
  </div>
</body>
</html>
