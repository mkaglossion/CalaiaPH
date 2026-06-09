<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingConfirmationContent;
use App\Services\BookingConfirmationContentService;
use Illuminate\Http\Request;

class BookingConfirmationContentController extends Controller
{
    public function index(BookingConfirmationContentService $service)
    {
        $contents = $service->all();

        return view('admin.bookingConfirmation.index', [
            'contents' => $contents,
            'types' => [
                'staycation' => 'Staycation',
                'dayvisit' => 'Day Visit',
                'package' => 'Package',
            ],
        ]);
    }

    public function update(Request $request, string $bookingType, BookingConfirmationContentService $service)
    {
        $bookingType = BookingConfirmationContent::normalizeType($bookingType);

        $payload = data_get($request->input('contents', []), $bookingType, []);

        $validated = $request->validate([
            "contents.$bookingType.page_eyebrow" => ['nullable', 'string', 'max:120'],
            "contents.$bookingType.page_title" => ['required', 'string', 'max:255'],
            "contents.$bookingType.page_description" => ['nullable', 'string', 'max:2000'],
            "contents.$bookingType.next_steps_title" => ['nullable', 'string', 'max:255'],
            "contents.$bookingType.next_steps" => ['nullable', 'string', 'max:4000'],
            "contents.$bookingType.support_title" => ['nullable', 'string', 'max:255'],
            "contents.$bookingType.support_description" => ['nullable', 'string', 'max:2000'],
            "contents.$bookingType.support_email" => ['nullable', 'email', 'max:255'],
            "contents.$bookingType.support_phone" => ['nullable', 'string', 'max:50'],
            "contents.$bookingType.home_button_label" => ['nullable', 'string', 'max:255'],
            "contents.$bookingType.print_button_label" => ['nullable', 'string', 'max:255'],
            "contents.$bookingType.payment_button_label" => ['nullable', 'string', 'max:255'],
            "contents.$bookingType.email_subject" => ['nullable', 'string', 'max:255'],
            "contents.$bookingType.email_greeting" => ['nullable', 'string', 'max:255'],
            "contents.$bookingType.email_intro" => ['nullable', 'string', 'max:4000'],
            "contents.$bookingType.email_closing" => ['nullable', 'string', 'max:4000'],
        ]);

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
                'email_subject' => $payload['email_subject'] ?? null,
                'email_greeting' => $payload['email_greeting'] ?? null,
                'email_intro' => $payload['email_intro'] ?? null,
                'email_closing' => $payload['email_closing'] ?? null,
            ]
        );

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
}
