<?php

return [
    'menu' => [
        ['title' => 'Dashboard', 'icon' => 'home', 'route' => 'admin.dashboard'],
        ['title' => 'Home', 'icon' => 'home', 'route' => 'admin.home.*', 'sub' => [
            ['label' => 'Pages Information', 'route' => 'admin.home.home'],
            ['label' => 'Event Experience', 'route' => 'admin.eventExperience.sections'],
            ['label' => 'Testimonials', 'route' => 'admin.testimonials.index'],
        ]],
        ['title' => 'Bookings', 'icon' => 'calendar-check', 'route' => 'admin.bookings.*', 'sub' => [
            ['label' => 'All Bookings', 'route' => 'admin.bookings.index'],
            ['label' => 'Unified Inventory', 'route' => 'admin.bookings.inventory'],
            ['label' => 'Staycation Bookings', 'route' => 'admin.bookings.staycations'],
            ['label' => 'Food Bookings', 'route' => 'admin.bookings.foodandbeverage'],
            ['label' => 'Activities Bookings', 'route' => 'admin.bookings.activities'],
        ]],
        ['title' => 'Where to Stay', 'icon' => 'building', 'route' => 'admin.stay.*', 'sub' => [
            ['label' => 'Pages Information', 'route' => 'admin.stay.wheretostay'],
            ['label' => 'Staycation Rooms', 'route' => 'admin.stay.staycations'],
            ['label' => 'Season Durations', 'route' => 'admin.seasonDurations.index'],
            ['label' => 'Seasonal Pricing', 'route' => 'admin.stay.settings'],
            ['label' => 'Additional Fees', 'route' => 'admin.booking-settings.index'],
        ]],
        ['title' => 'Day Pacakages & Activities', 'icon' => 'map-pin', 'route' => 'admin.do.*', 'sub' => [
            ['label' => 'Pages Information', 'route' => 'admin.do.whattodo'],
            ['label' => 'Activities Management', 'route' => 'admin.do.activities'],
        ]],
        ['title' => 'Food & Beverage', 'icon' => 'utensils-crossed', 'route' => 'admin.food.*', 'sub' => [
            ['label' => 'Pages Information', 'route' => 'admin.food.foodandbeverage'],
            ['label' => 'Category Images', 'route' => 'admin.dining.category-images.index'],
            ['label' => 'Food Management', 'route' => 'admin.foods.index'],
        ]],
        ['title' => 'Special Events', 'icon' => 'star', 'route' => 'admin.specialEvents.*', 'sub' => [
            ['label' => 'Pages Information', 'route' => 'admin.specialEvents.specialevents'],
            ['label' => 'Manager', 'route' => 'admin.specialEvents.manager'],
        ]],
        ['title' => 'Our Farm', 'icon' => 'leaf', 'route' => 'admin.farm.*', 'sub' => [
            ['label' => 'Pages Information', 'route' => 'admin.farm.ourfarm'],
            ['label' => 'Farm Experiences', 'route' => 'admin.farm.experiences'],
        ]],
        ['title' => 'Shop', 'icon' => 'shopping-bag', 'route' => 'admin.shop.*', 'sub' => [
            ['label' => 'Pages Information', 'route' => 'admin.shop.shopstore'],
            ['label' => 'Store Management', 'route' => 'admin.shop.shopitems'],
            ['label' => 'Orders', 'route' => 'admin.shop.orders'],
            ['label' => 'Inventory Monitor', 'route' => 'admin.inventory.shop'],
        ]],
        ['title' => 'Policies & Terms', 'icon' => 'file-lock-2', 'route' => 'admin.policies.index', 'sub_provider' => 'policy_pages'],
        ['title' => 'Booking Confirmation', 'icon' => 'file-text', 'route' => 'admin.booking-confirmation.index', 'sub' => [
            ['label' => 'Staycation', 'route' => 'admin.booking-confirmation.index', 'params' => ['tab' => 'staycation']],
            ['label' => 'Package', 'route' => 'admin.booking-confirmation.index', 'params' => ['tab' => 'package']],
            ['label' => 'Camp Waiver', 'route' => 'admin.booking-confirmation.index', 'params' => ['tab' => 'camp-waiver']],
        ]],
        ['title' => 'Promo Codes', 'icon' => 'ticket', 'route' => 'admin.promo-codes.index'],
        ['title' => 'Settings', 'icon' => 'settings', 'route' => 'admin.settings.*', 'sub' => [
            ['label' => 'Profile', 'route' => 'admin.settings.profile'],
            ['label' => 'Change Password', 'route' => 'admin.settings.password'],
            ['label' => 'Activity Log', 'route' => 'admin.settings.activity-log'],
            ['label' => 'System Settings', 'route' => 'admin.settings.system'],
            ['label' => 'Alerts', 'route' => 'admin.settings.alerts'],
        ]],
    ],
];
