<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Camp Waiver PDF</title>
  <style>
    @page {
      size: legal;
      margin: 0.5in;
    }
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 11px;
      line-height: 1.5;
      color: #1f2937;
      margin: 0;
      padding: 0;
    }
    .header {
      border-bottom: 2px solid #8f5d54;
      margin-bottom: 16px;
      padding-bottom: 12px;
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
      font-size: 24px;
      color: #111827;
    }
    .meta {
      margin-top: 8px;
      font-size: 10px;
      color: #4b5563;
    }
    .grid {
      width: 100%;
      border-collapse: collapse;
      margin-top: 14px;
    }
    .grid td {
      vertical-align: top;
      width: 50%;
      padding: 0 6px 10px 0;
    }
    .card {
      border: 1px solid #d1d5db;
      border-radius: 10px;
      padding: 12px 14px;
      background: #f9fafb;
    }
    .card h2,
    .section h2 {
      margin: 0 0 8px;
      font-size: 14px;
      color: #111827;
    }
    .label {
      color: #6b7280;
      font-weight: bold;
    }
    .row {
      margin-bottom: 6px;
    }
    .waiver-body {
      white-space: pre-line;
      margin-top: 10px;
      font-size: 10.5px;
    }
    .section {
      border: 1px solid #d1d5db;
      border-radius: 10px;
      padding: 12px 14px;
      margin-top: 12px;
    }
    .signature-grid {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
    }
    .signature-grid td {
      width: 50%;
      vertical-align: top;
      padding-right: 10px;
    }
    .signature-line {
      margin-top: 34px;
      border-top: 1px dashed #6b7280;
      padding-top: 8px;
      font-size: 10px;
    }
    .footer-note {
      margin-top: 12px;
      font-size: 9.5px;
      color: #6b7280;
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

  <table class="grid">
    <tr>
      <td>
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
      </td>
      <td>
        <div class="card">
          <h2>Guest Contact</h2>
          <div class="row"><span class="label">Name:</span> {{ trim(($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '')) ?: ($booking->customer_name ?? 'N/A') }}</div>
          <div class="row"><span class="label">Email:</span> {{ $customerInfo['email'] ?? ($booking->customer_email ?? 'N/A') }}</div>
          <div class="row"><span class="label">Phone:</span> {{ $customerInfo['phone'] ?? ($booking->customer_phone ?? 'N/A') }}</div>
        </div>
      </td>
    </tr>
  </table>

  <div class="section">
    <h2>Waiver Text</h2>
    <div class="waiver-body">{{ $waiverBody }}</div>
  </div>

  <table class="signature-grid">
    <tr>
      <td>
        <div class="section">
          <h2>Guest Signature</h2>
          <div class="signature-line">
            {{ $campWaiverAcceptance['details']['signature_name'] ?? trim(($customerInfo['first_name'] ?? '') . ' ' . ($customerInfo['last_name'] ?? '')) }}
            <div>Signature over printed name</div>
          </div>
          <div class="row"><span class="label">Date:</span> {{ $campWaiverAcceptance['details']['signature_date'] ?? $acceptedAt ?? 'N/A' }}</div>
        </div>
      </td>
      <td>
        <div class="section">
          <h2>Parent / Guardian</h2>
          <div class="signature-line">
            {{ $campWaiverAcceptance['details']['parent_signature_name'] ?? $campWaiverAcceptance['details']['parent_guardian_name'] ?? 'N/A' }}
            <div>Name and signature, if applicable</div>
          </div>
          <div class="row"><span class="label">Child / Ward:</span> {{ $campWaiverAcceptance['details']['child_ward_name'] ?? 'N/A' }}</div>
        </div>
      </td>
    </tr>
  </table>

  <div class="footer-note">
    Generated from the confirmed booking record. Use your browser or PDF viewer to print if needed.
  </div>
</body>
</html>
