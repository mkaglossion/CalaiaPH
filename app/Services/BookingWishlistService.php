<?php

namespace App\Services;

use App\Helpers\ImageHelper;
use App\Models\Activity;
use App\Models\ActivityPackage;
use App\Models\BookingWishlistItem;
use App\Models\Food;
use App\Models\FoodPackage;
use App\Models\Staycation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class BookingWishlistService
{
    public const TYPES = ['staycation', 'food', 'food_package', 'activity', 'activity_package'];

    private const MODELS = [
        'staycation' => Staycation::class,
        'food' => Food::class,
        'food_package' => FoodPackage::class,
        'activity' => Activity::class,
        'activity_package' => ActivityPackage::class,
    ];

    public function __construct(private PricingService $pricingService)
    {
    }

    public function currentContext(): array
    {
        $booking = session('booking', []);
        $checkin = $booking['checkin'] ?? null;
        $checkout = $booking['checkout'] ?? null;

        if (!$checkin || !$checkout) {
            throw ValidationException::withMessages([
                'booking_context' => 'Please select your check-in and check-out dates first.',
            ]);
        }

        try {
            $checkin = Carbon::parse($checkin)->toDateString();
            $checkout = Carbon::parse($checkout)->toDateString();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'booking_context' => 'Your booking dates are invalid. Please select them again.',
            ]);
        }

        if ($checkout <= $checkin) {
            throw ValidationException::withMessages([
                'booking_context' => 'Check-out must be after check-in.',
            ]);
        }

        return [
            'checkin' => $checkin,
            'checkout' => $checkout,
        ];
    }

    public function contextHash(array $context): string
    {
        return hash('sha256', ($context['checkin'] ?? '') . '|' . ($context['checkout'] ?? ''));
    }

    public function findActiveItem(string $type, int $id): Model
    {
        if (!isset(self::MODELS[$type])) {
            throw ValidationException::withMessages(['item_type' => 'Unsupported wishlist item.']);
        }

        $query = self::MODELS[$type]::query();
        if (in_array($type, ['food', 'food_package', 'activity', 'activity_package'], true)) {
            $query->where('is_active', true);
        }

        $model = $query->find($id);
        if (!$model) {
            throw ValidationException::withMessages(['item_id' => 'This booking item is no longer available.']);
        }

        return $model;
    }

    public function normalizeSelection(string $type, int $id, ?array $selection): array
    {
        $selection ??= [];

        if ($type === 'staycation') {
            $selectedId = (int) ($selection['room_id'] ?? $selection['id'] ?? 0);
            if ($selectedId > 0 && $selectedId !== $id) {
                throw ValidationException::withMessages([
                    'selection' => 'The selected room does not match this wishlist item.',
                ]);
            }

            $staycation = Staycation::findOrFail($id);
            $booking = session('booking', []);
            $roomsCount = max(1, (int) ($booking['rooms_count'] ?? 1));
            $defaultAdults = max(1, (int) ceil(((int) ($booking['adults_total'] ?? 1)) / $roomsCount));
            $defaultChildren = max(0, (int) floor(((int) ($booking['children_total'] ?? 0)) / $roomsCount));
            $name = strtolower((string) $staycation->name);
            $usesSlotQuantity = str_contains($name, 'byot')
                || str_contains($name, 'bring your own')
                || str_contains($name, 'car camping');
            $hasPets = in_array($selection['pets'] ?? false, [true, 1, '1', 'yes'], true);

            return [
                'room_id' => $id,
                'instance_num' => max(1, (int) ($selection['instance_num'] ?? 1)),
                'adults' => max(1, min(20, (int) ($selection['adults'] ?? $defaultAdults))),
                'children' => max(0, min(20, (int) ($selection['children'] ?? $defaultChildren))),
                'extra_mattress' => max(0, min(20, (int) ($selection['extra_mattress'] ?? 0))),
                'pets' => $hasPets,
                'pet_agreement' => (bool) ($selection['pet_agreement'] ?? false),
                'num_pets' => max(0, min(10, (int) ($selection['num_pets'] ?? 0))),
                'quantity' => max(1, min(20, (int) ($usesSlotQuantity
                    ? ($selection['extra_mattress'] ?? 1)
                    : ($selection['quantity'] ?? 1)))),
            ];
        }

        if ($type === 'food_package') {
            $package = FoodPackage::findOrFail($id);
            $choiceSections = collect($package->sections ?? [])->filter(
                fn ($section) => ($section['mode'] ?? 'included') === 'choice'
                    && !empty($section['items'])
            );
            $selectedChoices = array_values(array_filter(
                is_array($selection['selected_choices'] ?? null) ? $selection['selected_choices'] : [],
                'is_array'
            ));
            if ($choiceSections->count() > count($selectedChoices)) {
                throw ValidationException::withMessages([
                    'selection' => 'Add this meal package and complete its required choices before saving it.',
                ]);
            }

            return [
                'quantity' => max(1, min(20, (int) ($selection['quantity'] ?? 1))),
                'selected_choices' => $selectedChoices,
            ];
        }

        if ($type === 'activity') {
            return ['guests' => max(1, min(20, (int) ($selection['guests'] ?? $selection['quantity'] ?? 1)))];
        }

        return ['quantity' => max(1, min(20, (int) ($selection['quantity'] ?? 1)))];
    }

    public function present(BookingWishlistItem|array $saved): array
    {
        $type = is_array($saved) ? $saved['item_type'] : $saved->item_type;
        $id = (int) (is_array($saved) ? $saved['item_id'] : $saved->item_id);
        $selection = is_array($saved) ? ($saved['selection'] ?? []) : ($saved->selection ?? []);
        $context = is_array($saved) ? ($saved['booking_context'] ?? []) : ($saved->booking_context ?? []);
        $model = $this->findActiveItem($type, $id);

        $name = (string) $model->name;
        $image = $model->image ?? null;
        if (!$image && method_exists($model, 'swiperImages')) {
            $first = $model->swiperImages()->first();
            $image = $first?->image_path ?? $first?->image_url;
        }

        $price = match ($type) {
            'staycation' => $this->calculateStaycationPrice($model, $context, $selection),
            'food_package', 'activity_package' => (float) $model->getEffectivePrice(),
            default => (float) ($model->price ?? 0),
        };
        $multiplier = match ($type) {
            'staycation' => 1,
            'activity' => (int) ($selection['guests'] ?? 1),
            default => (int) ($selection['quantity'] ?? 1),
        };

        return [
            'record_id' => is_array($saved) ? ($saved['record_id'] ?? null) : $saved->id,
            'key' => $type . ':' . $id,
            'item_type' => $type,
            'item_id' => $id,
            'type_label' => match ($type) {
                'staycation' => 'Room / Tent',
                'food' => 'Meal',
                'food_package' => 'Meal Package',
                'activity' => 'Activity',
                'activity_package' => 'Activity Package',
            },
            'name' => $name,
            'image' => ImageHelper::src($image ?: ImageHelper::placeholder()),
            'unit_price' => $price,
            'quantity' => max(1, $multiplier),
            'max_quantity' => $type === 'staycation' ? 1 : $this->maxPurchasableQuantity($model),
            'line_total' => $price * max(1, $multiplier),
            'selection' => $selection,
            'booking_context' => $context,
            'price_label' => $type === 'staycation' ? 'Estimated stay total' : null,
        ];
    }

    public function maxPurchasableQuantity(Model $model): int
    {
        if ((bool) ($model->is_inventory_managed ?? false)) {
            return max(0, min(20, (int) ($model->stock_quantity ?? 0)));
        }

        return 20;
    }

    private function calculateStaycationPrice(Staycation $staycation, array $context, array $selection): float
    {
        $checkin = Carbon::parse($context['checkin'] ?? now()->toDateString())->startOfDay();
        $checkout = Carbon::parse($context['checkout'] ?? $checkin->copy()->addDay())->startOfDay();
        if ($checkout->lte($checkin)) {
            $checkout = $checkin->copy()->addDay();
        }

        $name = strtolower((string) $staycation->name);
        $isSpecialRoom = str_contains($name, 'byot')
            || str_contains($name, 'bring your own')
            || str_contains($name, 'car camping');

        if ($isSpecialRoom) {
            $rate = $this->resolveStaycationNightlyRate($staycation, $checkin);
            $guests = max(1, (int) ($selection['adults'] ?? 1) + (int) ($selection['children'] ?? 0));

            return $rate * $guests;
        }

        $total = 0.0;
        for ($date = $checkin->copy(); $date->lt($checkout); $date->addDay()) {
            $total += $this->resolveStaycationNightlyRate($staycation, $date);
        }

        return $total;
    }

    private function resolveStaycationNightlyRate(Staycation $staycation, Carbon $date): float
    {
        $seasonName = $this->pricingService->getSeasonType($date) ?? 'Non-Peak';
        $seasonKey = match (strtolower(trim($seasonName))) {
            'super-peak', 'super peak', 'superpeak' => 'superPeak',
            'peak' => 'peak',
            default => 'nonPeak',
        };

        $rate = $this->pricingService->getStaycationRate($staycation, $date, $seasonKey);
        if ($rate > 0) {
            return (float) $rate;
        }

        $type = strtolower((string) $staycation->type);
        $isGlamping = $type === 'glamping'
            || str_contains(strtolower((string) $staycation->name), 'glamping')
            || str_contains(strtolower((string) $staycation->name), 'dome')
            || str_contains(strtolower((string) $staycation->name), 'bell');

        return $isGlamping ? 5500.0 : 3000.0;
    }
}
