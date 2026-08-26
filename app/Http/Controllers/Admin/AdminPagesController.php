<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DayTourSwiperImage;
use App\Models\DayTourText;
use App\Models\StaySwiperImage;
use App\Models\StayText;
use App\Models\StaycationPageText;
use App\Models\DoSwiperImage;
use App\Models\DoText;
use App\Models\FoodSwiperImage;
use App\Models\FoodText;
use App\Models\SpecialSwiperImage;
use App\Models\SpecialText;
use App\Models\FarmSwiperImage;
use App\Models\FarmText;
use App\Models\FarmPageSection;
use App\Models\HomeSwiperImage;
use App\Models\HomeText;
use App\Models\PackageSwiperImage;
use App\Models\PackageText;
use App\Models\Package;
use App\Models\EventSection;
use App\Models\PackageImage;
use App\Models\PackageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminPagesController extends Controller
{
    private $pageConfigs = [
        'home' => [
            'imageModel' => HomeSwiperImage::class,
            'textModel' => HomeText::class,
            'view' => 'admin.home.home',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Home page.',
                'imageStoreRoute' => 'home-swiper-images.store',
                'imageDestroyRoute' => '/admin/home-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'home-texts.store',
                'textDestroyRoute' => '/admin/home-texts',
            ],
        ],
        'dayTour' => [
            'imageModel' => DayTourSwiperImage::class,
            'textModel' => DayTourText::class,
            'view' => 'admin.dayTour.daytour',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Day Tour page.',
                'imageStoreRoute' => 'day-tour-swiper-images.store',
                'imageDestroyRoute' => '/admin/day-tour-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'day-tour-texts.store',
                'textDestroyRoute' => '/admin/day-tour-texts',
            ],
        ],
        'packages' => [
            'imageModel' => PackageSwiperImage::class,
            'textModel' => PackageText::class,
            'view' => 'admin.packages.packagesInfos',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Packages page.',
            ],
        ],
        'whereToStay' => [
            'imageModel' => StaySwiperImage::class,
            'textModel' => StayText::class,
            'view' => 'admin.whereToStay.wheretostay',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Where to Stay page.',
                'imageStoreRoute' => 'stay-swiper-images.store',
                'imageDestroyRoute' => '/admin/stay-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'stay-texts.store',
                'textDestroyRoute' => '/admin/stay-texts',
                'pageCopyStoreRoute' => 'admin.stay.copy.store',
                'pageCopyFields' => [
                    'label' => [
                        'sources' => ['staycation_page_label'],
                        'fallback' => 'Staycation Options',
                    ],
                    'title' => [
                        'sources' => ['staycation_page_title'],
                        'fallback' => 'All Glamping & Camping',
                    ],
                    'intro' => [
                        'sources' => ['staycation_page_intro'],
                        'fallback' => 'Choose from our range of glamping tents and camping options. Each option has unique features that suit your needs and tastes.',
                    ],
                ],
                'pageCopyMeta' => [
                    'label' => ['label' => 'Page Label'],
                    'title' => ['label' => 'Page Title'],
                    'intro' => ['label' => 'Page Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                ],
            ],
        ],
        'whatToDo' => [
            'imageModel' => DoSwiperImage::class,
            'textModel' => DoText::class,
            'view' => 'admin.whatToDo.whattodo',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your What to Do page.',
                'imageStoreRoute' => 'do-swiper-images.store',
                'imageDestroyRoute' => '/admin/do-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'do-texts.store',
                'textDestroyRoute' => '/admin/do-texts',
                'pageCopyStoreRoute' => 'admin.do.copy.store',
                'excludePrefixes' => ['packages_'],
                'pageCopyFields' => [
                    'title' => [
                        'sources' => ['packages_title'],
                        'fallback' => 'Day Packages',
                    ],
                    'intro' => [
                        'sources' => ['packages_intro'],
                        'fallback' => 'Start with our curated package options, then explore activities you can add to your stay.',
                    ],
                ],
                'pageCopyMeta' => [
                    'title' => ['label' => 'Packages Title'],
                    'intro' => ['label' => 'Packages Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                ],
            ],
        ],
        'foodAndBeverage' => [
            'imageModel' => FoodSwiperImage::class,
            'textModel' => FoodText::class,
            'view' => 'admin.foodBeverages.foodandbeverage',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Food & Beverage page.',
                'imageStoreRoute' => 'food-swiper-images.store',
                'imageDestroyRoute' => '/admin/food-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'food-texts.store',
                'textDestroyRoute' => '/admin/food-texts',
                'pageCopyStoreRoute' => 'admin.dining.category-images.copy.store',
                'excludeLabels' => [
                    'dining_page_title',
                    'dining_page_intro',
                    'dining_messhall_title',
                    'dining_messhall_intro',
                    'dining_cook_title',
                    'dining_cook_intro',
                    'dining_cta_title',
                    'dining_cta_intro',
                    'dining_cta_button',
                    'dining_cta_note',
                ],
                'pageCopyFields' => [
                    'title' => [
                        'sources' => ['dining_page_title'],
                        'fallback' => 'Dining at Calaia Nature Park',
                    ],
                    'intro' => [
                        'sources' => ['dining_page_intro'],
                        'fallback' => 'Experience farm-to-table dining at our mess hall or enjoy cooking your own meal by choosing from our ready-to-cook options on your own grill setup.',
                    ],
                    'messhall_title' => [
                        'sources' => ['dining_messhall_title'],
                        'fallback' => 'Mess Hall Dining',
                    ],
                    'messhall_intro' => [
                        'sources' => ['dining_messhall_intro'],
                        'fallback' => 'Our breezy and spacious mess hall is perfect for groups and special occasions. Our kitchen is ready to serve you delicious and hearty farm-to-table meals.',
                    ],
                    'cook_title' => [
                        'sources' => ['dining_cook_title'],
                        'fallback' => 'Cook Your Own Experience',
                    ],
                    'cook_intro' => [
                        'sources' => ['dining_cook_intro'],
                        'fallback' => 'As we set up your grill, choose your menu from a variety of frozen, ready-to-cook meals for an authentic camping experience!',
                    ],
                    'cta_title' => [
                        'sources' => ['dining_cta_title'],
                        'fallback' => 'Ready to Dine With Us?',
                    ],
                    'cta_intro' => [
                        'sources' => ['dining_cta_intro'],
                        'fallback' => 'Message us to reserve your dining experience. We can accommodate lunch, dinner, group events, and special arrangements.',
                    ],
                    'cta_button' => [
                        'sources' => ['dining_cta_button'],
                        'fallback' => 'Make a Reservation on Messenger',
                    ],
                    'cta_note' => [
                        'sources' => ['dining_cta_note'],
                        'fallback' => 'Available for lunch and dinner reservations. Message us for inquiries.',
                    ],
                ],
                'pageCopyMeta' => [
                    'title' => ['label' => 'Page Title'],
                    'intro' => ['label' => 'Page Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                    'messhall_title' => ['label' => 'Mess Hall Title'],
                    'messhall_intro' => ['label' => 'Mess Hall Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                    'cook_title' => ['label' => 'Cook Your Own Title'],
                    'cook_intro' => ['label' => 'Cook Your Own Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                    'cta_title' => ['label' => 'CTA Title'],
                    'cta_intro' => ['label' => 'CTA Intro', 'type' => 'textarea', 'rows' => 4],
                    'cta_button' => ['label' => 'CTA Button Label'],
                    'cta_note' => ['label' => 'CTA Note', 'type' => 'textarea', 'rows' => 3],
                ],
            ],
        ],
        'specialEvents' => [
            'imageModel' => SpecialSwiperImage::class,
            'textModel' => SpecialText::class,
            'view' => 'admin.specialEvents.specialevents',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Special Events page.',
                'imageStoreRoute' => 'special-swiper-images.store',
                'imageDestroyRoute' => '/admin/special-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'special-texts.store',
                'textDestroyRoute' => '/admin/special-texts',
                'pageCopyStoreRoute' => 'admin.specialEvents.copy.store',
                'excludeLabels' => [
                    'special_events_title',
                    'special_events_intro',
                    'special_events_cta_title',
                    'special_events_cta_intro',
                    'special_events_cta_primary_label',
                    'special_events_cta_primary_url',
                    'special_events_cta_secondary_label',
                    'special_events_cta_secondary_url',
                ],
                'pageCopyFields' => [
                    'title' => [
                        'sources' => ['special_events_title'],
                        'fallback' => 'Our Special Events',
                    ],
                    'intro' => [
                        'sources' => ['special_events_intro'],
                        'fallback' => 'From intimate weddings to grand corporate celebrations, our beautiful farm setting provides the perfect backdrop for your special occasion.',
                    ],
                    'cta_title' => [
                        'sources' => ['special_events_cta_title'],
                        'fallback' => 'Have a Custom Event in Mind?',
                    ],
                    'cta_intro' => [
                        'sources' => ['special_events_cta_intro'],
                        'fallback' => 'We specialize in creating bespoke experiences tailored to your unique vision. From intimate gatherings to grand celebrations, let us bring your dream event to life.',
                    ],
                    'cta_primary_label' => [
                        'sources' => ['special_events_cta_primary_label'],
                        'fallback' => 'Plan Custom Event',
                    ],
                    'cta_primary_url' => [
                        'sources' => ['special_events_cta_primary_url'],
                        'fallback' => 'https://m.me/410774518796277',
                    ],
                    'cta_secondary_label' => [
                        'sources' => ['special_events_cta_secondary_label'],
                        'fallback' => 'Email Us Now',
                    ],
                    'cta_secondary_url' => [
                        'sources' => ['special_events_cta_secondary_url'],
                        'fallback' => 'https://mail.google.com/mail/?view=cm&fs=1&to=calaia.ph@gmail.com',
                    ],
                ],
                'pageCopyMeta' => [
                    'title' => ['label' => 'Page Title'],
                    'intro' => ['label' => 'Page Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                    'cta_title' => ['label' => 'CTA Title'],
                    'cta_intro' => ['label' => 'CTA Intro', 'type' => 'textarea', 'rows' => 4],
                    'cta_primary_label' => ['label' => 'Primary Button Label'],
                    'cta_primary_url' => ['label' => 'Primary Button URL', 'type' => 'url'],
                    'cta_secondary_label' => ['label' => 'Secondary Button Label'],
                    'cta_secondary_url' => ['label' => 'Secondary Button URL', 'type' => 'url'],
                ],
            ],
        ],
        'ourFarm' => [
            'imageModel' => FarmSwiperImage::class,
            'textModel' => FarmText::class,
            'view' => 'admin.ourFarm.ourfarm',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Our Farm page.',
                'imageStoreRoute' => 'farm-swiper-images.store',
                'imageDestroyRoute' => '/admin/farm-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'farm-texts.store',
                'textDestroyRoute' => '/admin/farm-texts',
                'pageCopyStoreRoute' => 'admin.farm.copy.store',
                'excludePrefixes' => ['farm_page_', 'farm_cta_'],
                'pageCopyFields' => [
                    'title' => [
                        'sources' => ['farm_page_title'],
                        'fallback' => 'Our Sustainable Farming',
                    ],
                    'intro' => [
                        'sources' => ['farm_page_intro'],
                        'fallback' => 'Discover how we raise livestock and grow produce with respect for nature and animal welfare',
                    ],
                    'cta_title' => [
                        'sources' => ['farm_cta_title'],
                        'fallback' => 'Ready for your farm experience?',
                    ],
                    'cta_intro' => [
                        'sources' => ['farm_cta_intro'],
                        'fallback' => 'Message us on Messenger to ask about farm visits, group reservations, and special arrangements.',
                    ],
                    'cta_button' => [
                        'sources' => ['farm_cta_button'],
                        'fallback' => 'Message Us on Messenger',
                    ],
                    'cta_note' => [
                        'sources' => ['farm_cta_note'],
                        'fallback' => 'We usually reply as soon as possible during business hours.',
                    ],
                ],
                'pageCopyMeta' => [
                    'title' => ['label' => 'Page Title'],
                    'intro' => ['label' => 'Page Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                    'cta_title' => ['label' => 'Reserve Title'],
                    'cta_intro' => ['label' => 'Reserve Message', 'type' => 'textarea', 'rows' => 4],
                    'cta_button' => ['label' => 'Reserve Button Label'],
                    'cta_note' => ['label' => 'Reserve Note', 'type' => 'textarea', 'rows' => 3],
                ],
            ],
        ],
        'shopStore' => [
            'imageModel' => \App\Models\ShopSwiperImage::class,
            'textModel' => \App\Models\ShopText::class,
            'view' => 'admin.ShopStore.shopstore',
            'cms' => [
                'title' => 'Page Information',
                'description' => 'Manage the image slider and content displayed on your Shop Store page.',
                'imageStoreRoute' => 'admin.shop-swiper-images.store',
                'imageDestroyRoute' => '/admin/shop/shop-swiper-images',
                'imageReorderRoute' => 'admin.cms.pages.swiper-images.reorder',
                'textStoreRoute' => 'shop-texts.store',
                'textDestroyRoute' => '/admin/shop-texts',
                'pageCopyStoreRoute' => 'admin.shop.copy.store',
                'excludePrefixes' => ['shop_page_'],
                'pageCopyFields' => [
                    'title' => [
                        'sources' => ['shop_page_title'],
                        'fallback' => 'Shop Our Products',
                    ],
                    'intro' => [
                        'sources' => ['shop_page_intro'],
                        'fallback' => 'Farm-fresh produce and quality frozen goods delivered straight from our fields to your table',
                    ],
                ],
                'pageCopyMeta' => [
                    'title' => ['label' => 'Page Title'],
                    'intro' => ['label' => 'Page Intro', 'type' => 'textarea', 'rows' => 4, 'columnClass' => 'md:col-span-2'],
                ],
            ],
        ],
    ];

    public function __call($method, $args)
    {
        if (isset($this->pageConfigs[$method])) {
            return $this->showPage($method);
        }

        if ($method === 'items') {
            return $this->items();
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    private function showPage($page)
    {
        $config = $this->pageConfigs[$page];
        $imageModel = $config['imageModel'];
        $textModel = $config['textModel'];
        $view = $config['view'];
        $extraData = [];

        $images = $this->resolveCmsImages($page);
        $texts = $textModel::orderByDesc('id')
            ->get()
            ->unique(function ($item) {
                return strtolower(trim((string) ($item->label ?? $item->title ?? '')));
            })
            ->reverse()
            ->values();
        $pageCopyTexts = [];

        if ($page === 'whatToDo') {
            $mainPackages = Package::ordered()->get();
            $mainPackagesTotal = $mainPackages->count();
            $packages = $mainPackages;
            $extraData = compact('mainPackages', 'mainPackagesTotal', 'packages');
        } elseif ($page === 'whereToStay') {
            $pageCopyTexts = StaycationPageText::whereNotNull('label')->pluck('text', 'label')->mapWithKeys(function ($content, $field) {
                return [strtolower($field) => $content];
            })->toArray();
        } elseif ($page === 'ourFarm') {
            $farmSections = FarmPageSection::ensureDefaults()
                ->map(fn (FarmPageSection $section) => $section->adminPayload())
                ->values()
                ->all();
            $extraData = compact('farmSections');
        }

        $cmsConfig = $this->buildCmsConfig($page, $texts, $pageCopyTexts);
        $filteredTexts = $this->filterCmsTexts(
            $texts,
            $cmsConfig['excludeLabels'] ?? [],
            $cmsConfig['excludePrefixes'] ?? []
        );

        return view($view, compact('images', 'texts', 'filteredTexts', 'pageCopyTexts', 'cmsConfig') + $extraData);
    }

    public function reorderImages(Request $request, string $page)
    {
        if (!isset($this->pageConfigs[$page])) {
            abort(404);
        }

        $request->validate([
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer', 'distinct'],
        ]);

        $images = $this->resolveCmsImages($page);
        $currentIds = $images->pluck('id')->map(fn ($id) => (int) $id)->all();
        $orderedIds = array_values(array_unique(array_map('intval', $request->input('ordered_ids', []))));
        $orderedIds = array_values(array_intersect($orderedIds, $currentIds));
        $finalIds = array_merge($orderedIds, array_values(array_diff($currentIds, $orderedIds)));

        $imageModel = $this->pageConfigs[$page]['imageModel'];

        DB::transaction(function () use ($imageModel, $finalIds): void {
            foreach ($finalIds as $index => $imageId) {
                $imageModel::whereKey($imageId)->update([
                    'sort_order' => $index + 1,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Image order updated successfully.',
        ]);
    }

    private function buildCmsConfig(string $page, Collection $texts, array $pageCopyTexts = []): array
    {
        $cms = $this->pageConfigs[$page]['cms'] ?? [];
        $source = $page === 'whereToStay' ? $pageCopyTexts : $texts;

        $config = [
            'title' => $cms['title'] ?? 'Page Information',
            'description' => $cms['description'] ?? 'Manage the image slider and content displayed on this page.',
            'imageStoreRoute' => isset($cms['imageStoreRoute']) ? route($cms['imageStoreRoute']) : '',
            'imageDestroyRoute' => $cms['imageDestroyRoute'] ?? '',
            'textStoreRoute' => isset($cms['textStoreRoute']) ? route($cms['textStoreRoute']) : '',
            'textDestroyRoute' => $cms['textDestroyRoute'] ?? '',
            'pageCopyEnabled' => !empty($cms['pageCopyFields']),
            'pageCopyFields' => [],
            'pageCopyMeta' => $cms['pageCopyMeta'] ?? [],
            'pageCopyStoreRoute' => isset($cms['pageCopyStoreRoute']) ? route($cms['pageCopyStoreRoute']) : '',
            'imageReorderRoute' => isset($cms['imageReorderRoute']) ? route($cms['imageReorderRoute'], ['page' => $page]) : '',
            'excludeLabels' => $cms['excludeLabels'] ?? [],
            'excludePrefixes' => $cms['excludePrefixes'] ?? [],
        ];

        foreach (($cms['pageCopyFields'] ?? []) as $key => $definition) {
            $config['pageCopyFields'][$key] = $this->resolveCmsValue(
                $source,
                (array) ($definition['sources'] ?? []),
                $definition['fallback'] ?? ''
            );
        }

        return $config;
    }

    private function resolveCmsImages(string $page): Collection
    {
        $imageModel = $this->pageConfigs[$page]['imageModel'] ?? null;

        if ($imageModel === null) {
            return collect();
        }

        return match ($page) {
            'packages' => $imageModel::ordered()->get(),
            'dayTour' => $imageModel::whereNull('daytour_id')->ordered()->get(),
            'foodAndBeverage' => $imageModel::whereNull('food_id')->ordered()->get(),
            default => $imageModel::ordered()->get(),
        };
    }

    private function filterCmsTexts(Collection $texts, array $excludeLabels = [], array $excludePrefixes = []): Collection
    {
        $excludedLabels = collect($excludeLabels)
            ->map(fn ($label) => strtolower(trim((string) $label)))
            ->filter()
            ->values()
            ->all();
        $excludedPrefixes = collect($excludePrefixes)
            ->map(fn ($prefix) => strtolower(trim((string) $prefix)))
            ->filter()
            ->values()
            ->all();

        return $texts->reject(function ($item) use ($excludedLabels, $excludedPrefixes) {
            $label = strtolower(trim((string) ($item->label ?? $item->title ?? '')));
            if ($label === '') {
                return false;
            }

            if (in_array($label, $excludedLabels, true)) {
                return true;
            }

            foreach ($excludedPrefixes as $prefix) {
                if ($prefix !== '' && str_starts_with($label, $prefix)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    private function resolveCmsValue(Collection|array $source, array $labels, string $fallback = ''): string
    {
        if (is_array($source)) {
            foreach ($labels as $label) {
                $value = trim((string) ($source[strtolower((string) $label)] ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }

            return $fallback;
        }

        foreach ($labels as $label) {
            $match = $source->first(function ($item) use ($label) {
                $itemLabel = strtolower(trim((string) ($item->label ?? $item->title ?? '')));
                return $itemLabel === strtolower(trim((string) $label));
            });

            $value = trim((string) ($match->text ?? $match->content ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    public function items()
    {
        $sections = EventSection::orderBy('order')->get();
        return view('admin.home.items', compact('sections'));
    }
}
