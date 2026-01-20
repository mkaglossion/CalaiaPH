<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Models\FoodSwiperImage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FoodController extends Controller
{
    public function index()
    {
        $foods = Food::latest()->get();
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:foods,name',
            'category' => 'required|string|in:breakfast,lunch,dinner,package',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
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
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.foods.index')
                        ->with('success', 'Food item created successfully!');
    }

    public function update(Request $request, Food $food)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:foods,name,' . $food->id,
            'category' => 'required|string|in:breakfast,lunch,dinner,package',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_image' => 'nullable|in:1',
            'is_active' => 'nullable|boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
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

        return redirect()->route('admin.foods.index')
                        ->with('success', 'Food item updated successfully!');
    }

    public function destroy(Food $food)
    {
        $food->delete();
        return redirect()->route('admin.foods.index')
                        ->with('success', 'Food item deleted successfully!');
    }
}
