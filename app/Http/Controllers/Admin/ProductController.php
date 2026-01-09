<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::paginate(10);
        return view('admin.ShopStore.shopitems', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|in:frozen,fresh',
            'is_active' => 'nullable|boolean',
        ], [
            'image.max' => 'The image field must not be greater than 2048 kilobytes.',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Product created successfully.', 'product' => $product]);
        }

        return redirect()->route('admin.shop.shopitems')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|in:frozen,fresh',
            'is_active' => 'nullable|boolean',
        ], [
            'image.max' => 'The image field must not be greater than 2048 kilobytes.',
        ]);

        $product = Product::findOrFail($id);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            // Optionally delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'category' => $request->category,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Product updated successfully.', 'product' => $product]);
        }

        return redirect()->route('admin.shop.shopitems')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Product deleted successfully.']);
        }

        return redirect()->route('admin.shop.shopitems')->with('success', 'Product deleted successfully.');
    }
}
