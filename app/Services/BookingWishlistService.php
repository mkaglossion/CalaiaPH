<?php

namespace App\Services;

use App\Helpers\ImageHelper;
use App\Models\Activity;
use App\Models\ActivityPackage;
use App\Models\BookingSetting;
use App\Models\BookingWishlistItem;
use App\Models\Food;
use App\Models\FoodPackage;
use App\Models\Staycation;
use App\Models\StaycationInventory;
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
            $usesSlotQuantity = $this->isSpecialStaycation($staycation);
            $hasPets = in_array($selection['pets'] ?? false, [true, 1, '1', 'yes'], true);
            $petAgreement = $hasPets && (bool) ($selection['pet_agreement'] ?? false);
            $numPets = $hasPets
                ? max(0, min($this->maxPetsPerRoom(), (int) ($selection['num_pets'] ?? 0)))
                : 0;

            return [
                'room_id' => $id,
                'instance_num' => max(1, (int) ($selection['instance_num'] ?? 1)),
                'adults' => max(1, min(20, (int) ($selection['adults'] ?? 1))),
                'children' => max(0, min(20, (int) ($selection['children'] ?? 0))),
                'extra_mattress' => max(0, min(20, (int) ($selection['extra_mattress'] ?? 0))),
                'pets' => $hasPets,
                'pet_agreement' => $petAgreement,
                'num_pets' => $numPets,
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

        $staycationPricing = $type === 'staycation'
            ? $this->calculateStaycationPricing($model, $context, $selection)
            : null;
        $price = match ($type) {
            'staycation' => $staycationPricing['total_price'],
            'food_package', 'activity_package' => (float) $model->getEffectivePrice(),
            default => (float) ($model->price ?? 0),
        };
        $isSpecialStaycation = $type === 'staycation' && $this->isSpecialStaycation($model);
        $multiplier = match ($type) {
            'staycation' => $isSpecialStaycation ? 1 : (int) ($selection['quantity'] ?? 1),
            'activity' => (int) ($selection['guests'] ?? 1),
            default => (int) ($selection['quantity'] ?? 1),
        };
        $multiplier = max(1, $multiplier);
        $priceBreakdown = $staycationPricing ? [
            'room_quantity' => $multiplier,
            'accommodation_total' => $staycationPricing['accommodation_price'] * $multiplier,
            'additional_adults_per_room' => $staycationPricing['additional_adults'],
            'additional_adult_total' => $staycationPricing['additional_adult_price'] * $multiplier,
            'pets_per_room' => $staycationPricing['num_pets'],
            'pet_total' => $staycationPricing['pet_price'] * $multiplier,
            'total' => $staycationPricing['total_price'] * $multiplier,
        ] : null;

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
            'quantity' => $multiplier,
            'max_quantity' => $type === 'staycation'
                ? $this->maxStaycationQuantity($model, $context)
                : $this->maxPurchasableQuantity($model),
            'line_total' => $price * $multiplier,
            'selection' => $selection,
            'booking_context' => $context,
            'price_label' => $type === 'staycation' ? 'Estimated stay total' : null,
            'is_special_staycation' => $isSpecialStaycation,
            'guest_limits' => $type === 'staycation' ? $this->staycationGuestLimits($model) : null,
            'max_pets_per_room' => $type === 'staycation' ? $this->maxPetsPerRoom() : null,
            'price_breakdown' => $priceBreakdown,
        ];
    }

    public function isSpecialStaycation(Staycation $staycation): bool
    {
        $name = strtolower((string) $staycation->name);

        return str_contains($name, 'byot')
            || str_contains($name, 'bring your own')
            || str_contains($name, 'car camping');
    }

    public function maxPetsPerRoom(): int
    {
        return max(1, (int) BookingSetting::getValue('max_pets_per_room', 2));
    }

    public function staycationGuestLimits(Staycation $staycation): array
    {
        if ($this->isSpecialStaycation($staycation)) {
            return ['adults' => 20, 'children' => 20, 'occupancy' => 20];
        }

        $explicitIncluded = max(0, (int) ($staycation->included_guest_capacity ?? 0));
        $configuredAdults = max(0, (int) ($staycation->max_guest_capacity ?? 0));
        $legacyAdults = max(0, (int) ($staycation->adults ?? 0));
        $children = max(0, (int) ($staycation->children ?? 0));
        $legacyCapacity = max(0, (int) ($staycation->capacity ?? 0));
        $included = $explicitIncluded > 0
            ? $explicitIncluded
            : ($legacyAdults > 0 ? $legacyAdults : max(1, $legacyCapacity));
        $derivedMaximum = max($included, $legacyAdults + $children, $legacyCapacity);
        $adults = $configuredAdults > 0
            ? max($included, $configuredAdults)
            : $derivedMaximum;
        $occupancy = $configuredAdults > 0
            ? $adults + $children
            : $derivedMaximum;

        return [
            'adults' => max(1, min(20, $adults)),
            'children' => max(0, min(20, $children > 0 ? $children : $occupancy - 1)),
            'occupancy' => max(1, min(20, $occupancy)),
        ];
    }

    public function maxStaycationQuantity(Staycation $staycation, array $context): int
    {
        try {
            $checkin = Carbon::parse($context['checkin'] ?? null)->startOfDay();
            $checkout = Carbon::parse($context['checkout'] ?? null)->startOfDay();
        } catch (\Throwable) {
            return 0;
        }

        if ($checkout->lte($checkin)) {
            return 0;
        }

        $inventory = StaycationInventory::query()
            ->where('staycation_id', $staycation->id)
            ->whereDate('date', '>=', $checkin->toDateString())
            ->whereDate('date', '<', $checkout->toDateString())
            ->get()
            ->keyBy(fn (StaycationInventory $item) => Carbon::parse($item->date)->toDateString());
        $fallback = max(0, (int) ($staycation->base_quantity ?? 0));
        $minimum = null;

        for ($date = $checkin->copy(); $date->lt($checkout); $date->addDay()) {
            $record = $inventory->get($date->toDateString());
            $available = $record
                ? ($record->is_blocked ? 0 : max(0, (int) $record->available_quantity))
                : $fallback;
            $minimum = $minimum === null ? $available : min($minimum, $available);
        }

        return min(20, max(0, (int) $minimum));
    }

    public function maxPurchasableQuantity(Model $model): int
    {
        if ((bool) ($model->is_inventory_managed ?? false)) {
            return max(0, min(20, (int) ($model->stock_quantity ?? 0)));
        }

        return 20;
    }

    private function calculateStaycationPricing(Staycation $staycation, array $context, array $selection): array
    {
        $checkin = Carbon::parse($context['checkin'] ?? now()->toDateString())->startOfDay();
        $checkout = Carbon::parse($context['checkout'] ?? $checkin->copy()->addDay())->startOfDay();
        if ($checkout->lte($checkin)) {
            $checkout = $checkin->copy()->addDay();
        }

        $isSpecialRoom = $this->isSpecialStaycation($staycation);
        $nights = max(1, $checkin->diffInDays($checkout));
        $adults = max(1, (int) ($selection['adults'] ?? 1));
        $children = max(0, (int) ($selection['children'] ?? 0));
        $accommodationPrice = 0.0;

        if ($isSpecialRoom) {
            $rate = $this->resolveStaycationNightlyRate($staycation, $checkin);
            $accommodationPrice = $rate * max(1, $adults + $children);
            $nights = 1;
        } else {
            for ($date = $checkin->copy(); $date->lt($checkout); $date->addDay()) {
                $accommodationPrice += $this->resolveStaycationNightlyRate($staycation, $date);
            }
        }

        $additionalAdults = $this->resolveAdditionalAdultCount($staycation, $adults);
        $additionalAdultPrice = $additionalAdults
            * (float) BookingSetting::getValue('mattress_price_per_night', 1200)
            * $nights;
        $hasPets = in_array($selection['pets'] ?? false, [true, 1, '1', 'yes'], true);
        $numPets = !$isSpecialRoom && $hasPets
            ? max(0, (int) ($selection['num_pets'] ?? 0))
            : 0;
        $petPrice = $numPets * (float) BookingSetting::getValue('pet_price', 0);

        return [
            'accommodation_price' => $accommodationPrice,
            'additional_adults' => $additionalAdults,
            'additional_adult_price' => $additionalAdultPrice,
            'num_pets' => $numPets,
            'pet_price' => $petPrice,
            'total_price' => $accommodationPrice + $additionalAdultPrice + $petPrice,
        ];
    }

    private function supportsExtraMattress(Staycation $staycation): bool
    {
        if ($this->isSpecialStaycation($staycation)) {
            return false;
        }

        $type = strtolower((string) ($staycation->type ?? ''));
        $name = strtolower((string) ($staycation->name ?? ''));

        return $type === 'glamping'
            || str_contains($name, 'glamping')
            || str_contains($name, 'dome')
            || str_contains($name, 'bell');
    }

    private function resolveIncludedGuestCapacity(Staycation $staycation): int
    {
        $included = max(0, (int) ($staycation->included_guest_capacity ?? 0));
        if ($included > 0) {
            return $included;
        }

        $adults = max(0, (int) ($staycation->adults ?? 0));
        if ($adults > 0) {
            return $adults;
        }

        return max(1, (int) ($staycation->capacity ?? 0));
    }

    private function resolveMaxGuestCapacity(Staycation $staycation): int
    {
        $included = $this->resolveIncludedGuestCapacity($staycation);
        $configured = max(0, (int) ($staycation->max_guest_capacity ?? 0));
        if ($configured > 0) {
            return max($included, $configured);
        }

        $derived = max(0, (int) ($staycation->adults ?? 0))
            + max(0, (int) ($staycation->children ?? 0));
        if ($derived > 0) {
            return max($included, $derived);
        }

        $capacity = max(0, (int) ($staycation->capacity ?? 0));
        if ($capacity > 0) {
            return max($included, $capacity);
        }

        return $this->supportsExtraMattress($staycation) ? $included + 2 : $included;
    }

    private function resolveAdditionalAdultCount(Staycation $staycation, int $adults): int
    {
        if (!$this->supportsExtraMattress($staycation)) {
            return 0;
        }

        $included = $this->resolveIncludedGuestCapacity($staycation);
        $maximumAdditional = max(0, $this->resolveMaxGuestCapacity($staycation) - $included);

        return min($maximumAdditional, max(0, max(1, $adults) - $included));
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
