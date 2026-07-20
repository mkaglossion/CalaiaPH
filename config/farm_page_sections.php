<?php

return [
    'sections' => [
        'ethical_livestock' => [
            'title' => 'Ethically raised livestock.',
            'description' => 'Our animals live as nature intended: with plenty of space to roam, fresh air, and a natural diet free from antibiotics and growth hormones.',
            'image_url' => null,
            'secondary_image_url' => null,
            'items' => [
                ['key' => 'pasture-access', 'text' => 'Pasture-raised with daily access to open fields.'],
                ['key' => 'no-antibiotics', 'text' => 'No antibiotics or hormones - ever.'],
                ['key' => 'non-gmo-feed', 'text' => 'Non-GMO, locally-sourced feed.'],
                ['key' => 'humane-treatment', 'text' => 'Humane treatment with low-stress handling.'],
            ],
            'settings' => ['background_color' => '#6b4a2f'],
            'sort_order' => 10,
        ],
        'product_cards' => [
            'title' => null,
            'description' => null,
            'items' => [
                [
                    'key' => 'grass-fed-beef',
                    'title' => 'Grass-Fed Beef',
                    'description' => '100% grass-fed and grass-finished diet; higher in healthy Omega-3s.',
                    'meta' => 'Available seasonally',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
                [
                    'key' => 'heritage-pork',
                    'title' => 'Heritage Pork',
                    'description' => 'Small groups with room to root and explore for superior flavor and texture.',
                    'meta' => 'Available seasonally',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
                [
                    'key' => 'pasture-lamb',
                    'title' => 'Pasture Lamb',
                    'description' => 'Grazed on diverse pastures for naturally complex flavor.',
                    'meta' => 'Available seasonally',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
            ],
            'settings' => [],
            'sort_order' => 20,
        ],
        'field_banners' => [
            'title' => null,
            'description' => null,
            'items' => [
                [
                    'key' => 'vegetable-garden',
                    'title' => 'Vegetable Garden.',
                    'description' => 'We grow over 50 varieties of vegetables using organic methods and natural amendments like compost and cover crops.',
                    'alignment' => 'left',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
                [
                    'key' => 'orchard-fruits',
                    'title' => 'Orchard Fruits.',
                    'description' => 'Our heritage fruit trees are grown without chemical sprays, allowing natural ecosystems to maintain balance.',
                    'alignment' => 'right',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
            ],
            'settings' => [],
            'sort_order' => 30,
        ],
        'growing_nature' => [
            'title' => 'Growing with nature.',
            'description' => null,
            'items' => [
                [
                    'key' => 'soil-first',
                    'title' => 'Soil First',
                    'description' => 'We build our soil health through composting, cover crops, and minimal tillage.',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
                [
                    'key' => 'biodiversity',
                    'title' => 'Biodiversity',
                    'description' => 'Companion planting and diverse ecosystems keep plants healthy naturally.',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
                [
                    'key' => 'seasonal-focus',
                    'title' => 'Seasonal Focus',
                    'description' => 'We grow what thrives in our climate without forcing seasons.',
                    'image_url' => null,
                    'image_public_id' => null,
                ],
            ],
            'settings' => ['background_color' => '#3f6668'],
            'sort_order' => 40,
        ],
        'farm_experiences' => [
            'title' => 'Farm Experiences',
            'description' => 'Connect with your food and learn about sustainable agriculture through hands-on activities',
            'items' => [],
            'settings' => [],
            'sort_order' => 50,
        ],
    ],
];
