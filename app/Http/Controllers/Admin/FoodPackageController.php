<?php

namespace App\Http\Controllers\Admin;

use App\Models\Food;
use App\Models\FoodPackage;
use App\Models\FoodPackageSetting;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\ValidationException;

class FoodPackageController extends Controller
{
    private function ensurePackageOrderingColumns(): void
    {
        if (!Schema::hasColumn('food_packages', 'meal_type')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->string('meal_type', 20)->default('all_day')->after('category');
                $table->index('meal_type');
            });
        }

        if (!Schema::hasColumn('food_packages', 'sort_order')) {
            Schema::table('food_packages', function (Blueprint $table) {
                $table->integer('sort_order')->default(1)->after('meal_type');
                $table->index('sort_order');
            });
        }
    }

    protected const CATEGORIES = [
        'messhall' => 'Messhall',
        'overnight_stay_menu' => 'Overnight Stay Menu',
        'cook_your_own' => 'Cook Your Own',
        'special' => 'Special',
    ];

    /**
     * Get validation rules for food packages
     */
    private function getValidationRules(Request $request): array
    {
        $isStructuredPackage = strtolower((string) $request->input('category', '')) === 'overnight_stay_menu';

        $baseRules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:messhall,overnight_stay_menu,cook_your_own,special',
            'meal_type' => 'nullable|string|in:breakfast,lunch,dinner,all_day',
            'sort_order' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
            'remove_image' => 'nullable|in:1',
            'is_active' => 'boolean',
            'is_inventory_managed' => 'boolean',
            'stock_quantity' => 'required_if:is_inventory_managed,true|nullable|integer|min:1',
        ];

        if ($isStructuredPackage) {
            $baseRules['sections'] = 'required|array|min:1';
            $baseRules['sections.*.title'] = 'required|string|max:255';
            $baseRules['sections.*.mode'] = 'nullable|string|in:included,choice';
            $baseRules['sections.*.note'] = 'nullable|string|max:255';
            $baseRules['sections.*.choice_limit'] = 'nullable|integer|min:1|max:20';
            $baseRules['sections.*.items'] = 'required|array|min:1';
            $baseRules['sections.*.items.*.food_id'] = 'required|integer|exists:foods,id';
            $baseRules['sections.*.items.*.quantity'] = 'nullable|integer|min:1|max:20';
            $baseRules['foods'] = 'nullable|array';
            $baseRules['quantities'] = 'nullable|array';
        } else {
            $baseRules['foods'] = 'required|array|min:1';
            $baseRules['foods.*'] = 'integer|exists:foods,id';
            $baseRules['quantities'] = 'required|array';
            $baseRules['quantities.*'] = 'integer|min:1';
        }

        return $baseRules;
    }

    private function normalizePackageSections(array $sections): array
    {
        return collect($sections)
            ->filter(fn ($section) => is_array($section))
            ->map(function (array $section) {
                return [
                    'title' => trim((string) ($section['title'] ?? '')),
                    'mode' => in_array(($section['mode'] ?? 'included'), ['included', 'choice'], true) ? $section['mode'] : 'included',
                    'note' => trim((string) ($section['note'] ?? '')),
                    'choice_limit' => max(1, (int) ($section['choice_limit'] ?? 1)),
                    'items' => collect($section['items'] ?? [])
                        ->filter(fn ($item) => is_array($item))
                        ->map(function (array $item) {
                            return [
                                'food_id' => (int) ($item['food_id'] ?? 0),
                                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                            ];
                        })
                        ->filter(fn ($item) => $item['food_id'] > 0)
                        ->values()
                        ->all(),
                ];
            })
            ->filter(function (array $section) {
                return $section['title'] !== '' && !empty($section['items']);
            })
            ->values()
            ->all();
    }

    private function getSectionFoods(array $sections): array
    {
        return collect($sections)
            ->flatMap(fn ($section) => collect($section['items'] ?? [])->pluck('food_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function calculateFlatPackageTotal(array $validated): float
    {
        $foodIds = array_values(array_filter(array_map('intval', $validated['foods'] ?? [])));
        if (empty($foodIds)) {
            return 0;
        }

        $foods = Food::select('id', 'price')
            ->whereIn('id', $foodIds)
            ->get()
            ->keyBy('id');

        $total = 0;
        foreach (($validated['foods'] ?? []) as $index => $foodId) {
            $foodId = (int) $foodId;
            if (!isset($foods[$foodId])) {
                continue;
            }

            $quantity = max(1, (int) ($validated['quantities'][$index] ?? 1));
            $total += ((float) $foods[$foodId]->price) * $quantity;
        }

        return $total;
    }

    private function calculateStructuredPackageTotal(array $sections): float
    {
        $foodIds = collect($sections)
            ->flatMap(function ($section) {
                $mode = strtolower((string) ($section['mode'] ?? 'included'));
                $choiceLimit = max(1, (int) ($section['choice_limit'] ?? 1));
                $items = collect($section['items'] ?? [])
                    ->filter(fn ($item) => is_array($item) && filled($item['food_id'] ?? null))
                    ->values();

                if ($mode === 'choice') {
                    $items = $items->take($choiceLimit);
                }

                return $items->pluck('food_id');
            })
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($foodIds)) {
            return 0;
        }

        $foods = Food::select('id', 'price')
            ->whereIn('id', $foodIds)
            ->get()
            ->keyBy('id');

        return collect($sections)
            ->sum(function (array $section) use ($foods) {
                $mode = strtolower((string) ($section['mode'] ?? 'included'));
                $choiceLimit = max(1, (int) ($section['choice_limit'] ?? 1));
                $items = collect($section['items'] ?? [])
                    ->filter(fn ($item) => is_array($item) && filled($item['food_id'] ?? null))
                    ->values();

                if ($mode === 'choice') {
                    $items = $items->take($choiceLimit);
                }

                return $items->sum(function (array $item) use ($foods) {
                    $foodId = (int) ($item['food_id'] ?? 0);
                    $food = $foods[$foodId] ?? null;
                    $quantity = max(1, (int) ($item['quantity'] ?? 1));

                    return ((float) ($food->price ?? 0)) * $quantity;
                });
            });
    }

    private function syncPackageFoods(FoodPackage $package, array $validated): void
    {
        $category = strtolower((string) ($validated['category'] ?? $package->category ?? ''));
        $sections = $validated['sections'] ?? [];

        if ($category === 'overnight_stay_menu' && !empty($sections)) {
            $syncData = [];
            foreach ($this->getSectionFoods($sections) as $foodId) {
                $syncData[$foodId] = ['quantity' => 1];
            }

            $package->foods()->sync($syncData);
            return;
        }

        $this->syncPackageFoodsLegacy($package, $validated);
    }

    private function syncPackageFoodsLegacy(FoodPackage $package, array $validated): void
    {
        $syncData = [];
        foreach (($validated['foods'] ?? []) as $index => $foodId) {
            $syncData[$foodId] = [
                'quantity' => ($validated['quantities'][$index] ?? 1),
            ];
        }

        $package->foods()->sync($syncData);
    }

    /**
     * Transform a food package into a JSON-friendly payload
     */
    private function packageToArray(FoodPackage $package): array
    {
        $package->load(['foods' => function($q) {
            $q->select('foods.id', 'foods.name', 'foods.price');
        }]);

        return [
            'id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'category' => $package->category,
            'meal_type' => $package->meal_type,
            'sort_order' => $package->sort_order,
            'image' => $package->image,
            'sections' => $package->sections ?? [],
            'price' => $package->price,
            'effective_price' => $package->getEffectivePrice(),
            'is_active' => $package->is_active,
            'is_inventory_managed' => $package->is_inventory_managed,
            'stock_quantity' => $package->stock_quantity,
            'foods' => $package->foods->map(function($food) {
                return [
                    'id' => $food->id,
                    'name' => $food->name,
                    'quantity' => $food->pivot->quantity,
                    'price' => $food->price,
                ];
            })->values(),
        ];
    }

    /**
     * Get data for form views
     */
    private function getFormData(): array
    {
        return [
            'categories' => FoodPackageSetting::getPackageCategoryLabels(),
            'foods' => Food::select('id', 'name', 'category', 'price', 'is_active')
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Display a listing of food packages
     */
    public function index(Request $request)
    {
        $this->ensurePackageOrderingColumns();

        if ($request->query('api') === '1') {
            return $this->apiIndex($request);
        }

        return redirect()->route('admin.foods.index')
            ->with('info', 'Food packages are managed from the Food Management page.');
    }

    /**
     * Show the form for creating a new package
     */
    public function create()
    {
        return redirect()->route('admin.foods.index')
            ->with('info', 'Create packages from the Food Management page.');
    }

    /**
     * Store a newly created package in database
     */
    public function store(Request $request)
    {
        $this->ensurePackageOrderingColumns();

        try {
            $sections = $request->input('sections', []);
            if (is_string($sections)) {
                $sections = json_decode($sections, true) ?: [];
            }
            if (is_array($sections)) {
                $request->merge(['sections' => $sections]);
            }

            $validated = $request->validate($this->getValidationRules($request));

            $structuredSections = [];
            if (($validated['category'] ?? '') === 'overnight_stay_menu') {
                $structuredSections = $this->normalizePackageSections($validated['sections'] ?? []);

                if (empty($structuredSections)) {
                    throw ValidationException::withMessages([
                        'sections' => 'Overnight Stay Menu packages need at least one section.',
                    ]);
                }
            }

            $imageUrl = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $uploadResult = Cloudinary::uploadApi()->upload($file->getRealPath(), ['resource_type' => 'auto']);
                $imageUrl = $uploadResult['secure_url'];
            }

            $price = $validated['price'] ?? null;
            if (($price === null || $price === '') && $validated['category'] !== 'overnight_stay_menu') {
                $price = $this->calculateFlatPackageTotal($validated);
            } elseif (($price === null || $price === '') && !empty($structuredSections)) {
                $price = $this->calculateStructuredPackageTotal($structuredSections);
            } elseif ($price === null || $price === '') {
                $price = 0;
            }

            $package = FoodPackage::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'meal_type' => $validated['meal_type'] ?? 'all_day',
                'sort_order' => $validated['sort_order'] ?? 1,
                'price' => $price,
                'image' => $imageUrl,
                'sections' => !empty($structuredSections) ? $structuredSections : null,
                'is_active' => $request->boolean('is_active', true),
                'is_inventory_managed' => $request->boolean('is_inventory_managed', false),
                'stock_quantity' => $validated['stock_quantity'] ?? 999,
            ]);

            $this->syncPackageFoods($package, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Package created successfully!',
                    'package' => $this->packageToArray($package),
                ], 201);
            }

            return redirect()->route('admin.foods.index')
                ->with('success', 'Package created successfully!');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified package
     */
    public function show(FoodPackage $package)
    {
        $package->load(['foods' => function($q) {
            $q->select('foods.id', 'foods.name', 'foods.price', 'foods.image');
        }]);

        return view('admin.food.packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit(FoodPackage $package)
    {
        if (request()->expectsJson()) {
            return response()->json($this->packageToArray($package));
        }

        return redirect()->route('admin.foods.index')
            ->with('info', 'Manage packages from the Food Management page.');
    }

    /**
     * Update the specified package in database
     */
    public function update(Request $request, FoodPackage $package)
    {
        $this->ensurePackageOrderingColumns();

        try {
            $sections = $request->input('sections', []);
            if (is_string($sections)) {
                $sections = json_decode($sections, true) ?: [];
            }
            if (is_array($sections)) {
                $request->merge(['sections' => $sections]);
            }

            $validated = $request->validate($this->getValidationRules($request));

            $structuredSections = [];
            if (($validated['category'] ?? '') === 'overnight_stay_menu') {
                $structuredSections = $this->normalizePackageSections($validated['sections'] ?? []);

                if (empty($structuredSections)) {
                    throw ValidationException::withMessages([
                        'sections' => 'Overnight Stay Menu packages need at least one section.',
                    ]);
                }
            }

            $price = $validated['price'] ?? '';
            if ($price === '' && ($validated['category'] ?? '') !== 'overnight_stay_menu') {
                $price = $this->calculateFlatPackageTotal($validated);
            } elseif ($price === '' && !empty($structuredSections)) {
                $price = $this->calculateStructuredPackageTotal($structuredSections);
            } elseif ($price === '') {
                $price = 0;
            }

            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'meal_type' => $validated['meal_type'] ?? 'all_day',
                'sort_order' => $validated['sort_order'] ?? 1,
                'price' => $price,
                'sections' => !empty($structuredSections) ? $structuredSections : null,
                'is_active' => $request->boolean('is_active', true),
                'is_inventory_managed' => $request->boolean('is_inventory_managed', false),
                'stock_quantity' => $validated['stock_quantity'] ?? 999,
            ];

            if ($request->boolean('remove_image')) {
                $updateData['image'] = null;
            } elseif ($request->hasFile('image')) {
                $file = $request->file('image');
                $uploadResult = Cloudinary::uploadApi()->upload($file->getRealPath(), ['resource_type' => 'auto']);
                $updateData['image'] = $uploadResult['secure_url'];
            }

            $package->update($updateData);

            $this->syncPackageFoods($package, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Package updated successfully!',
                    'package' => $this->packageToArray($package),
                ]);
            }

            return redirect()->route('admin.foods.index')
                ->with('success', 'Package updated successfully!');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified package from database
     */
    public function destroy(FoodPackage $package)
    {
        $package->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Package deleted successfully!',
            ]);
        }

        return redirect()->route('admin.foods.index')
            ->with('success', 'Package deleted successfully!');
    }

    /**
     * Get package details for API (used in booking page)
     */
    public function getPackageDetails(FoodPackage $package)
    {
        $packageData = $this->packageToArray($package);
        $packageData['savings'] = $package->getSavings();

        return response()->json($packageData);
    }

    /**
     * API endpoint for packages list (used in admin foods page)
     */
    public function apiIndex(Request $request)
    {
        $this->ensurePackageOrderingColumns();

        $packagesQuery = FoodPackage::with(['foods' => function($q) {
            $q->select('foods.id', 'foods.name', 'foods.price');
        }])->withCount('foods')
            ->orderBy('category');

        if (Schema::hasColumn('food_packages', 'sort_order')) {
            $packagesQuery->orderBy('sort_order');
        }

        $packages = $packagesQuery->orderBy('name')->get();

        $packagesData = $packages->map(function($package) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'category' => $package->category,
                'meal_type' => $package->meal_type,
                'sort_order' => $package->sort_order,
                'image' => $package->image,
                'sections' => $package->sections ?? [],
                'effective_price' => $package->getEffectivePrice(),
                'is_active' => $package->is_active,
                'foods_count' => $package->hasStructuredSections() ? $package->getDisplayItemsCount() : $package->foods_count,
                'edit_url' => route('admin.food.packages.edit', $package),
                'delete_url' => route('admin.food.packages.destroy', $package),
            ];
        });

        return response()->json(['packages' => $packagesData]);
    }
}
