<?php

namespace App\Services;

use App\Models\BookingConfirmationContent;
use App\Models\InformationPage;
use Illuminate\Support\Facades\Cache;

class BookingConfirmationContentService
{
    public const CACHE_KEY = 'booking_confirmation_contents_resolved';
    public const DEFAULT_CAMP_WAIVER_FORM_URL = 'https://tinyurl.com/Calaiawaiver';

    public function all(): array
    {
        $resolved = Cache::remember(self::CACHE_KEY, now()->addMinutes(30), function () {
            $resolved = [];

            foreach (BookingConfirmationContent::allowedTypes() as $type) {
                $resolved[$type] = $this->resolveType($type);
            }

            return $resolved;
        });

        $defaultsUpdated = false;

        foreach (BookingConfirmationContent::allowedTypes() as $type) {
            if (!isset($resolved[$type])) {
                $resolved[$type] = $this->defaultsFor($type);
                $defaultsUpdated = true;
            }
        }

        if ($defaultsUpdated) {
            Cache::put(self::CACHE_KEY, $resolved, now()->addMinutes(30));
        }

        $contactDetails = InformationPage::contactDetails();

        foreach ($resolved as &$content) {
            if (!is_array($content)) {
                continue;
            }

            $content['support'] = is_array($content['support'] ?? null) ? $content['support'] : [];
            $content['support']['email'] = $contactDetails['email'];
            $content['support']['phone'] = $contactDetails['phone'];
        }
        unset($content);

        return $resolved;
    }

    public function forType(string $type, array $context = []): array
    {
        $normalized = BookingConfirmationContent::normalizeType($type);
        $resolved = $this->all()[$normalized] ?? $this->defaultsFor($normalized);

        return $this->applyContextTokens($resolved, $context);
    }

    public function forBookingData(array $bookingData, array $context = []): array
    {
        $type = BookingConfirmationContent::normalizeType(
            (string) ($bookingData['booking_type'] ?? $bookingData['type'] ?? 'dayvisit')
        );

        return $this->forType($type, $context);
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function resolveType(string $type): array
    {
        $defaults = $this->defaultsFor($type);
        $record = BookingConfirmationContent::where('booking_type', $type)->first();

        if (!$record) {
            return $defaults;
        }

        $resolved = [
            'page' => [
                'eyebrow' => $this->fallbackValue($record->page_eyebrow, $defaults['page']['eyebrow']),
                'title' => $this->fallbackValue($record->page_title, $defaults['page']['title']),
                'description' => $this->fallbackValue($record->page_description, $defaults['page']['description']),
            ],
            'next_steps' => [
                'title' => $this->fallbackValue($record->next_steps_title, $defaults['next_steps']['title']),
                'items' => $record->next_steps ?? $defaults['next_steps']['items'],
            ],
            'support' => [
                'title' => $this->fallbackValue($record->support_title, $defaults['support']['title']),
                'description' => $this->fallbackValue($record->support_description, $defaults['support']['description']),
                'email' => $this->fallbackValue($record->support_email, $defaults['support']['email']),
                'phone' => $this->fallbackValue($record->support_phone, $defaults['support']['phone']),
            ],
            'buttons' => [
                'home' => $this->fallbackValue($record->home_button_label, $defaults['buttons']['home']),
                'print' => $this->fallbackValue($record->print_button_label, $defaults['buttons']['print']),
                'payment' => $this->fallbackValue($record->payment_button_label, $defaults['buttons']['payment']),
            ],
            'email' => [
                'subject' => $this->fallbackValue($record->email_subject, $defaults['email']['subject']),
                'greeting' => $this->fallbackValue($record->email_greeting, $defaults['email']['greeting']),
                'intro' => $this->fallbackValue($record->email_intro, $defaults['email']['intro']),
                'closing' => $this->fallbackValue($record->email_closing, $defaults['email']['closing']),
            ],
        ];

        if (isset($defaults['camp_waiver'])) {
            $resolved['camp_waiver'] = [
                'title' => $this->fallbackValue($record->camp_waiver_title, $defaults['camp_waiver']['title']),
                'body' => $this->fallbackValue($record->camp_waiver_body, $defaults['camp_waiver']['body']),
                'form_url' => $this->fallbackValue($record->camp_waiver_form_url ?? null, $defaults['camp_waiver']['form_url'] ?? null),
            ];
        }

        return array_replace_recursive($defaults, $resolved);
    }

    private function defaultsFor(string $type): array
    {
        return match ($type) {
            'staycation' => [
                'page' => [
                    'eyebrow' => 'CalaiaPH Staycation',
                    'title' => 'Booking Confirmed!',
                    'description' => 'Your staycation booking has been successfully processed and confirmed.',
                ],
                'next_steps' => [
                    'title' => 'What\'s Next?',
                    'items' => [
                        'Check your email for a detailed confirmation and booking instructions.',
                        'Save the date in your calendar so your trip is locked in.',
                        'Contact us if you need to make changes or have any questions.',
                    ],
                ],
                'support' => [
                    'title' => 'Need assistance?',
                    'description' => 'Reach us anytime if you need help with your booking.',
                    'email' => InformationPage::DEFAULT_CONTACT_EMAIL,
                    'phone' => InformationPage::DEFAULT_CONTACT_PHONE,
                ],
                'buttons' => [
                    'home' => 'Return to Home',
                    'print' => 'Print Confirmation',
                    'payment' => null,
                ],
                'camp_waiver' => [
                    'title' => '📋 Camp Waiver Form — Required',
                    'body' => $this->defaultCampWaiverEmailBody(),
                    'form_url' => self::DEFAULT_CAMP_WAIVER_FORM_URL,
                ],
                'email' => [
                    'subject' => 'Booking Receipt - CalaiaPH',
                    'greeting' => 'Dear',
                    'intro' => 'Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!',
                    'closing' => 'We look forward to welcoming you to CalaiaPH!',
                ],
            ],
            'campwaiver' => [
                'page' => [
                    'eyebrow' => 'Booking Confirmation',
                    'title' => 'Camp Waiver',
                    'description' => 'Manage the camp waiver shown to staycation guests before payment.',
                ],
                'next_steps' => [
                    'title' => 'What\'s Next?',
                    'items' => [
                        'Update the waiver title and body shown before checkout.',
                        'Keep the wording aligned with your operational and legal requirements.',
                    ],
                ],
                'support' => [
                    'title' => 'Need assistance?',
                    'description' => 'Reach us anytime if you need help with your booking content.',
                    'email' => InformationPage::DEFAULT_CONTACT_EMAIL,
                    'phone' => InformationPage::DEFAULT_CONTACT_PHONE,
                ],
                'buttons' => [
                    'home' => null,
                    'print' => null,
                    'payment' => null,
                ],
                'camp_waiver' => [
                    'title' => 'Camp Waiver and Release from Liability',
                    'body' => $this->defaultCampWaiverBody(),
                    'form_url' => self::DEFAULT_CAMP_WAIVER_FORM_URL,
                ],
                'email' => [
                    'subject' => null,
                    'greeting' => null,
                    'intro' => null,
                    'closing' => null,
                ],
            ],
            'package' => [
                'page' => [
                    'eyebrow' => 'CalaiaPH Package Booking',
                    'title' => 'Confirm Your Payment',
                    'description' => 'Review the booking summary below before proceeding to secure checkout.',
                ],
                'next_steps' => [
                    'title' => 'What\'s Next?',
                    'items' => [
                        'Review your booking details and total amount.',
                        'Proceed to secure checkout to complete payment.',
                        'Keep your reference number for any future support requests.',
                    ],
                ],
                'support' => [
                    'title' => 'Need help with your package booking?',
                    'description' => 'Reach us anytime if you need help with your package payment.',
                    'email' => InformationPage::DEFAULT_CONTACT_EMAIL,
                    'phone' => InformationPage::DEFAULT_CONTACT_PHONE,
                ],
                'buttons' => [
                    'home' => 'Back to Packages',
                    'print' => null,
                    'payment' => 'Proceed to Payment',
                ],
                'email' => [
                    'subject' => 'Booking Confirmation - {reference}',
                    'greeting' => 'Hi',
                    'intro' => 'Your package booking is confirmed.',
                    'closing' => 'If you need changes to your reservation, contact us with your reference number.',
                ],
            ],
            default => [
                'page' => [
                    'eyebrow' => 'CalaiaPH Day Visit',
                    'title' => 'Day Visit Confirmed!',
                    'description' => 'Your day visit booking has been successfully processed and confirmed.',
                ],
                'next_steps' => [
                    'title' => 'What\'s Next?',
                    'items' => [
                        'Check your email for your confirmation and entry details.',
                        'Prepare your booking reference before your visit.',
                        'Contact us if you need to make changes or have any questions.',
                    ],
                ],
                'support' => [
                    'title' => 'Need assistance?',
                    'description' => 'Reach us anytime if you need help with your booking.',
                    'email' => InformationPage::DEFAULT_CONTACT_EMAIL,
                    'phone' => InformationPage::DEFAULT_CONTACT_PHONE,
                ],
                'buttons' => [
                    'home' => 'Return to Home',
                    'print' => 'Print Confirmation',
                    'payment' => null,
                ],
                'email' => [
                    'subject' => 'Booking Receipt - CalaiaPH',
                    'greeting' => 'Dear',
                    'intro' => 'Your booking has been successfully processed and confirmed. Below is your receipt. Thank you for choosing CalaiaPH!',
                    'closing' => 'We look forward to welcoming you to CalaiaPH!',
                ],
            ],
        };
    }

    public function defaultCampWaiverBody(): string
    {
        return <<<'TEXT'
CAMP WAIVER AND RELEASE FROM LIABILITY

By accomplishing this form, the guest freely and voluntarily gives his/her consent to provide necessary and relevant personal data for the management of the facility and to reasonably process the same for records purposes and other relevant matters. Furthermore, the guest acknowledges that his/her personal data may also be shared to third persons if there is a reasonable need to do so, such as in cases of emergency. Personal data gathered shall be deleted from the files within one (1) year.

The guest is also aware that the facility is provided with CCTV cameras which may capture video or photos of guests within the vicinity for security and other related purposes.

Participant's Information:

Full Name of Camper:
Date of Birth:
Gender:
Address:
Parent/Guardian Name (if applicable):
Emergency Contact Name:
Emergency Contact Phone Number:
Relationship to Participant:
Kindly state any medical or physical condition that you have:

I. ACKNOWLEDGMENT OF RISKS
1. I, the undersigned, acknowledge and am fully aware that the activities involved in participating in the camp activities involve inherent risks. These risks include, but are not limited to, physical injury, illness, emotional distress, or death due to strenuous activities such as:
- Physical activities (for example: hiking, swimming, archery, climbing, sports)
- Environmental conditions (for example: uneven terrain, weather)
- Camp equipment (for example: ropes, climbing gear, water sports equipment)
- Interaction with other participants
- Potential for allergic reactions (for example: food, insects, plants)
- Animal or insect bites or stings
- Participation in outdoor adventures or challenges
2. I understand that these activities are physically demanding and may involve risks that cannot be fully eliminated even with proper training and supervision.
3. This Waiver and Release from Liability covers all activities within the park. By signing this form, the guest acknowledges that the owners, management, or employees of Calaia Nature Park have no fault whatsoever for any injury, damage, or death that the guest may suffer while participating in the camp's activities.

II. ASSUMPTION OF RISK
I voluntarily and freely assume all risks associated with participating in the camp activities, including participation by my child, children, or ward if applicable. I understand that accidents and injuries can occur. I agree to take full responsibility for any injury, illness, or damage that I may incur, or that my child, children, or ward may incur, whether caused by my own recklessness, negligence, lack of ability, or the actions of others.

III. RELEASE OF LIABILITY
In consideration of being allowed to participate in camp activities, I, for myself, my heirs, executors, administrators, and assigns, do hereby release, waive, discharge, and hold harmless Calaia Nature Park, its owners, employees, agents, volunteers, and representatives from any and all liability for any injury, illness, death, or property damage arising out of my participation or the participation of my child, children, or ward in camp activities, whether caused by the negligence of others or the personnel which includes the employees and management of Calaia Nature Park or otherwise. I agree not to bring any legal action or claim against Calaia Nature Park for any injury, damage, or loss sustained during participation in camp activities.

IV. MEDICAL AUTHORIZATION
In the event that I or my child, children, or ward require medical treatment during my or our stay or visit in the camp, I authorize Calaia Nature Park to find a way to transport myself or my child, children, or ward to the nearest medical center either via private or public vehicle. I understand that I will be financially responsible for any medical costs incurred.

I further agree to disclose or inform the camp staff of any medical condition, allergies, or special needs that I or my child, children, or ward knowingly have which may prevent us from participating in the activities.

V. HEALTH AND FITNESS
I confirm that I, my child, children, or ward is in good physical and mental health to participate in the camp activities.

I take it upon my responsibility to disclose any physical or mental condition that I have or my child, children, or ward have that may prevent us from participating in the camp activities.

VI. PHOTO/VIDEO RELEASE
I consent to the use of photographs, videos, or other media taken during our participation in camp activities for promotional, marketing, or educational purposes. I understand that I will not receive any compensation for such use and waive any right to review or approve the final product.

VII. PARENTAL/GUARDIAN CONSENT (for Minors)
I, the undersigned parent/legal guardian, give my permission for my child/children or ward to participate in the camp activities. I agree to assume full responsibility for my child/children or ward's safety and well-being during their participation in the camp activities and hold Calaia Nature Park, its employees, and management harmless from any liability, as stated above.

VIII. RULES AND REGULATIONS
I agree to fully abide by all the rules and guidelines provided by Calaia Nature Park, including safety instructions and camp regulations. In the event that I or my child/children or ward fails to abide or follow the rules and guidelines provided by the management, I and/or my child/children or ward will be prevented from further joining the camp activities. If through my repeated reckless or negligent act, or my child/children or ward's act, that puts ourselves or the life of others in danger after having been called out by the management or its representative, I shall be asked to immediately leave the facility without refund.

IX. GOVERNING LAW
This waiver and release of liability shall be governed by and construed in accordance with existing laws. Any dispute will be resolved exclusively in the court of Batangas having jurisdiction over the subject matter, to the exclusion of all other courts.

X. SEVERABILITY
If any part of this waiver is found to be invalid, unenforceable, or unconstitutional, the remaining provisions that are valid shall remain in full force and effect.

XI. ACKNOWLEDGMENT AND SIGNATURE
I acknowledge that I have read and understood this waiver and release of liability. I understand that by affixing my signature in this document, I am waiving certain legal rights, including the right to sue.

Name and Signature of Guest:
Date:

If guest is under 18 (Parent/Guardian to sign):
Name and Signature of Parent/Guardian:
Name of Child/Ward:
Date:
TEXT;
    }

    public function defaultCampWaiverEmailBody(): string
    {
        return <<<'TEXT'
Please note that filling out and signing our camp waiver form is mandatory for all guests, including children, prior to your date of stay.
Each participant must have their own individual signed waiver — one form per person, with no exceptions.
We kindly ask that all waivers be completed and submitted at least one day before your check-in date to help ensure a smooth check-in process.
Thank you so much for your cooperation!
TEXT;
    }

    private function fallbackValue(?string $value, ?string $default): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value !== '' ? $value : $default;
    }

    private function applyContextTokens(array $content, array $context): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = $this->applyContextTokens($value, $context);
            } elseif (is_string($value)) {
                $content[$key] = $this->replaceTokens($value, $context);
            }
        }

        return $content;
    }

    private function replaceTokens(string $value, array $context): string
    {
        if ($value === '' || empty($context)) {
            return $value;
        }

        $replace = [];
        foreach ($context as $key => $contextValue) {
            if (is_scalar($contextValue) || $contextValue === null) {
                $replace['{' . $key . '}'] = (string) $contextValue;
            }
        }

        return strtr($value, $replace);
    }
}
