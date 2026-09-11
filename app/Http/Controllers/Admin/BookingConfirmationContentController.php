<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingConfirmationContent;
use App\Models\Booking;
use App\Models\Package;
use App\Models\PackageBooking;
use App\Services\BookingConfirmationContentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingConfirmationContentController extends Controller
{
    public function index(BookingConfirmationContentService $service)
    {
        $contents = $service->all();

        return view('admin.bookingConfirmation.index', [
            'contents' => $contents,
            'types' => [
                'staycation' => 'Staycation',
                'package' => 'Package',
                'dayvisit' => 'Day Visit',
            ],
        ]);
    }

    public function preview(Request $request, BookingConfirmationContentService $service): View
    {
        $rawTab = (string) $request->query('tab', 'staycation');
        $tab = BookingConfirmationContent::normalizeType($rawTab);

        return match ($tab) {
            'package' => $this->previewPackage($service),
            'campwaiver' => $this->previewCampWaiver($service),
            default => $this->previewStaycation($service),
        };
    }

    public function update(Request $request, string $bookingType, BookingConfirmationContentService $service)
    {
        $rawBookingType = $bookingType;
        $bookingType = BookingConfirmationContent::normalizeType($bookingType);

        $payload = data_get($request->input('contents', []), $rawBookingType, []);
        $isCampWaiver = $bookingType === 'campwaiver';

        $rules = $isCampWaiver ? [
            "contents.$rawBookingType.camp_waiver_title" => ['required', 'string', 'max:255'],
            "contents.$rawBookingType.camp_waiver_body" => ['required', 'string', 'max:20000'],
            "contents.$rawBookingType.camp_waiver_form_url" => ['nullable', 'url', 'max:2048'],
        ] : [
            "contents.$rawBookingType.page_eyebrow" => ['nullable', 'string', 'max:120'],
            "contents.$rawBookingType.page_title" => ['required', 'string', 'max:255'],
            "contents.$rawBookingType.page_description" => ['nullable', 'string', 'max:2000'],
            "contents.$rawBookingType.next_steps_title" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.next_steps" => ['nullable', 'string', 'max:4000'],
            "contents.$rawBookingType.support_title" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.support_description" => ['nullable', 'string', 'max:2000'],
            "contents.$rawBookingType.support_email" => ['nullable', 'email', 'max:255'],
            "contents.$rawBookingType.support_phone" => ['nullable', 'string', 'max:50'],
            "contents.$rawBookingType.home_button_label" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.print_button_label" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.payment_button_label" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.camp_waiver_title" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.camp_waiver_body" => ['nullable', 'string', 'max:20000'],
            "contents.$rawBookingType.camp_waiver_form_url" => ['nullable', 'url', 'max:2048'],
            "contents.$rawBookingType.email_subject" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.email_greeting" => ['nullable', 'string', 'max:255'],
            "contents.$rawBookingType.email_intro" => ['nullable', 'string', 'max:4000'],
            "contents.$rawBookingType.email_closing" => ['nullable', 'string', 'max:4000'],
        ];

        $validated = $request->validate($rules);

        if ($isCampWaiver) {
            BookingConfirmationContent::updateOrCreate(
                ['booking_type' => $bookingType],
                [
                    'page_eyebrow' => 'Booking Confirmation',
                    'page_title' => $payload['camp_waiver_title'] ?? 'Camp Waiver',
                    'page_description' => 'Manage the camp waiver shown to staycation guests before payment.',
                    'next_steps_title' => null,
                    'next_steps' => null,
                    'support_title' => null,
                    'support_description' => null,
                    'support_email' => null,
                    'support_phone' => null,
                    'home_button_label' => null,
                    'print_button_label' => null,
                    'payment_button_label' => null,
                    'camp_waiver_title' => $payload['camp_waiver_title'] ?? '',
                    'camp_waiver_body' => $payload['camp_waiver_body'] ?? '',
                    'camp_waiver_form_url' => $payload['camp_waiver_form_url'] ?? null,
                    'email_subject' => null,
                    'email_greeting' => null,
                    'email_intro' => null,
                    'email_closing' => null,
                ]
            );
        } else {
            BookingConfirmationContent::updateOrCreate(
                ['booking_type' => $bookingType],
                [
                    'page_eyebrow' => $payload['page_eyebrow'] ?? null,
                    'page_title' => $payload['page_title'] ?? '',
                    'page_description' => $payload['page_description'] ?? null,
                    'next_steps_title' => $payload['next_steps_title'] ?? null,
                    'next_steps' => $this->normalizeLines($payload['next_steps'] ?? null),
                    'support_title' => $payload['support_title'] ?? null,
                    'support_description' => $payload['support_description'] ?? null,
                    'support_email' => $payload['support_email'] ?? null,
                    'support_phone' => $payload['support_phone'] ?? null,
                    'home_button_label' => $payload['home_button_label'] ?? null,
                    'print_button_label' => $payload['print_button_label'] ?? null,
                    'payment_button_label' => $payload['payment_button_label'] ?? null,
                    'camp_waiver_title' => $payload['camp_waiver_title'] ?? null,
                    'camp_waiver_body' => $payload['camp_waiver_body'] ?? null,
                    'camp_waiver_form_url' => $payload['camp_waiver_form_url'] ?? null,
                    'email_subject' => $payload['email_subject'] ?? null,
                    'email_greeting' => $payload['email_greeting'] ?? null,
                    'email_intro' => $payload['email_intro'] ?? null,
                    'email_closing' => $payload['email_closing'] ?? null,
                ]
            );
        }

        $service->flushCache();

        return back()->with('success', ucfirst($bookingType) . ' booking confirmation content updated successfully.');
    }

    private function normalizeLines(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));

        return $lines ?: null;
    }

    private function previewStaycation(BookingConfirmationContentService $service): View
    {
        $bookingData = [
            'booking_type' => 'staycation',
            'type' => 'staycation',
            'transaction_id' => 'PREVIEW-STAY-0001',
            'package' => 'Sample Staycation',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '+63 900 000 0000',
            'checkin' => now()->addDays(7)->toDateString(),
            'checkout' => now()->addDays(8)->toDateString(),
            'booking_date' => now()->addDays(7)->toDateString(),
            'total_guests' => 4,
            'total_amount' => 6800,
            'payment_summary' => [
                'grand_total_paid' => 6800,
                'vat_amount' => 816,
                'reference_id' => 'PREVIEW-STAY-0001',
            ],
            'customer_info' => [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'phone' => '+63 900 000 0000',
            ],
            'accommodations' => [],
            'selected_foods' => [],
            'selected_activities' => [],
            'selected_activity_packages' => [],
            'acceptances' => [],
        ];

        return view('booking.confirmation', [
            'bookingData' => $bookingData,
            'customerInfo' => $bookingData['customer_info'],
            'selectedFoods' => [],
            'selectedActivities' => [],
            'selectedActivityPackages' => [],
            'staycations' => collect(),
            'isStaycation' => true,
            'confirmationContent' => $service->forType('staycation', [
                'reference' => $bookingData['transaction_id'],
                'package' => $bookingData['package'],
            ]),
        ]);
    }

    private function previewPackage(BookingConfirmationContentService $service): View
    {
        $package = new Package([
            'name' => 'Sample Package',
            'price' => 2500,
            'sessions' => [
                [
                    'name' => 'morning',
                    'start' => '08:00',
                    'end' => '12:00',
                ],
            ],
        ]);
        $package->exists = true;

        $booking = new PackageBooking([
            'booking_reference' => 'PREVIEW-PKG-0001',
            'public_token' => 'preview-token',
            'booking_date' => now()->addDays(5)->toDateString(),
            'session' => 'morning',
            'total_guests' => 4,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '+63 900 000 0000',
            'special_requests' => 'Sample special request for preview.',
            'total_amount' => 2500,
            'payment_status' => 'pending',
        ]);
        $booking->exists = true;
        $booking->setRelation('package', $package);

        return view('booking.payment-confirmation', [
            'booking' => $booking,
            'confirmationContent' => $service->forType('package', [
                'reference' => $booking->booking_reference,
                'package' => $package->name,
            ]),
        ]);
    }

    private function previewCampWaiver(BookingConfirmationContentService $service): View
    {
        $booking = new Booking([
            'transaction_id' => 'PREVIEW-WAIVER-0001',
            'customer_name' => 'Juan Dela Cruz',
            'customer_email' => 'juan@example.com',
            'customer_phone' => '+63 900 000 0000',
            'booking_type' => 'staycation',
            'total_amount' => 0,
            'payment_status' => 'confirmed',
            'booking_data' => [
                'transaction_id' => 'PREVIEW-WAIVER-0001',
                'customer_info' => [
                    'first_name' => 'Juan',
                    'last_name' => 'Dela Cruz',
                    'email' => 'juan@example.com',
                    'phone' => '+63 900 000 0000',
                ],
                'acceptances' => [[
                    'source' => 'camp_waiver_accepted',
                    'label' => 'Camp Waiver and Release from Liability',
                    'accepted' => true,
                    'accepted_at' => now()->toDateTimeString(),
                    'details' => [
                        'participant_name' => 'Juan Dela Cruz',
                        'signature_name' => 'Juan Dela Cruz',
                        'signature_date' => now()->toDateString(),
                    ],
                ]],
            ],
        ]);
        $booking->exists = true;

        $bookingData = $booking->booking_data ?? [];
        $confirmationContent = $service->forType('campwaiver', [
            'reference' => $booking->transaction_id,
            'package' => 'Staycation',
        ]);

        return view('booking.camp-waiver', [
            'booking' => $booking,
            'bookingData' => $bookingData,
            'customerInfo' => $bookingData['customer_info'] ?? [],
            'campWaiverAcceptance' => collect($bookingData['acceptances'] ?? [])->first(),
            'confirmationContent' => $confirmationContent,
            'waiverContent' => $confirmationContent['camp_waiver'] ?? [],
        ]);
    }

    private function previewStaycationFromPayload(Request $request, string $rawTab, BookingConfirmationContentService $service): View
    {
        $content = $this->previewContentFromPayload('staycation', (array) data_get($request->input('contents', []), $rawTab, []), $service);
        $bookingData = $this->staycationPreviewBookingData();

        return view('booking.confirmation', [
            'bookingData' => $bookingData,
            'customerInfo' => $bookingData['customer_info'],
            'selectedFoods' => [],
            'selectedActivities' => [],
            'selectedActivityPackages' => [],
            'staycations' => collect(),
            'isStaycation' => true,
            'confirmationContent' => $content,
        ]);
    }

    private function previewPackageFromPayload(Request $request, string $rawTab, BookingConfirmationContentService $service): View
    {
        $content = $this->previewContentFromPayload('package', (array) data_get($request->input('contents', []), $rawTab, []), $service);
        $booking = $this->packagePreviewBooking($content);

        return view('booking.payment-confirmation', [
            'booking' => $booking,
            'confirmationContent' => $content,
        ]);
    }

    private function previewCampWaiverFromPayload(Request $request, string $rawTab, BookingConfirmationContentService $service): View
    {
        $content = $this->previewContentFromPayload('campwaiver', (array) data_get($request->input('contents', []), $rawTab, []), $service);
        $booking = $this->campWaiverPreviewBooking($content);
        $bookingData = $booking->booking_data ?? [];

        return view('booking.camp-waiver', [
            'booking' => $booking,
            'bookingData' => $bookingData,
            'customerInfo' => $bookingData['customer_info'] ?? [],
            'campWaiverAcceptance' => collect($bookingData['acceptances'] ?? [])->first(),
            'confirmationContent' => $content,
            'waiverContent' => $content['camp_waiver'] ?? [],
        ]);
    }

    private function previewContentFromPayload(string $type, array $payload, BookingConfirmationContentService $service): array
    {
        $resolved = $service->forType($type, [
            'reference' => 'PREVIEW',
            'package' => $type === 'package' ? 'Sample Package' : 'Sample Staycation',
        ]);

        if ($type === 'campwaiver') {
            $resolved['page']['eyebrow'] = $payload['page_eyebrow'] ?? $resolved['page']['eyebrow'] ?? '';
            $resolved['page']['title'] = $payload['page_title'] ?? $resolved['page']['title'] ?? '';
            $resolved['page']['description'] = $payload['page_description'] ?? $resolved['page']['description'] ?? '';
            $resolved['camp_waiver']['title'] = $payload['camp_waiver_title'] ?? $resolved['camp_waiver']['title'] ?? '';
            $resolved['camp_waiver']['body'] = $payload['camp_waiver_body'] ?? $resolved['camp_waiver']['body'] ?? '';
            $resolved['camp_waiver']['form_url'] = $payload['camp_waiver_form_url'] ?? $resolved['camp_waiver']['form_url'] ?? '';

            return $resolved;
        }

        $resolved['page']['eyebrow'] = $payload['page_eyebrow'] ?? $resolved['page']['eyebrow'] ?? '';
        $resolved['page']['title'] = $payload['page_title'] ?? $resolved['page']['title'] ?? '';
        $resolved['page']['description'] = $payload['page_description'] ?? $resolved['page']['description'] ?? '';
        $resolved['next_steps']['title'] = $payload['next_steps_title'] ?? $resolved['next_steps']['title'] ?? '';
        if (array_key_exists('next_steps', $payload)) {
            $resolved['next_steps']['items'] = $this->normalizeLines($payload['next_steps']) ?? [];
        }
        $resolved['support']['title'] = $payload['support_title'] ?? $resolved['support']['title'] ?? '';
        $resolved['support']['description'] = $payload['support_description'] ?? $resolved['support']['description'] ?? '';
        $resolved['support']['email'] = $payload['support_email'] ?? $resolved['support']['email'] ?? '';
        $resolved['support']['phone'] = $payload['support_phone'] ?? $resolved['support']['phone'] ?? '';
        $resolved['buttons']['home'] = $payload['home_button_label'] ?? $resolved['buttons']['home'] ?? null;
        $resolved['buttons']['print'] = $payload['print_button_label'] ?? $resolved['buttons']['print'] ?? null;
        $resolved['buttons']['payment'] = $payload['payment_button_label'] ?? $resolved['buttons']['payment'] ?? null;
        $resolved['camp_waiver']['title'] = $payload['camp_waiver_title'] ?? $resolved['camp_waiver']['title'] ?? '';
        $resolved['camp_waiver']['body'] = $payload['camp_waiver_body'] ?? $resolved['camp_waiver']['body'] ?? '';
        $resolved['camp_waiver']['form_url'] = $payload['camp_waiver_form_url'] ?? $resolved['camp_waiver']['form_url'] ?? '';
        $resolved['email']['subject'] = $payload['email_subject'] ?? $resolved['email']['subject'] ?? '';
        $resolved['email']['greeting'] = $payload['email_greeting'] ?? $resolved['email']['greeting'] ?? '';
        $resolved['email']['intro'] = $payload['email_intro'] ?? $resolved['email']['intro'] ?? '';
        $resolved['email']['closing'] = $payload['email_closing'] ?? $resolved['email']['closing'] ?? '';

        return $resolved;
    }

    private function staycationPreviewBookingData(): array
    {
        return [
            'booking_type' => 'staycation',
            'type' => 'staycation',
            'transaction_id' => 'PREVIEW-STAY-0001',
            'package' => 'Sample Staycation',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '+63 900 000 0000',
            'checkin' => now()->addDays(7)->toDateString(),
            'checkout' => now()->addDays(8)->toDateString(),
            'booking_date' => now()->addDays(7)->toDateString(),
            'total_guests' => 4,
            'total_amount' => 6800,
            'payment_summary' => [
                'grand_total_paid' => 6800,
                'vat_amount' => 816,
                'reference_id' => 'PREVIEW-STAY-0001',
            ],
            'customer_info' => [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juan@example.com',
                'phone' => '+63 900 000 0000',
            ],
            'accommodations' => [],
            'selected_foods' => [],
            'selected_activities' => [],
            'selected_activity_packages' => [],
            'acceptances' => [],
        ];
    }

    private function packagePreviewBooking(array $confirmationContent): PackageBooking
    {
        $package = new Package([
            'name' => 'Sample Package',
            'price' => 2500,
            'sessions' => [
                [
                    'name' => 'morning',
                    'start' => '08:00',
                    'end' => '12:00',
                ],
            ],
        ]);
        $package->exists = true;

        $booking = new PackageBooking([
            'booking_reference' => 'PREVIEW-PKG-0001',
            'public_token' => 'preview-token',
            'booking_date' => now()->addDays(5)->toDateString(),
            'session' => 'morning',
            'total_guests' => 4,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '+63 900 000 0000',
            'special_requests' => 'Sample special request for preview.',
            'total_amount' => 2500,
            'payment_status' => 'pending',
        ]);
        $booking->exists = true;
        $booking->setRelation('package', $package);

        return $booking;
    }

    private function campWaiverPreviewBooking(array $confirmationContent): Booking
    {
        $booking = new Booking([
            'transaction_id' => 'PREVIEW-WAIVER-0001',
            'customer_name' => 'Juan Dela Cruz',
            'customer_email' => 'juan@example.com',
            'customer_phone' => '+63 900 000 0000',
            'booking_type' => 'staycation',
            'total_amount' => 0,
            'payment_status' => 'confirmed',
            'booking_data' => [
                'transaction_id' => 'PREVIEW-WAIVER-0001',
                'customer_info' => [
                    'first_name' => 'Juan',
                    'last_name' => 'Dela Cruz',
                    'email' => 'juan@example.com',
                    'phone' => '+63 900 000 0000',
                ],
                'acceptances' => [[
                    'source' => 'camp_waiver_accepted',
                    'label' => 'Camp Waiver and Release from Liability',
                    'accepted' => true,
                    'accepted_at' => now()->toDateTimeString(),
                    'details' => [
                        'participant_name' => 'Juan Dela Cruz',
                        'signature_name' => 'Juan Dela Cruz',
                        'signature_date' => now()->toDateString(),
                    ],
                ]],
            ],
        ]);
        $booking->exists = true;

        return $booking;
    }
}
