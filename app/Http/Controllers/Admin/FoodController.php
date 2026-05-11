<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Models\FoodSwiperImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class FoodController extends Controller
{
    private const SORT_OPTIONS = [
        'featured' => 'Featured',
        'name_asc' => 'Alphabetically, A-Z',
        'name_desc' => 'Alphabetically, Z-A',
        'price_asc' => 'Price, low to high',
        'price_desc' => 'Price, high to low',
        'oldest' => 'Date, old to new',
        'newest' => 'Date, new to old',
    ];

    private function ensureFoodOrderingColumns(): void
    {
        if (!Schema::hasColumn('foods', 'meal_type')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->string('meal_type', 20)->default('all_day')->after('subcategory');
                $table->index('meal_type');
            });
        }

        if (!Schema::hasColumn('foods', 'sort_order')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->integer('sort_order')->default(1)->after('meal_type');
                $table->index('sort_order');
            });
        }
    }

    private function hasFoodColumn(string $column): bool
    {
        static $columnCache = [];

        if (!array_key_exists($column, $columnCache)) {
            $columnCache[$column] = Schema::hasColumn('foods', $column);
        }

        return $columnCache[$column];
    }

    public function index(Request $request)
    {
        $this->ensureFoodOrderingColumns();

        // Handle API requests
        if ($request->query('api') === '1') {
            return $this->apiIndex($request);
        }

        $sortBy = $request->string('sort_by', 'featured')->toString();
        if (!array_key_exists($sortBy, self::SORT_OPTIONS)) {
            $sortBy = 'featured';
        }

        $foodsQuery = Food::query();
        switch ($sortBy) {
            case 'name_asc':
                $foodsQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $foodsQuery->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $foodsQuery->orderBy('price', 'asc')->orderBy('name', 'asc');
                break;
            case 'price_desc':
                $foodsQuery->orderBy('price', 'desc')->orderBy('name', 'asc');
                break;
            case 'oldest':
                $foodsQuery->orderBy('created_at', 'asc')->orderBy('name', 'asc');
                break;
            case 'newest':
                $foodsQuery->orderBy('created_at', 'desc')->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $foodsQuery->orderBy('category');
                if (Schema::hasColumn('foods', 'meal_type') && Schema::hasColumn('foods', 'sort_order')) {
                    $foodsQuery
                        ->orderByRaw("CASE meal_type WHEN 'breakfast' THEN 1 WHEN 'lunch' THEN 2 WHEN 'dinner' THEN 3 ELSE 4 END")
                        ->orderBy('sort_order');
                }
                $foodsQuery->orderBy('name');
                break;
        }

        $foods = $foodsQuery->paginate(10)->appends(['sort_by' => $sortBy]);
        $sortOptions = self::SORT_OPTIONS;

        return view('admin.foods.index', compact('foods', 'sortBy', 'sortOptions'));
    }

    public function create()
    {
        return view('admin.foods.form');
    }

    public function edit(Food $food)
    {
        return view('admin.foods.form', compact('food'));
    }

    public function show(Food $food)
    {
        return response()->json($food);
    }

    public function store(Request $request)
    {
        $this->ensureFoodOrderingColumns();

        try {
            $rules = [
                'name' => 'required|string|max:255|unique:foods,name',
                'category' => 'required|string|in:messhall,cook_your_own',
                'subcategory' => 'nullable|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'nullable|boolean',
                'is_special_option' => 'nullable|boolean',
                'stock_quantity' => 'nullable|integer|min:0',
                'is_inventory_managed' => 'nullable|boolean',
            ];

            if ($this->hasFoodColumn('meal_type')) {
                $rules['meal_type'] = 'nullable|string|in:breakfast,lunch,dinner,all_day';
            }

            if ($this->hasFoodColumn('sort_order')) {
                $rules['sort_order'] = 'nullable|integer|min:1';
            }

            $validated = $request->validate($rules);

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filePath = $file->getRealPath();
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                $imagePath = $uploadResult['secure_url'];
            }

            $createData = [
                'name' => $request->name,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'price' => $request->price,
                'description' => $request->description,
                'image' => $imagePath,
                'is_active' => $request->boolean('is_active', true),
                'is_special_option' => $request->boolean('is_special_option', false),
                'stock_quantity' => $request->integer('stock_quantity', 0),
                'is_inventory_managed' => $request->boolean('is_inventory_managed', false),
            ];

            if ($this->hasFoodColumn('meal_type')) {
                $createData['meal_type'] = $request->meal_type ?? 'all_day';
            }

            if ($this->hasFoodColumn('sort_order')) {
                $createData['sort_order'] = $request->filled('sort_order') ? $request->integer('sort_order') : 1;
            }

            $food = Food::create($createData);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Food item created successfully!',
                    'image' => $imagePath
                ], 201);
            }

            return redirect()->route('admin.foods.index')
                            ->with('success', 'Food item created successfully!');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, Food $food)
    {
        $this->ensureFoodOrderingColumns();

        try {
            $rules = [
                'name' => 'required|string|max:255|unique:foods,name,' . $food->id,
                'category' => 'required|string|in:messhall,cook_your_own',
                'subcategory' => 'nullable|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'remove_image' => 'nullable|in:1',
                'is_active' => 'nullable|boolean',
                'is_special_option' => 'nullable|boolean',
                'stock_quantity' => 'nullable|integer|min:0',
                'is_inventory_managed' => 'nullable|boolean',
            ];

            if ($this->hasFoodColumn('meal_type')) {
                $rules['meal_type'] = 'nullable|string|in:breakfast,lunch,dinner,all_day';
            }

            if ($this->hasFoodColumn('sort_order')) {
                $rules['sort_order'] = 'nullable|integer|min:1';
            }

            $validated = $request->validate($rules);

            $updateData = [
                'name' => $request->name,
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'price' => $request->price,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true),
                'is_special_option' => $request->boolean('is_special_option', false),
                'stock_quantity' => $request->integer('stock_quantity', 0),
                'is_inventory_managed' => $request->boolean('is_inventory_managed', false),
            ];

            if ($this->hasFoodColumn('meal_type')) {
                $updateData['meal_type'] = $request->meal_type ?? 'all_day';
            }

            if ($this->hasFoodColumn('sort_order')) {
                $updateData['sort_order'] = $request->filled('sort_order') ? $request->integer('sort_order') : 1;
            }

            if ($request->has('remove_image') && $request->remove_image == '1') {
                $updateData['image'] = null;
            } elseif ($request->hasFile('image')) {
                $file = $request->file('image');
                $filePath = $file->getRealPath();
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                $updateData['image'] = $uploadResult['secure_url'];
            }

            $food->update($updateData);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Food item updated successfully!',
                    'image' => $food->image
                ], 200);
            }

            return redirect()->route('admin.foods.index')
                            ->with('success', 'Food item updated successfully!');
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Food $food)
    {
        $food->delete();
        return redirect()->route('admin.foods.index')
                         ->with('success', 'Food item deleted successfully!');
    }

    /**
     * API endpoint for foods list (used in package creation)
     */
    public function apiIndex(Request $request)
    {
        $this->ensureFoodOrderingColumns();

        if ($request->query('api') !== '1' && ! $request->expectsJson()) {
            return $this->index($request);
        }

        $selectColumns = ['id', 'name', 'category', 'price', 'is_active'];
        if (Schema::hasColumn('foods', 'meal_type')) {
            $selectColumns[] = 'meal_type';
        }
        if (Schema::hasColumn('foods', 'sort_order')) {
            $selectColumns[] = 'sort_order';
        }

        $foodsQuery = Food::query()
            ->select($selectColumns)
            ->orderBy('category');

        if (Schema::hasColumn('foods', 'meal_type') && Schema::hasColumn('foods', 'sort_order')) {
            $foodsQuery
                ->orderByRaw("CASE meal_type WHEN 'breakfast' THEN 1 WHEN 'lunch' THEN 2 WHEN 'dinner' THEN 3 ELSE 4 END")
                ->orderBy('sort_order');
        }

        $foods = $foodsQuery->orderBy('name')->get();

        return response()->json(['foods' => $foods]);
    }
}
