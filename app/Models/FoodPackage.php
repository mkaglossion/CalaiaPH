<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodPackage extends Model
{
    protected $table = 'food_packages';

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'meal_type',
        'sort_order',
        'image',
        'sections',
        'is_active',
        'stock_quantity',
        'is_inventory_managed'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_inventory_managed' => 'boolean',
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'sort_order' => 'integer',
        'sections' => 'array',
    ];

    /**
     * Get the foods included in this package
     */
    public function foods()
    {
        return $this->belongsToMany(Food::class, 'food_package_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get package items (with quantity info)
     */
    public function packageItems()
    {
        return $this->hasMany(FoodPackageItem::class);
    }

    /**
     * Calculate total price of all items in package
     */
    public function calculateItemsTotal()
    {
        if ($this->hasStructuredSections()) {
            $foodPrices = $this->foods()
                ->select('foods.id', 'foods.price')
                ->get()
                ->keyBy('id');

            return collect($this->sections)
                ->sum(function ($section) use ($foodPrices) {
                    $mode = strtolower((string) ($section['mode'] ?? 'included'));
                    $choiceLimit = max(1, (int) ($section['choice_limit'] ?? 1));
                    $items = collect($section['items'] ?? [])
                        ->filter(fn ($item) => filled($item['food_id'] ?? null))
                        ->values();

                    if ($mode === 'choice') {
                        $items = $items->take($choiceLimit);
                    }

                    return $items->sum(function ($item) use ($foodPrices) {
                        $foodId = (int) ($item['food_id'] ?? 0);
                        $food = $foodPrices->get($foodId);
                        $quantity = max(1, (int) ($item['quantity'] ?? 1));

                        return ((float) ($food->price ?? 0)) * $quantity;
                    });
                });
        }

        return $this->foods()
            ->get()
            ->sum(function ($food) {
                return $food->price * $food->pivot->quantity;
            });
    }

    /**
     * Get effective price (package price or sum of items)
     */
    public function getEffectivePrice()
    {
        if ($this->hasStructuredSections()) {
            if (!empty($this->price) && (float) $this->price > 0) {
                return $this->price;
            }

            return $this->calculateItemsTotal();
        }

        return $this->price ?? $this->calculateItemsTotal();
    }

    /**
     * Get savings if package price is set
     */
    public function getSavings()
    {
        if (!$this->price) {
            return 0;
        }
        return max(0, $this->calculateItemsTotal() - $this->price);
    }

    public function hasStructuredSections(): bool
    {
        return is_array($this->sections) && !empty($this->sections);
    }

    public function getDisplayItemsCount(): int
    {
        if (!$this->hasStructuredSections()) {
            return (int) $this->foods()->count();
        }

        return collect($this->sections)
            ->flatMap(function ($section) {
                return collect($section['items'] ?? [])->filter(function ($item) {
                    return filled($item['food_id'] ?? null);
                });
            })
            ->count();
    }

    public function getStructuredSectionsAttribute(): array
    {
        return is_array($this->sections) ? $this->sections : [];
    }

    public function getFlatSectionFoodsAttribute()
    {
        if (!$this->hasStructuredSections()) {
            return $this->foods;
        }

        $foodIds = collect($this->sections)
            ->flatMap(fn ($section) => collect($section['items'] ?? [])->pluck('food_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return Food::select('id', 'name', 'price', 'category')
            ->whereIn('id', $foodIds)
            ->get();
    }
}
