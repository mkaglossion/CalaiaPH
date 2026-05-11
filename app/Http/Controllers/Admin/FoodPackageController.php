<?php

namespace App\Http\Controllers\Admin;

use App\Models\Food;
use App\Models\FoodPackage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

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
        'cook_your_own' => 'Cook Your Own',
        'special' => 'Special',
    ];

    /**
     * Get validation rules for food packages
     */
    private function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:messhall,cook_your_own,special',
            'meal_type' => 'nullable|string|in:breakfast,lunch,dinner,all_day',
            'sort_order' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_inventory_managed' => 'boolean',
            'stock_quantity' => 'required_if:is_inventory_managed,true|nullable|integer|min:1',
            'foods' => 'required|array|min:1',
            'foods.*' => 'integer|exists:foods,id',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:1',
        ];
    }

    /**
     * Sync package foods with quantities
     */
    private function syncPackageFoods(FoodPackage $package, array $validated): void
    {
        $syncData = [];
        foreach ($validated['foods'] as $index => $foodId) {
            $syncData[$foodId] = [
                'quantity' => $validated['quantities'][$index] ?? 1,
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
            'categories' => self::CATEGORIES,
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

        $validated = $request->validate($this->getValidationRules());

        $package = FoodPackage::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'meal_type' => $validated['meal_type'] ?? 'all_day',
            'sort_order' => $validated['sort_order'] ?? 1,
            'price' => $validated['price'],
            'is_active' => $request->has('is_active'),
            'is_inventory_managed' => $request->has('is_inventory_managed'),
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

        $validated = $request->validate($this->getValidationRules());

        $package->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'meal_type' => $validated['meal_type'] ?? 'all_day',
            'sort_order' => $validated['sort_order'] ?? 1,
            'price' => $validated['price'],
            'is_active' => $request->has('is_active'),
            'is_inventory_managed' => $request->has('is_inventory_managed'),
            'stock_quantity' => $validated['stock_quantity'] ?? 999,
        ]);

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

        if (Schema::hasColumn('food_packages', 'meal_type') && Schema::hasColumn('food_packages', 'sort_order')) {
            $packagesQuery
                ->orderByRaw("CASE meal_type WHEN 'breakfast' THEN 1 WHEN 'lunch' THEN 2 WHEN 'dinner' THEN 3 ELSE 4 END")
                ->orderBy('sort_order');
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
                'effective_price' => $package->getEffectivePrice(),
                'is_active' => $package->is_active,
                'foods_count' => $package->foods_count,
                'edit_url' => route('admin.food.packages.edit', $package),
                'delete_url' => route('admin.food.packages.destroy', $package),
            ];
        });

        return response()->json(['packages' => $packagesData]);
    }
}
