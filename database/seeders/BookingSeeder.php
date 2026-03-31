<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Booking;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleBookings = [
            [
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'customer_name' => 'Maria Santos',
                'customer_email' => 'maria.santos@email.com',
                'customer_phone' => '+63 917 123 4567',
                'booking_type' => 'staycation',
                'checkin_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'booking_data' => [
                    'accommodations' => [
                        [
                            'room_id' => 1,
                            'name' => 'Standard Glamping Tent',
                            'quantity' => 1,
                            'price' => 5440,
                            'subtotal' => 10880,
                        ]
                    ],
                    'customer_info' => [
                        'first_name' => 'Maria',
                        'last_name' => 'Santos',
                        'email' => 'maria.santos@email.com',
                        'phone' => '+63 917 123 4567',
                    ],
                    'adults' => 2,
                    'children' => 0,
                ],
                'total_amount' => 10880.00,
                'subtotal' => 10880.00,
                'vat_amount' => 0.00,
                'vat_rate' => 0.12,
                'is_vat_inclusive' => false,
                'payment_method' => 'credit_card',
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'checkin_status' => 'pending',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'customer_name' => 'John David',
                'customer_email' => 'john.david@email.com',
                'customer_phone' => '+63 912 345 6789',
                'booking_type' => 'staycation',
                'checkin_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'booking_data' => [
                    'accommodations' => [
                        [
                            'room_id' => 2,
                            'name' => 'Premium Dome Tent',
                            'quantity' => 2,
                            'price' => 6800,
                            'subtotal' => 13600,
                        ]
                    ],
                    'customer_info' => [
                        'first_name' => 'John',
                        'last_name' => 'David',
                        'email' => 'john.david@email.com',
                        'phone' => '+63 912 345 6789',
                    ],
                    'adults' => 4,
                    'children' => 2,
                ],
                'total_amount' => 15232.00,
                'subtotal' => 13600.00,
                'vat_amount' => 1632.00,
                'vat_rate' => 0.12,
                'is_vat_inclusive' => false,
                'payment_method' => 'gcash',
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'checkin_status' => 'pending',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'customer_name' => 'Ana Cruz',
                'customer_email' => 'ana.cruz@email.com',
                'customer_phone' => '+63 918 765 4321',
                'booking_type' => 'staycation',
                'checkin_date' => Carbon::now()->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'booking_data' => [
                    'accommodations' => [
                        [
                            'room_id' => 1,
                            'name' => 'Standard Glamping Tent',
                            'quantity' => 1,
                            'price' => 5440,
                            'subtotal' => 5440,
                        ]
                    ],
                    'customer_info' => [
                        'first_name' => 'Ana',
                        'last_name' => 'Cruz',
                        'email' => 'ana.cruz@email.com',
                        'phone' => '+63 918 765 4321',
                    ],
                    'adults' => 1,
                    'children' => 0,
                ],
                'total_amount' => 5440.00,
                'subtotal' => 5440.00,
                'vat_amount' => 0.00,
                'vat_rate' => 0.12,
                'is_vat_inclusive' => false,
                'payment_method' => 'paypal',
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'checkin_status' => 'checked_in',
                'checkin_timestamp' => Carbon::now()->subHours(2),
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'customer_name' => 'Robert Gomez',
                'customer_email' => 'robert.gomez@email.com',
                'customer_phone' => '+63 919 876 5432',
                'booking_type' => 'staycation',
                'checkin_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
                'booking_data' => [
                    'accommodations' => [
                        [
                            'room_id' => 3,
                            'name' => 'Luxury Tent Suite',
                            'quantity' => 1,
                            'price' => 8900,
                            'subtotal' => 17800,
                        ]
                    ],
                    'customer_info' => [
                        'first_name' => 'Robert',
                        'last_name' => 'Gomez',
                        'email' => 'robert.gomez@email.com',
                        'phone' => '+63 919 876 5432',
                    ],
                    'adults' => 2,
                    'children' => 1,
                ],
                'total_amount' => 17800.00,
                'subtotal' => 17800.00,
                'vat_amount' => 0.00,
                'vat_rate' => 0.12,
                'is_vat_inclusive' => false,
                'payment_method' => 'credit_card',
                'payment_status' => 'paid',
                'status' => 'completed',
                'checkin_status' => 'checked_out',
                'checkin_timestamp' => Carbon::now()->subDays(3)->setHour(14),
                'checkout_timestamp' => Carbon::now()->subDays(1)->setHour(11),
                'created_at' => Carbon::now()->subDays(10),
            ],
            [
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'customer_name' => 'Jessica Torres',
                'customer_email' => 'jessica.torres@email.com',
                'customer_phone' => '+63 920 111 2222',
                'booking_type' => 'staycation',
                'checkin_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->addDays(17)->format('Y-m-d'),
                'booking_data' => [
                    'accommodations' => [
                        [
                            'room_id' => 2,
                            'name' => 'Premium Dome Tent',
                            'quantity' => 1,
                            'price' => 6800,
                            'subtotal' => 6800,
                        ],
                        [
                            'room_id' => 1,
                            'name' => 'Standard Glamping Tent',
                            'quantity' => 1,
                            'price' => 5440,
                            'subtotal' => 5440,
                        ]
                    ],
                    'customer_info' => [
                        'first_name' => 'Jessica',
                        'last_name' => 'Torres',
                        'email' => 'jessica.torres@email.com',
                        'phone' => '+63 920 111 2222',
                    ],
                    'adults' => 3,
                    'children' => 2,
                ],
                'total_amount' => 13747.60,
                'subtotal' => 12240.00,
                'vat_amount' => 1507.60,
                'vat_rate' => 0.12,
                'is_vat_inclusive' => false,
                'payment_method' => 'gcash',
                'payment_status' => 'pending',
                'status' => 'confirmed',
                'checkin_status' => 'pending',
                'created_at' => Carbon::now()->subHours(24),
            ],
            [
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'customer_name' => 'Michael Lee',
                'customer_email' => 'michael.lee@email.com',
                'customer_phone' => '+63 921 333 4444',
                'booking_type' => 'staycation',
                'checkin_date' => Carbon::now()->subDays(8)->format('Y-m-d'),
                'checkout_date' => Carbon::now()->subDays(6)->format('Y-m-d'),
                'booking_data' => [
                    'accommodations' => [
                        [
                            'room_id' => 1,
                            'name' => 'Standard Glamping Tent',
                            'quantity' => 2,
                            'price' => 5440,
                            'subtotal' => 10880,
                        ]
                    ],
                    'customer_info' => [
                        'first_name' => 'Michael',
                        'last_name' => 'Lee',
                        'email' => 'michael.lee@email.com',
                        'phone' => '+63 921 333 4444',
                    ],
                    'adults' => 4,
                    'children' => 0,
                ],
                'total_amount' => 10880.00,
                'subtotal' => 10880.00,
                'vat_amount' => 0.00,
                'vat_rate' => 0.12,
                'is_vat_inclusive' => false,
                'payment_method' => 'credit_card',
                'payment_status' => 'paid',
                'status' => 'completed',
                'checkin_status' => 'checked_out',
                'checkin_timestamp' => Carbon::now()->subDays(8)->setHour(15),
                'checkout_timestamp' => Carbon::now()->subDays(6)->setHour(10),
                'created_at' => Carbon::now()->subDays(12),
            ],
        ];

        foreach ($sampleBookings as $booking) {
            Booking::create($booking);
        }
    }
}
