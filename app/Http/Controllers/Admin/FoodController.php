<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Models\FoodSwiperImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Validation\ValidationException;

class FoodController extends Controller
{
    public function index(Request $request)
    {
        // Handle API requests
        if ($request->query('api') === '1') {
            return $this->apiIndex($request);
        }

        $foods = Food::latest()->paginate(10);
        return view('admin.foods.index', compact('foods'));
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
        try {
            $validated = $request->validate([
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
            ]);

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filePath = $file->getRealPath();
                $uploadResult = Cloudinary::uploadApi()->upload($filePath, ['resource_type' => 'auto']);
                $imagePath = $uploadResult['secure_url'];
            }

            $food = Food::create([
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
            ]);

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
        try {
            $validated = $request->validate([
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
            ]);

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
        if ($request->query('api') !== '1' && ! $request->expectsJson()) {
            return $this->index($request);
        }

        $foods = Food::query()
            ->select('id', 'name', 'category', 'price', 'is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json(['foods' => $foods]);
    }
}
